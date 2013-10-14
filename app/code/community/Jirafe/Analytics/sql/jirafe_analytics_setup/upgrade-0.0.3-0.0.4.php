<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/log')};
    CREATE TABLE {$this->getTable('jirafe_analytics/log')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `type` varchar(32) DEFAULT NULL,
        `location` varchar(256) DEFAULT NULL,
        `message` text NOT NULL,
        `created_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
");
$installer->endSetup();
    