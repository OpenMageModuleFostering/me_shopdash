<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Me_Shopdash_Model_System_Config_Source_Redirect
{
    /**
     * Get priority values
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            0 => Mage::helper('me_shopdash')->__('Shopping Cart'),
            1 => Mage::helper('me_shopdash')->__('Checkout')
        );
    }
}
