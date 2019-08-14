<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Me_Shopdash_Model_Mysql4_Shopdash_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct() 
    {
        parent::_construct();
        $this->_init('me_shopdash/shopdash');
    }    
}