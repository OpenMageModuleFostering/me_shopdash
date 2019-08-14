<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */ 
class Me_Shopdash_Helper_Data extends Mage_Core_Helper_Abstract 
{
    /**
     * Extension log file name
     * @var string
     */
    protected $_log = 'shopdash.log';

    /**
     * Base attributes for export
     * @var array
     */
    protected $_baseAttributes = array('name', 'description', 'url_path', 'image');

    /**
     * Not used attributes for export
     * @var array
     */
    protected $_removedAttributes = array(
        'visibility',
        'status',
        'url_key',
        'gift_message_available',
        'is_recurring'
    );

    /**
     * Base product types for export
     * @var array
     */
    protected $_baseProductTypes = array('simple', 'configurable', 'virtual', 'downloadable');

    /**
     * Deals variable
     * @var array
     */
    protected $_deal = array();

    /**
     * Product config array
     * @var array
     */
    protected $_productConfig = array();

    /**
     * Product unit attribute is custom
     * @var bool
     */
    protected $_productUnitIsCustom = false;

    /**
     * Category config array
     * @var array
     */
    protected $_categoryConfig = array();

    /**
     * Get extension config
     * @param $var
     * @return mixed
     */
    public function getBaseConfigByVar( $var )
    {
        return Mage::getStoreConfig('shopdash/config/' . $var);
    }

    /**
     * Get extension basic attributes to select
     * @return array
     */
    public function getBaseAttributes()
    {
        return $this->_baseAttributes;
    }

    /**
     * Get attributes to remove form select
     * @return array
     */
    public function getRemovedAttributes()
    {
        return $this->_removedAttributes;
    }

    /**
     * Get extension basic product types to select
     * @return array
     */
    public function getBaseProductTypes()
    {
        return $this->_baseProductTypes;
    }

    /**
     * Get extension deal configuration
     * @return mixed
     */
    public function getDealsConfig()
    {
        return Mage::getStoreConfig('shopdash/deals');
    }

    /**
     * Get extension products configuration
     * @return mixed
     */
    public function getProductConfig()
    {
        $this->_productConfig = Mage::getStoreConfig('shopdash/products');
        if( !is_null($this->_productConfig) ) return $this->_productConfig;
        else return null;
    }

    /**
     * Get product properties
     * @return array
     */
    public function getProperties()
    {
        $this->getProductConfig();
        if( isset($this->_productConfig['properties']) && !empty($this->_productConfig['properties']) ) {
            $properties = explode(',', $this->_productConfig['properties']);

            return $properties;
        }
        else return array();
    }

    /**
     * Get extension product unit configuration
     * @return mixed
     */
    public function getProductUnit()
    {
        $this->getProductConfig();
        if( isset($this->_productConfig['unit']) && !empty($this->_productConfig['unit']) ) return $this->_productConfig['unit'];
        else {
            $this->_productUnitIsCustom = true;
            return $this->_productConfig['unit_custom'];
        }
    }

    /**
     * Get if extension unit configuration is custom
     * @return bool
     */
    public function getProductUnitIsCustom()
    {
        $this->getProductUnit();
        return $this->_productUnitIsCustom;
    }

    /**
     * Get is export includes product image gallery
     * @return bool
     */
    public function getInclProductGallery()
    {
        $this->getProductConfig();
        if( !is_null($this->_productConfig['gallery']) ) return $this->_productConfig['gallery'];
        else return false;
    }

    /**
     * Get extension categories configuration
     * @return mixed
     */
    public function getCategoryConfig()
    {
        $this->_categoryConfig = Mage::getStoreConfig('shopdash/category');
        return $this->_categoryConfig;
    }

    /**
     * Get extension category ids configuration
     * @return array|null
     */
    public function getCategoryIdsConfig()
    {
        $this->getCategoryConfig();
        if( !is_null($this->_categoryConfig) && !$this->_categoryConfig['category_all'] ) {
            $category_ids = explode(',', $this->_categoryConfig['category_ids']);

            return $category_ids;
        }
        else return null;
    }

    /**
     * Get if item category ids are in extension config category ids
     * @param $itemCategoryIds
     * @return bool
     */
    public function getInSelectedCategory($itemCategoryIds)
    {
        if( is_null($itemCategoryIds) || empty($itemCategoryIds) ) return false;

        $configCategoryIds = $this->getCategoryIdsConfig();
        if( !is_null($configCategoryIds) ) {

            $result = false;

            foreach($itemCategoryIds as $categoryId) {
                if( in_array($categoryId, $configCategoryIds) ) {
                    $result = true;
                    break;
                }
            }

            return $result;
        }
        else return true;
    }

    /**
     * Get base deal configuration
     * @return mixed|null
     */
    public function getBaseDeal()
    {
        $this->_deal = $this->getDealsConfig();

        if( $this->_deal['deal_enable'] ) {
            unset($this->_deal['deal_enable']);

            return $this->_deal;
        }
        else {
            $this->_deal = null;
        }
    }

    /**
     * Get deal attributes for select
     * @return array|null
     */
    public function prepareDealAttributes()
    {
        $this->getBaseDeal();
        if( !is_null( $this->_deal ) ) {

            if( array_key_exists('name_custom', $this->_deal) && empty($this->_deal['name']) ) {
                $this->_deal['name'] = $this->_deal['name_custom'];
            }

            $dealAttributes = $this->_deal;
            unset($dealAttributes['priority']);

            return $dealAttributes;
        }
        else return null;
    }

    /**
     * Extension logging
     * @param $msg
     */
    public function setLog($msg)
    {
        if( is_array($msg) ) {
            foreach($msg as $message) {
                Mage::log($this->__($message), null, $this->_log);
            }
        }
        elseif(is_string($msg) ) {
            Mage::log($this->__($msg), null, $this->_log);
        }
        else {
            Mage::log($msg, null, $this->_log);
        }
    }            
}
