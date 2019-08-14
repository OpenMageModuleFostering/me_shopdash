<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */
class Me_Shopdash_Adminhtml_ShopdashController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init action
     * @return $this
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
             ->_setActiveMenu('catalog/shopdash')
             ->_addBreadcrumb(
                Mage::helper('me_shopdash')->__('Catalog'),
                Mage::helper('me_shopdash')->__('Catalog')
             )
             ->_addBreadcrumb(
                Mage::helper('me_shopdash')->__('Manage ShopDash'),
                Mage::helper('me_shopdash')->__('Manage ShopDash')
             )
        ;
        return $this;
    }

    /**
     * Edit action
     */
    public function editAction()
    {
        if (! (int) $this->getRequest()->getParam('store')) {
            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('me_shopdash')->__('Please click on Reset filter to see the whole list'));
            return $this->_redirect('*/*/*/', array('store' => Mage::app()->getAnyStoreView()->getId(), '_current' => true));
        }

        $this->_initAction();
        
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Manage ShopDash'))
             ->_title($this->__('Manage Export'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('me_shopdash/adminhtml_shopdash_store_switcher'))
             ->_addContent($this->getLayout()->createBlock('me_shopdash/adminhtml_shopdash_edit'))
             ->_addLeft($this->getLayout()->createBlock('me_shopdash/adminhtml_shopdash_edit_tabs'));

        $this->renderLayout();
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        $links = $this->getRequest()->getPost('links');
        $store_id = $this->getRequest()->getParam('store');;

        if ( isset($links['products']) && !is_null($store_id) && $store_id ) {

            $productsGrid = Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['products']);

            try {

                $shopDashCollection = Mage::getModel('me_shopdash/shopdash')->getCollection()
                                    ->addFieldToFilter('store_id', $store_id);
                foreach($shopDashCollection as $shopdash) {
                    $shopdash->delete();
                }

                $shopDashCollection->clear();

                if( !empty($productsGrid) ) {

                    foreach(array_keys($productsGrid) as $productId) {

                        $shopDashModel = Mage::getModel('me_shopdash/shopdash');

                        $shopDashModel->setProductId( $productId );
                        $shopDashModel->setStoreId( $store_id );

                        $shopDashCollection->addItem($shopDashModel);
                    }

                    $shopDashCollection->save();

                }

                $this->_getSession()->addSuccess(Mage::helper('me_shopdash')->__('Export products has been saved.'));

            } catch (Mage_Core_Exception $e) {

                $this->_getSession()->addError($e->getMessage());

            } catch (Exception $e) {

                $this->_getSession()->addException($e, Mage::helper('me_shopdash')->__('An error occurred while saving the news item.'));

            }

        }
        else {
            $this->_getSession()->addError(Mage::helper('me_shopdash')->__('Please select products.'));
        }

        $this->_redirect('*/*/edit', array('store' => $store_id));

    }

    /**
     * Product default grid action
     */
    public function productAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('product.grid')->setProducts($this->getRequest()->getPost('products', null));
        $this->renderLayout();
    }

    /**
     * Product grid action
     */
    public function productgridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('product.grid')->setProducts($this->getRequest()->getPost('products', null));
        $this->renderLayout();
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_title($this->__('ShopDash'))
             ->_title($this->__('Manage Export'));

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Run action
     */
    public function runAction()
    {
        try {
            $_helper = Mage::helper(('me_shopdash'));

            $result = array(
                'result' => 0,
                'msg' => ''
            );

            if( !$_helper->getBaseConfigByVar('enable') ) {
                $_helper->setLog('ShopDash extension is disbaled.');
                $result['result'] = 1;
            }

            Mage::getModel('me_shopdash/shopdash')->startExport();
            $result['result'] = 1;
        } catch (Mage_Core_Exception $e) {
            $result['result'] = 0;
            $result['msg'] = $e->getMessage();
        } catch (Exception $e) {
            $result['result'] = 0;
            $result['msg'] = $e . Mage::helper('me_shopdash')->__('An error occurred while running export.');
        }

        Mage::app()->getResponse()->setBody( json_encode($result) );
    }
}