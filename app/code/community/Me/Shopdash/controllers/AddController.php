<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */
class Me_Shopdash_AddController extends Mage_Core_Controller_Front_Action
{
    /**
     * Dummy post data for testing with Magento Sample Data
     * @var array
     */
    protected $_dummyPost = array(
        'products' => array(
            array( // simple product
                'product_id' => 16,
                'amount' => 1,
                'attributes' => array()
            ),
            array( // simple product
                'product_id' => 17,
                'amount' => 3,
                'attributes' => array()
            ),
            array( // configurable product
                'product_id' => 83,
                'amount' => 1,
                'attributes' => array(
                    'Gender' => 'Mens',
                    'Shoe Size' => '12'
                )
            ),
            array( // simple product custom options
                'product_id'  => 171,
                'amount'      => 1,
                'attributes'  => array(
                    'Brand' => 'LG',
                    'Color' => 'Blue'
                ),
            ),
        )
    );

    /**
     * Retrieve shopping cart model object
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getModel('checkout/cart');
    }

    /**
     * Get checkout session model instance
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Initialize product instance from request data
     * @param productId
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct($productId)
    {
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Get url action
     */
    public function indexAction()
    {
        $_helper = Mage::helper('me_shopdash');
        $post = $this->getRequest()->getPost();

        // dummy post for testing
        //$post = $this->_dummyPost;

        if( $post ) {

            if( isset($post['products']) ) {
                $answer = $this->_getAnswer($post['products']);
                if( !is_null($answer) ) $answerUrl = Mage::getUrl('shopdash/add/addtocart', array('code' => $answer));
                else {
                    $_helper->setLog('Invalid request.');
                    $_helper->setLog($post);
                }
            }
            else {
                $_helper->setLog('Invalid request.');
                $_helper->setLog($post);
            }
        }

        Mage::app()->getResponse()->setBody($answerUrl);
    }

    /**
     * Add to cart action
     */
    public function addtocartAction()
    {
        $_helper = Mage::helper('me_shopdash');

        $params = $this->getRequest()->getParams();

        if( $params ) {

            parse_str( $params['code'], $data);

            if( !is_null($data) && is_array($data) ) {

                try {

                    foreach($data as $item) {

                        $cart = $this->_getCart();

                        if( !isset($item['amount']) || !isset($item['product_id']) ) {
                            throw new Exception($_helper->__('ShopDash: Cannot add the item to shopping cart.'));
                        }

                        $addParams['qty'] = (int)$item['amount'];
                        $addParams['product'] = (int)$item['product_id'];
                        if( isset($item['attributes']) ) $addParams['attributes'] = $item['attributes'];

                        if (isset($addParams['qty'])) {
                            $filter = new Zend_Filter_LocalizedToNormalized(
                                array('locale' => Mage::app()->getLocale()->getLocaleCode())
                            );
                            $addParams['qty'] = $filter->filter($addParams['qty']);
                        }

                        $product = $this->_initProduct($addParams['product']);

                        /**
                         * Check product availability
                         */
                        if (!$product) {
                            $this->_getSession()->addError($_helper->__('Cannot add the item to shopping cart.'));
                            $this->_redirect('checkout/cart');
                            return;
                        }

                        if( $product->getTypeId() == 'configurable' ) {
                            $addParams = $this->_getConfigurableAttributes($product, $item);
                            if( is_null($params) ) {
                                $this->_getSession()->addError($_helper->__('Cannot add configurable item to shopping cart.'));
                                $this->_redirect('checkout/cart');
                                return;
                            }
                        }

                        if( $product->getHasOptions() && $product->getRequiredOptions() && !empty($addParams['attributes']) ) {

                            $productCustomOptions = $product->getProductOptionsCollection();

                            if( !empty($productCustomOptions) ) {

                                foreach($productCustomOptions as $options) {
                                    if( $options->getIsRequire() ) {

                                        $values = array();

                                        foreach($options->getValues() as $value) {
                                            $values[$value->getId()] = $value->getStoreTitle() ? $value->getStoreTitle() : $value->getTitle();
                                        }

                                        $result[] = array(
                                            'option_id' => $options->getId(),
                                            'title' => $options->getTitle(),
                                            'values' => $values
                                        );

                                    }
                                }

                                foreach($result as $_result) {
                                    if( array_key_exists($_result['title'], $addParams['attributes']) ) {
                                        $option[$_result['option_id']] = array_search($addParams['attributes'][$_result['title']], $_result['values']);
                                    }
                                }

                                if( !empty($option) ) {
                                    $addParams['options'] = $option;
                                }

                                if( count($addParams['options']) != count($addParams['attributes']) || in_array(false, $addParams['options']) ) {
                                    Mage::throwException($_helper->__('Invalid custom option(s): <a href="%s">%s</a>', $product->getProducturl(), $product->getName()));
                                }

                            }
                        }

                        $cart->addProduct($product, $addParams);

                        $cart->save();

                        $this->_getSession()->setCartWasUpdated(true);

                    }

                    if (!$cart->getQuote()->getHasError()) {
                        if( count($data) > 1 ) $message = $_helper->__('Products were added to your shopping cart.');
                        else $message = $_helper->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                        $this->_getSession()->addSuccess($message);
                    }

                } catch (Mage_Core_Exception $e) {

                    if ($this->_getSession()->getUseNotice(true)) {
                        $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
                    } else {
                        $messages = array_unique(explode("\n", $e->getMessage()));
                        foreach ($messages as $message) {
                            $this->_getSession()->addError(Mage::helper('core')->__($message));
                        }
                    }

                    $this->_redirect('checkout/cart');

                } catch (Exception $e) {
                    $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
                    Mage::logException($e);
                    $this->_redirect('checkout/cart');
                }

            }

        }

        if( $_helper->getBaseConfigByVar('redirect') ) $this->_redirect('checkout/onepage');
        else $this->_redirect('checkout/cart');
    }

    /**
     * Create URL for answer
     * @param $post
     * @return null|string
     */
    protected function _getAnswer($post)
    {
        $_answerUrl = '';

        if( is_array($post) && !is_null($post) && !empty($post) ) {

            $_answerUrl = http_build_query( $post );

            return $_answerUrl;
        }
        else return null;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param array $item
     * @return array|null
     */
    protected function _getConfigurableAttributes($product, $item = array())
    {
        $params = array(
            'product' => (int)$product->getId(),
            'qty' => (int)$item['amount'],
        );

        if( !empty($item['attributes']) ) {

            $confAttributes = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);

            foreach($confAttributes as $attribute) {
                if( array_key_exists($attribute['frontend_label'], $item['attributes']) ) {

                    foreach($attribute['values'] as $value) {
                        if( $value['store_label'] == $item['attributes'][$attribute['frontend_label']] ) $superAttribute[$attribute['attribute_id']] = $value['value_index'];
                    }

                }
            }

            $params['super_attribute'] = $superAttribute;

            return $params;
        }
        else return null;
    }
}