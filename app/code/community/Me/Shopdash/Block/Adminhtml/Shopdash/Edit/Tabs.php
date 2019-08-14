<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Me_Shopdash_Block_Adminhtml_Shopdash_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('shopdash_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('me_shopdash')->__('ShopDash Export Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section1', array(
          'label'     => Mage::helper('me_shopdash')->__('Select Export Products'),
          'title'     => Mage::helper('me_shopdash')->__('Select Export Products'),
          'url'       => $this->getUrl('*/*/product', array('_current' => true)),
          'class'     => 'ajax',
      ));
     
      return parent::_beforeToHtml();
  }
}