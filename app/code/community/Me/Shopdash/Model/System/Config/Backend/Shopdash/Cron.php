<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */
class Me_Shopdash_Model_System_Config_Backend_Shopdash_Cron extends Mage_Core_Model_Config_Data
{
    const CRON_STRING_PATH = 'crontab/jobs/me_shopdash_export/schedule/cron_expr';
    const CRON_MODEL_PATH = 'crontab/jobs/me_shopdash_export/run/model';

    protected function _beforeSave()
    {
        $time = $this->getValue();;
        
        $cronExprArray = array(
            intval($time[1]), // Minute
            intval($time[0]), // Hour
            '*', // Day of the Month
            '*', // Month of the Year
            '*', // Day of the Week
        );

        $cronExprString = join(' ', $cronExprArray);

        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::CRON_MODEL_PATH))
                ->setPath(self::CRON_MODEL_PATH)
                ->save();
        } catch (Exception $e) {
            throw new Exception(Mage::helper('me_shopdash')->__('Unable to save the cron expression.'));
        }
    }
}
