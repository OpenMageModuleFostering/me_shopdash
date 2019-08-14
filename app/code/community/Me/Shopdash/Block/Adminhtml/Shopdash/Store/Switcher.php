<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Me_Shopdash_Block_Adminhtml_Shopdash_Store_Switcher extends Mage_Adminhtml_Block_Store_Switcher
{
    /**
     * @var bool
     */
    protected $_hasDefaultOption = false;

    /**
     * Set overriden params
     */
    public function __construct()
    {
        parent::__construct();
        $this->setUseConfirm(false)->setSwitchUrl(
            $this->getUrl('*/*/*/', array('store' => null, '_current' => true))
        );
    }
}
