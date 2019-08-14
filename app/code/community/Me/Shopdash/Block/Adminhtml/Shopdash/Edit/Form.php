<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Me_Shopdash_Block_Adminhtml_Shopdash_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getSaveUrl(),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Retrieve Save URL
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
}