<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Me_Shopdash_Model_System_Config_Source_Priority
{
    const SMALL	= 'small';
    const MEDIUM = 'medium';
    const HUGE = 'huge';

    /**
     * Get priority values
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            ''              => Mage::helper('me_shopdash')->__('Please select...'),
            self::SMALL     => Mage::helper('me_shopdash')->__('Small'),
            self::MEDIUM    => Mage::helper('me_shopdash')->__('Medium'),
            self::HUGE      => Mage::helper('me_shopdash')->__('Huge')
        );
    }
}
