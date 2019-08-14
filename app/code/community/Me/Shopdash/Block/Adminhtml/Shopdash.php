<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Me_Shopdash_Block_Adminhtml_Shopdash extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'me_shopdash';
        $this->_controller = 'adminhtml_shopdash';
        $this->_headerText = Mage::helper('me_shopdash')->__('Shopdash Manager');
        $this->_addButtonLabel = Mage::helper('me_shopdash')->__('Add Item');
        parent::__construct();
        $this->_removeButton('add');
    }
}