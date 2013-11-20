<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/batch')};
    CREATE TABLE {$this->getTable('jirafe_analytics/batch')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `json` mediumtext NOT NULL,
        `store_id` int(10) NOT NULL DEFAULT '1',
        `historical` tinyint(1) unsigned NOT NULL DEFAULT '0',
        `http_code` int(10) DEFAULT NULL,
        `total_time` FLOAT DEFAULT 0,
        `created_dt` datetime DEFAULT NULL,
        `completed_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `store_id` (`store_id`),
        KEY `historical` (`historical`)
    ) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/data_type')};
    CREATE TABLE {$this->getTable('jirafe_analytics/data_type')} (
        `id` int(10) unsigned NOT NULL,
        `type` varchar(32),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    INSERT INTO {$this->getTable('jirafe_analytics/data_type')} VALUES
        (1, 'cart'),
        (2, 'category'),
        (3, 'customer'),
        (4, 'order'),
        (5, 'product'),
        (6, 'employee');
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/data')};
    CREATE TABLE {$this->getTable('jirafe_analytics/data')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `json` text NOT NULL,
        `type_id` int(10) unsigned NOT NULL,
        `store_id` int(10) NOT NULL DEFAULT '1',
        `attempt_count` int(10) unsigned NOT NULL DEFAULT '0',
        `success` tinyint(1) unsigned NOT NULL DEFAULT '0',
        `historical` tinyint(1) unsigned NOT NULL DEFAULT '0',
        `captured_dt` datetime DEFAULT NULL,
        `completed_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `type_id` (`type_id`),
        KEY `store_id` (`store_id`),
        KEY `historical` (`historical`),
        CONSTRAINT `jirafe_analytics_data_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES {$this->getTable('jirafe_analytics/data_type')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `jirafe_analytics_data_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/batch_data')};
    CREATE TABLE {$this->getTable('jirafe_analytics/batch_data')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `batch_id` int(10) unsigned NOT NULL,
        `data_id` int(10)  unsigned NOT NULL,
        `batch_order` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id`),
        KEY `batch_id` (`batch_id`),
        KEY `store_id` (`data_id`),
        CONSTRAINT `jirafe_analytics_batch_data_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES {$this->getTable('jirafe_analytics/batch')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `jirafe_analytics_batch_data_ibfk_2` FOREIGN KEY (`data_id`) REFERENCES {$this->getTable('jirafe_analytics/data')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/data_attempt')};
    CREATE TABLE {$this->getTable('jirafe_analytics/data_attempt')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `data_id` int(10) unsigned NOT NULL,
        `created_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `data_id` (`data_id`),
        CONSTRAINT `jirafe_analytics_data_attempt_ibfk_1` FOREIGN KEY (`data_id`) REFERENCES {$this->getTable('jirafe_analytics/data')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/data_error')};
    CREATE TABLE {$this->getTable('jirafe_analytics/data_error')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `data_id` int(10) unsigned NOT NULL,
        `data_attempt_id` int(10) unsigned NOT NULL,
        `error_type` varchar(64) DEFAULT NULL,
        `errors` text,
        `created_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `data_id` (`data_id`),
        KEY `data_attempt_id` (`data_attempt_id`),
        CONSTRAINT `jirafe_analytics_data_error_ibfk_1` FOREIGN KEY (`data_id`) REFERENCES {$this->getTable('jirafe_analytics/data')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `jirafe_analytics_data_error_ibfk_2` FOREIGN KEY (`data_attempt_id`) REFERENCES {$this->getTable('jirafe_analytics/data_attempt')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/log')};
    CREATE TABLE {$this->getTable('jirafe_analytics/log')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `type` varchar(32) DEFAULT NULL,
        `location` varchar(256) DEFAULT NULL,
        `message` text NOT NULL,
        `created_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/map')};
    CREATE TABLE {$this->getTable('jirafe_analytics/map')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `element` varchar(16) NOT NULL, 
        `key` varchar(64) NOT NULL,
        `api` varchar(64) NOT NULL,
        `magento` varchar(64) NOT NULL,
        `type` varchar(16) NOT NULL,
        `default` varchar(256) DEFAULT NULL,
        `created_ts` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    INSERT INTO {$this->getTable('jirafe_analytics/map')} ( `element`, `key`, `api`, `magento`, `type`, `default` ) VALUES 
        ('cart', 'id', 'id', 'entity_id', 'string', '' ),
        ('cart', 'create_date', 'create_date', 'created_at', 'datetime', '' ),
        ('cart', 'change_date', 'change_date', 'updated_at', 'datetime', '' ),
        ('cart', 'subtotal', 'subtotal', 'subtotal', 'float', '' ),
        ('cart', 'total', 'total', 'grand_total', 'float', '' ),
        ('cart', 'total_tax', 'total_tax',null,'float', '0' ),
        ('cart', 'total_shipping', 'total_shipping',null,'float', '0' ),
        ('cart', 'total_payment_cost', 'total_payment_cost', 'grand_total', 'float', '0' ),
        ('cart', 'total_discounts', 'total_discounts',null,'float', '0' ),
        ('cart', 'currency', 'currency', 'quote_currency_code', 'string', '' ),
        ('cart_item', 'id', 'id', 'item_id', 'string', '' ),
        ('cart_item', 'create_date', 'create_date', 'created_at', 'datetime', '' ),
        ('cart_item', 'change_date', 'change_date', 'updated_at', 'datetime', '' ),
        ('cart_item', 'quantity', 'quantity', 'qty', 'int', '' ),
        ('category', 'id','id', 'entity_id', 'string', '' ),
        ('category', 'name','name', 'name', 'string', '' ),
        ('category', 'change_date','change_date', 'updated_at', 'datetime', '' ),
        ('category', 'create_date','create_date', 'created_at', 'datetime', '' ),
        ('customer', 'id', 'id', 'entity_id', 'string', '' ),
        ('customer', 'active_flag', 'active_flag', 'is_active', 'boolean', '' ),
        ('customer', 'change_date', 'change_date', 'created_at', 'datetime', '' ),
        ('customer', 'create_date', 'create_date', 'updated_at', 'datetime', '' ),
        ('customer', 'email', 'email', 'email', 'string', '' ),
        ('customer', 'first_name', 'first_name', 'firstname', 'string', '' ),
        ('customer', 'last_name', 'last_name', 'lastname', 'string', '' ),
        ('customer', 'company', 'company', 'company', 'string', '' ),
        ('customer', 'phone', 'phone', 'telephone', 'string', '' ),
        ('customer', 'company', 'company', 'company', 'string', '' ),
        ('customer', 'phone', 'phone', 'telephone', 'string', '' ),
        ('employee', 'id', 'id', 'user_id', 'string', '' ),
        ('employee', 'active_flag', 'active_flag', 'is_active', 'boolean', 'true' ),
        ('employee', 'change_date', 'change_date', 'created', 'datetime', '' ),
        ('employee', 'create_date', 'create_date', 'modified', 'datetime', '' ),
        ('employee', 'email', 'email', 'email', 'string', '' ),
        ('employee', 'first_name', 'first_name', 'firstname', 'string', '' ),
        ('employee', 'last_name', 'last_name', 'lastname', 'string', '' ),
        ('order', 'order_number', 'order_number', 'increment_id', 'string', '' ),
        ('order', 'cart_id', 'cart_id', 'quote_id', 'string', '' ),
        ('order', 'order_date', 'order_date', 'created_at', 'datetime', '' ),
        ('order', 'create_date', 'create_date', 'created_at', 'datetime', '' ),
        ('order', 'change_date', 'change_date', 'updated_at', 'datetime', '' ),
        ('order', 'subtotal', 'subtotal', 'subtotal', 'float', '' ),
        ('order', 'total', 'total', 'grand_total', 'float', '' ),
        ('order', 'total_tax', 'total_tax', 'tax_amount', 'float', '' ),
        ('order', 'total_shipping', 'total_shipping', 'shipping_amount', 'float', '' ),
        ('order', 'total_discounts', 'total_discounts', 'discount_amount', 'float', '' ),
        ('order', 'currency', 'currency', 'order_currency_code', 'string', '' ),
        ('order', 'cancel_date', 'cancel_date', 'updated_at', 'datetime', '' ),
        ('order_item', 'id', 'id' ,'item_id', 'string', '' ),
        ('order_item', 'create_date', 'create_date', 'created_at', 'datetime', '' ),
        ('order_item', 'change_date', 'change_date', 'updated_at', 'datetime', '' ),
        ('order_item', 'quantity', 'quantity', 'qty_ordered', 'int', '' ),
        ('product', 'id', 'id', 'entity_id', 'string', '' ),
        ('product', 'create_date', 'create_date', 'created_at', 'datetime', '' ),
        ('product', 'change_date', 'change_date', 'updated_at', 'datetime', '' ),
        ('product', 'name', 'name', 'name', 'string', '' ),
        ('product', 'code', 'code', 'sku', 'string', '' );
");
$installer->endSetup();
