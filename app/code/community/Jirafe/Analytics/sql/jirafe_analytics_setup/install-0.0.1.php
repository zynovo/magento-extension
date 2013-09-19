<?php
$installer = $this;
$installer->startSetup();

$installer->run("
     
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/cart')};
    CREATE TABLE {$this->getTable('jirafe_analytics/cart')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `session_id` int(10) unsigned NOT NULL,
        `quote_id` int(10) unsigned DEFAULT NULL,
        `status_id` smallint(5) unsigned NOT NULL,
        `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `modified_dt` datetime  DEFAULT NULL,
        PRIMARY KEY (`id`)
     ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
     
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/cart_item')};
    CREATE TABLE {$this->getTable('jirafe_analytics/cart_item')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `cart_id` int(10)  unsigned NOT NULL,
        `product_id` int(10)  unsigned NOT NULL,
        `sku` varchar(255) DEFAULT NULL,
        `quantity` int(10) unsigned NOT NULL,
        `price` decimal(12,4) NOT NULL,
        `status_id` smallint(5) unsigned NOT NULL,
        `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `modified_dt` datetime  DEFAULT NULL,
        PRIMARY KEY (`id`)
     ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
     
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/category')};
    CREATE TABLE {$this->getTable('jirafe_analytics/category')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `admin_user_id`  int(10) unsigned NOT NULL,
        `entity_id` int(10) unsigned NOT NULL,
        `status_id` smallint(5) unsigned DEFAULT NULL,
        `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `modified_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
     ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
     
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/customer')};
    CREATE TABLE {$this->getTable('jirafe_analytics/customer')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `customer_id` int(10) unsigned NOT NULL,
        `first_name` varchar(64) DEFAULT NULL,
        `last_name` varchar(64) DEFAULT NULL,
        `email` varchar(256) DEFAULT NULL,
        `phone` varchar(25) DEFAULT NULL,
        `status_id` smallint(5) unsigned DEFAULT NULL,
        `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `modified_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
     ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
     
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/order')};
    CREATE TABLE {$this->getTable('jirafe_analytics/order')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `session_id` int(10) unsigned NOT NULL,
        `order_id` int(10) unsigned NOT NULL,
        `order_number` varchar(64) DEFAULT NULL,
        `grand_total` decimal(12,4) DEFAULT NULL,
        `shipping_amount` decimal(12,4) DEFAULT NULL,
        `shipping_tax_amount` decimal(12,4) DEFAULT NULL,
        `tax_amount` decimal(12,4) DEFAULT NULL,
        `total_paid` decimal(12,4) DEFAULT NULL ,
        `discount_amount` decimal(12,4) DEFAULT NULL,
        `status_id` smallint(5) unsigned DEFAULT NULL,
        `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `modified_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
     ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/order_item')};
    CREATE TABLE {$this->getTable('jirafe_analytics/order_item')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `session_id` int(10) unsigned NOT NULL,
        `order_id` int(10) unsigned NOT NULL,
        `item_id` int(10) unsigned NOT NULL,
        `product_id` int(10) unsigned DEFAULT NULL,
        `sku` varchar(255) DEFAULT NULL,
        `price` decimal(12,4) DEFAULT NULL,
        `tax_amount` decimal(12,4) DEFAULT NULL,
        `row_total` decimal(12,4)  DEFAULT NULL,
        `status_id` smallint(5) unsigned DEFAULT NULL,
        `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `modified_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
     ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/product')};
    CREATE TABLE {$this->getTable('jirafe_analytics/product')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `admin_user_id`  int(10) unsigned NOT NULL,
        `entity_id` int(10) unsigned NOT NULL,
        `status_id` smallint(5) unsigned DEFAULT NULL,
        `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `modified_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
     ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/session')};
    CREATE TABLE {$this->getTable('jirafe_analytics/session')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `session_key` varchar(64) NOT NULL,
        `customer_id` int(10) unsigned DEFAULT NULL,
        `ip_address` varchar(25) DEFAULT NULL,
        `store_id` smallint(5) unsigned DEFAULT NULL,
        `store_currency_code` varchar(64) DEFAULT NULL,
        `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `modified_dt` DATETIME DEFAULT NULL,
        PRIMARY KEY (`id`)
     ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/status')};
    CREATE TABLE {$this->getTable('jirafe_analytics/status')} (
        `id` int(10) unsigned NOT NULL,
        `description` varchar(16) NOT NULL,
        PRIMARY KEY (`id`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     
    INSERT INTO {$this->getTable('jirafe_analytics/status')} VALUES
        (1, 'added'),
        (2, 'modified'),
        (3, 'deleted');

    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/user')};
    CREATE TABLE {$this->getTable('jirafe_analytics/user')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `admin_user_id` int(10) unsigned NOT NULL,
        `email` varchar(128) DEFAULT NULL,
        `username` varchar(40) DEFAULT NULL,
        `status_id` smallint(5) unsigned DEFAULT NULL,
        `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `modified_dt` DATETIME DEFAULT NULL,
        PRIMARY KEY (`id`)
     ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
     
");
$installer->endSetup();
