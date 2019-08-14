<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Me_Shopdash_Model_System_Config_Source_Attributes
{
    public function toOptionArray()
    {
        $options = array();
        
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
                    ->addVisibleFilter();
        
        $options[] = array('value' => '', 'label' => Mage::helper('me_shopdash')->__('Please select...'));
        foreach($collection as $attribute) {
            $options[] = array('value' => $attribute->getAttributeCode(), 'label' => $attribute->getFrontendLabel());
        }
        
        return $options;
    }
}
