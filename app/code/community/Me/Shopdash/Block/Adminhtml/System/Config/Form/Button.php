<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Me_Shopdash_Block_Adminhtml_System_Config_Form_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('me/shopdash/system/config/form/button.phtml');
    }

    /**
     * Return element html
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     * @return string
     */
    public function getAjaxRunUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_shopdash/run');
    }

    /**
     * Generate button html
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'        => 'me_shopdash_button',
                'label'     => $this->helper('me_shopdash')->__('Run Export'),
                'onclick'   => 'javascript:run(); return false;'
            ));

        return $button->toHtml();
    }
}
