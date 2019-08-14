<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */
class Me_Shopdash_Model_Shopdash_Catalog_Product extends Mage_Core_Model_Abstract
{
    protected $_productCollection;

    /**
     * @param int $store_id
     * @param null $attributes
     * @return Mage_Catalog_Model_Resource_Product_Collection | null
     */
    public function getStoreCollection($store_id, $attributes = null)
    {
        $productCollection = $this->_getProductCollection($store_id);

        $productCollection->load();

        return $productCollection;
    }

    /**
     * Get base products collection
     * @param int $store_id
     * @return Mage_Catalog_Model_Resource_Product_Collection | null
     */
    protected function _getProductCollection($store_id)
    {
        $_helper = Mage::helper('me_shopdash');
        $store = Mage::app()->getStore($store_id);
        
        if (!$store) {
            return false;
        }
        
        if ( is_null($this->_productCollection) ) {

            $this->_productCollection = Mage::getModel('catalog/product')->getCollection()
                                      ->setStoreId($store_id)
                                      ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                                      ->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
                                      ->addAttributeToFilter('type_id', array('in' => Mage::helper('me_shopdash')->getBaseProductTypes()));

            $product_ids = $this->_getShopdashProductIds($store_id);
            if( !is_null($product_ids) && count($product_ids) > 0 ) $this->_productCollection->addAttributeToFilter('entity_id', array('in' => $product_ids));

            // add deal attributes to select
            if( $dealAttributes = $_helper->prepareDealAttributes() ) {

                if( isset($dealAttributes['name_custom']) && $dealAttributes['name_custom'] != $dealAttributes['name'] ) {
                    $this->_productCollection->addAttributeToSelect( $dealAttributes );
                }
                else {
                    $this->_productCollection->addAttributeToSelect( $dealAttributes['price'] );
                }
            }

            // add properties attributes to select
            if( count( $_helper->getProperties() ) > 0 ) $this->_productCollection->addAttributeToSelect( $_helper->getProperties() );

            // add unit attributes to select
            if( !$_helper->getProductUnitIsCustom() ) $this->_productCollection->addAttributeToSelect( $_helper->getProductUnit() );

            // add base attributes to select
            $this->_addProductAttributesAndPrices($this->_productCollection);

            // add media gallery images to select
            if( $_helper->getInclProductGallery() ) $this->_addMediaGallery($this->_productCollection);

        }
        
        return $this->_productCollection;
    }

    /**
     * Add attributes to collection
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _addProductAttributesAndPrices(Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection)
    {
        return $collection
        ->addMinimalPrice()
        ->addFinalPrice()
        ->addAttributeToSelect( Mage::helper('me_shopdash')->getBaseAttributes() ) //TODO: check url_path
        ->addUrlRewrite();
    }

    /**
     * Add media gallery images to collection
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _addMediaGallery(Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection)
    {
        if( !is_null($collection) ) {
            $backendModel = $collection->getResource()->getAttribute('media_gallery')->getBackend();
            foreach($collection as $product){
                $backendModel->afterLoad($product);
            }
        }
    }

    /**
     * Get export product ids by store
     * @param $store_id
     * @return array|null
     */
    protected function _getShopdashProductIds($store_id)
    {
        if( $store_id ) {

            $shopDashCollection = Mage::getModel('me_shopdash/shopdash')->getCollection()
                                ->addFieldToFilter('store_id', $store_id);

            if( $shopDashCollection->count() ) {

                $productIds = array();

                foreach($shopDashCollection as $shopdash) {
                    $productIds[] = $shopdash->getProductId();
                }

                return $productIds;

            }
            else return null;

        }
        else return  null;
    }
}