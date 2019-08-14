<?php
/**
 * @category    Me
 * @package     Me_Shopdash
 * @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('me_shopdash/shopdash')};
CREATE TABLE IF NOT EXISTS {$this->getTable('me_shopdash/shopdash')} (
  `shopdash_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`shopdash_id`),
  KEY `product_id` (`product_id`),
  KEY `store_id` (`store_id`),
  KEY `store_id_2` (`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->getTable('me_shopdash/shopdash')}`
  ADD CONSTRAINT FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE,
  ADD CONSTRAINT FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE;

    ");

$installer->endSetup(); 