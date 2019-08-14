<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */
class Me_Shopdash_Model_Shopdash extends Mage_Core_Model_Abstract 
{
    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('me_shopdash/shopdash');
    }

    /**
     * Feed file path
     * @var
     */
    protected $_filePath;

    /**
     * Feed file name
     * @var
     */
    protected $_fileName;

    /**
     * Start export for each store
     */
    public function startExport()
    {
        $_helper = $this->_getHelper();

        if( !$_helper->getBaseConfigByVar('enable') ) {
            $_helper->setLog('ShopDash extension is disbaled.');
            return;
        }
        
        $allStores = Mage::app()->getStores();
        foreach($allStores as $store) {
            $this->_generateXml($store->getId());
        }
    }

    /**
     * Get extension helper
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelper()
    {
        return Mage::helper('me_shopdash');
    }

    /**
     * Get path for export file
     * @return mixed
     */
    protected function _getPath() 
    {
        if (is_null($this->_filePath)) {
            $this->_filePath = str_replace('//', '/', Mage::getBaseDir() . $this->_getHelper()->getBaseConfigByVar('path'));
        }
        return $this->_filePath;
    }

    /**
     * Get export file name
     * @return mixed
     */
    protected function _getFilename() 
    {
        if (is_null($this->_fileName)) {
            $this->_fileName = $this->_getHelper()->getBaseConfigByVar('filename');
        }
        return $this->_fileName;
    }

    /**
     * Generate XML for defined store
     * @param $store_id
     * @return $this
     */
    protected function _generateXml($store_id) 
    {
        $store = Mage::app()->getStore($store_id);
        
        $_helper = $this->_getHelper();
        $_taxHelper  = Mage::helper('tax');
        $_coreHelper = Mage::helper('core');
        
        // website price inculdes tax
        $_inclTax = $_taxHelper->priceIncludesTax($store);
        
        //config values
        $baseUrl = Mage::app()->getStore($store_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        $collection = Mage::getModel('me_shopdash/shopdash_catalog_product')->getStoreCollection($store_id);

        if( !$collection->count() ) {
            $_helper->setLog('Store Id: ' . $store_id . ' Product collection empty.');
            return;
        }

        //category array
        $categoryPathes = $this->_getCategoryPathes($store_id);

        if( !count($categoryPathes) ) {
            $_helper->setLog('Category pathes empty.');
            return;
        }

        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->_getPath()));

        if ( $io->fileExists('store' . $store_id . '_' . $this->_getFilename()) && !$io->isWriteable('store' . $store_id . '_' . $this->_getFilename()) ) {
            $this->_getHelper()->setLog('Permission error, unable to write export file');
        }

        $io->streamOpen('store' . $store_id . '_' . $this->_getFilename());

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<catalog>');

        foreach ($collection as $item) {

            // check selected category ids
            if( !$_helper->getInSelectedCategory( $item->getCategoryIds() ) ) continue;

            //get prices
            $_finalPriceInclTax = $this->_getFinalPrice($item, $item->getPrice(), $_taxHelper, $store, $_inclTax);
            
            $xml = sprintf('<product><id>%s</id><name><![CDATA[%s]]></name><price>%s</price><category>%s</category><url><![CDATA[%s]]></url><description><![CDATA[%s]]></description>%s%s%s%s</product>',
                $item->getEntityId(), // id
                $_coreHelper->stripTags($item->getName(), null, true), // name
                round($_finalPriceInclTax, 2), // price
                $this->_getCategoryPathById($categoryPathes, $item->getCategoryIds()), // category
                $baseUrl . $item->getUrlPath(), // url
                $_coreHelper->stripTags($item->getDescription(), null, true), // description
                $this->getImages($item, $baseUrl), // images
                $this->_getDeal($item, $_taxHelper, $store, $_inclTax), // deal
                $this->_getUnit($item), // unit
                $this->_getProperties($item, $_coreHelper) // properties
            );
            
            $io->streamWrite($xml);
        }
        unset($collection);

        $io->streamWrite('</catalog>');
        $io->streamClose();

        return $this;
    }

    /**
     * Get item properties
     * @param Mage_Catalog_Model_Product $item
     * @param $_coreHelper
     * @return string
     */
    protected function _getProperties($item, $_coreHelper)
    {
        $_properties = '';
        $propertyRow = '';

        $properties = $this->_getHelper()->getProperties();
        foreach($properties as $attribute) {

            $itemProperty = $item->getData($attribute);

            if( !is_null( $itemProperty ) ) {

                if( $item->getAttributeText($attribute) ) $value = $item->getAttributeText($attribute);
                else $value = $item->getData($attribute);

                if( is_array( $value ) ) { // if multiselect attaribute

                    $_multiValues = implode(',', $value);

                    $propertyRow .= sprintf('<property name="%s"><![CDATA[%s]]></property>',
                        $_coreHelper->stripTags($attribute, null, true),
                        $_coreHelper->stripTags(trim($_multiValues), null, true)
                    );
                }
                else {
                    $propertyRow .= sprintf('<property name="%s"><![CDATA[%s]]></property>',
                        $_coreHelper->stripTags($attribute, null, true),
                        $_coreHelper->stripTags(trim($value), null, true)
                    );
                }
            }

        }

        if( $item->getTypeId() == 'configurable' ) {

            $confAttributes = $item->getTypeInstance(true)->getConfigurableAttributesAsArray($item);
            if( !empty($confAttributes) ) {
                foreach($confAttributes as $confAttribute) {

                    $_values = '';
                    $i = 1;
                    $cnt = count($confAttribute['values']);

                    foreach($confAttribute['values'] as $confValue) {
                        if( $i != $cnt ) $_values .=  trim($confValue['store_label']) . '|';
                        else $_values .= trim($confValue['store_label']);
                        $i++;
                    }

                    $propertyRow .= sprintf('<property name="%s" required="required"><![CDATA[%s]]></property>',
                        $_coreHelper->stripTags($confAttribute['frontend_label'], null, true),
                        $_coreHelper->stripTags(trim($_values), null, true)
                    );

                }
            }

        }

        if( $item->getHasOptions() && $item->getRequiredOptions() ) {

            $productCustomOptions = $item->getProductOptionsCollection();

            if( !empty($productCustomOptions) ) {

                foreach ($productCustomOptions as $options) {

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

                        $propertyRow .= sprintf('<property name="%s" required="required"><![CDATA[%s]]></property>',
                            $_coreHelper->stripTags($options->getTitle(), null, true),
                            $_coreHelper->stripTags(implode('|', $values), null, true)
                        );

                    }

                }

            }

        }

        if( $propertyRow ) $_properties = sprintf('<properties>%s</properties>', $propertyRow);

        return $_properties;
    }

    /**
     * Get item's images
     * @param Mage_Catalog_Model_Product $item
     * @param string $baseUrl
     * @return string
     */
    protected function getImages($item, $baseUrl)
    {
        $_images = '';

        if( !Mage::helper('me_shopdash')->getInclProductGallery() ) {
            $_images = sprintf('<images><image><![CDATA[%s]]></image></images>',
                $baseUrl . 'media/catalog/product' . $item->getImage()
            );
        }
        else {
            $images = $item->getData('media_gallery');
            if( isset($images['images']) && count($images['images']) > 0 ) {

                if( count($images['images']) > 1 ) {
                    $imageRow = '';

                    foreach($images['images'] as $image) {
                        $imageRow .= sprintf('<image><![CDATA[%s]]></image>',
                            $baseUrl . 'media/catalog/product' . $image['file']
                        );
                    }

                    $_images = sprintf('<images>%s</images>', $imageRow);

                }
                else {
                    $_images = sprintf('<images><image><![CDATA[%s]]></image></images>',
                        $baseUrl . 'media/catalog/product' . $item->getImage()
                    );
                }

            }
        }

        return $_images;
    }

    /**
     * Get item's unit
     * @param Mage_Catalog_Model_Product $item
     * @return string
     */
    protected function _getUnit($item)
    {
        $_helper = $this->_getHelper();
        $_unit = '';

        if( !$_helper->getProductUnitIsCustom() ) $unit = $item->getData( $_helper->getProductUnit() );
        else $unit = $_helper->getProductUnit();

        $_unit = sprintf('<unit_of_quantity><![CDATA[%s]]></unit_of_quantity>',
            Mage::helper('core')->stripTags($unit, null, true)
        );

        return $_unit;
    }

    /**
     * Get item's deal
     * @param Mage_Catalog_Model_Product $item
     * @param Mage_Tax_Helper_Data $_taxHelper
     * @param Mage_Core_Model_Store $store
     * @param boolean $_inclTax
     * @return string
     */
    protected function _getDeal($item, $_taxHelper, $store, $_inclTax)
    {
        $dealConfig = $this->_getHelper()->getBaseDeal();
        $price = $item->getData( $dealConfig['price'] );
        if( isset($dealConfig['name']) && !empty($dealConfig['name']) ) $name = $item->getData( $dealConfig['name'] );
        else $name = $dealConfig['name_custom'];

        if( !is_null($dealConfig) && !is_null($price) && !is_null($name)) {
            $_dealItem = sprintf('<deal><name><![CDATA[%s]]></name><priority><![CDATA[%s]]></priority><price>%s</price></deal>',
                Mage::helper('core')->stripTags($name, null, true),
                $dealConfig['priority'],
                $this->_getFinalPrice($item, $price, $_taxHelper, $store, $_inclTax)
            );

            return $_dealItem;
        }
        else return '';
    }

    /**
     * Get item final price with tax
     * @param Mage_Catalog_Model_Product $item
     * @param float price
     * @param Mage_Tax_Helper_Data $_taxHelper
     * @param Mage_Core_Model_Store $store
     * @param boolean $_inclTax
     * @return float
     */
    protected function _getFinalPrice($item, $price, $_taxHelper, $store, $_inclTax)
    {
        if( !$_inclTax ) {
            $_finalPrice = $store->convertPrice($price, null);
            return $_taxHelper->getPrice($item, $_finalPrice, true, null, null, null, $store, null);
        }
        else {
            return $store->convertPrice($price, null);
        }
    }

    /**
     * Get category pathes by category id
     * @param $categoryPathes
     * @param $category_ids
     * @return string
     */
    protected function _getCategoryPathById($categoryPathes, $category_ids)
    {
        $categoryPath = '';
        foreach($category_ids as $id) {
            if( isset($categoryPathes[$id]) ) $categoryPath = $categoryPathes[$id];
        }
        
        return $categoryPath;
    }

    /**
     * Get all category pathes
     * @param $store_id
     * @return array
     */
    protected function _getCategoryPathes($store_id)
    {
        $categoryPathes = array();
        $categoriesArray = $this->_getCategoriesArray($store_id);
        
        foreach($categoriesArray as $key => $category) {
            
            $categoryPath = '';
            
            $pathArray = explode('/', $category['path']);
            $lastId = end($pathArray);
            foreach($pathArray as $id) {
                if( $id != $lastId ) $categoryPath .= $categoriesArray[$id]['name'] . ' / ';
                else $categoryPath .= $categoriesArray[$id]['name'];
            }
            
            $categoryPathes[$key] = $categoryPath;
        }

        return $categoryPathes;
    }

    /**
     * Get categories by store
     * @param $store_id
     * @return array
     */
    protected function _getCategoriesArray($store_id)
    {
        $categoriesArray = array();
        
        $storecategoryid = Mage::app()->getStore($store_id)->getRootCategoryId(); 
        $storeRootCategoryPath = Mage::getModel('catalog/category')->load( $storecategoryid )->getPath();
        
        $categoriesCollection = Mage::getModel('catalog/category')->getCollection()
                              ->addAttributeToSelect('name')
                              ->addPathsFilter($storeRootCategoryPath . '/');

        $selectedCategoryIds = Mage::helper('me_shopdash')->getCategoryIdsConfig();
        if( $selectedCategoryIds ) $categoriesCollection->addAttributeToFilter('entity_id', array('in', $selectedCategoryIds));

        foreach($categoriesCollection as $category) {
            $categoriesArray[$category->getId()]['name'] = htmlspecialchars($category->getName());
            $categoriesArray[$category->getId()]['path'] = str_replace($storeRootCategoryPath . '/', '', $category->getPath());
        }
        
        return $categoriesArray;
    }
}
