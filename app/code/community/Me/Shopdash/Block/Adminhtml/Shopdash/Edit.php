<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Me_Shopdash_Block_Adminhtml_Shopdash_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'me_shopdash';
        $this->_controller = 'adminhtml_shopdash';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('me_shopdash')->__('Save Products'));

        $this->_removeButton('saveandcontinue');
        $this->_removeButton('delete');
        $this->_removeButton('back');
    }

    /**
    * Add child HTML to layout
    * @return Me_Shopdash_Block_Adminhtml_Shopdash_Edit
    */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setChild('store_switcher', $this->getLayout()->createBlock('adminhtml/tag_store_switcher'));

        return $this;
    }

    /**
     * Get header text
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('me_shopdash')->__('Select Export Product(s)');
    }
}