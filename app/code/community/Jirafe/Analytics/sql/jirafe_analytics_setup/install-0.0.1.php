<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/session')};
    CREATE TABLE {$this->getTable('jirafe_analytics/session')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `http_user_agent` varchar(256) DEFAULT NULL,
        `http_accept_language` varchar(64) DEFAULT NULL,
        `session_id` varchar(64) NOT NULL,
        `visitor_id` int(11) unsigned DEFAULT 0,
        `quote_id` int(11) DEFAULT NULL,
        `ip_address` varchar(25) DEFAULT NULL,
        `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
     ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
     
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/cart_item')};
    CREATE TABLE {$this->getTable('jirafe_analytics/cart_item')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `session_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `sku` varchar(255) DEFAULT NULL,
        `quote_id` int(11) DEFAULT NULL,
        `customer_id` int(11) DEFAULT NULL,
        `quantity` int(11) NOT NULL,
        `price` decimal(12,4) NOT NULL,
        `removed` tinyint unsigned DEFAULT 0,
        `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
     ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
");
$installer->endSetup();
