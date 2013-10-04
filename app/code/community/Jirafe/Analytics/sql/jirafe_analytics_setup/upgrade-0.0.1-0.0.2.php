<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('jirafe_analytics/queue')} ADD COLUMN `store_id` int(10) NOT NULL DEFAULT '1';
    ALTER TABLE {$this->getTable('jirafe_analytics/queue')} ADD COLUMN `historical` tinyint(1) unsigned NOT NULL DEFAULT '0';
    ALTER TABLE {$this->getTable('jirafe_analytics/queue')} ADD KEY `store_id` (`store_id`);
    ALTER TABLE {$this->getTable('jirafe_analytics/queue')} ADD KEY `historical` (`historical`);
    ALTER TABLE {$this->getTable('jirafe_analytics/queue')} ADD CONSTRAINT `jirafe_analytics_queue_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`)  ON DELETE CASCADE ON UPDATE CASCADE;
");
$installer->endSetup();
    