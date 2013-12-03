<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    
    ALTER TABLE {$this->getTable('jirafe_analytics/data_type')} 
    ADD `table_name` varchar(64),
    ADD `id_field` varchar(32),
    ADD `last_id` int(10) DEFAULT NULL,
    ADD `captured_dt` datetime DEFAULT NULL;
    
    UPDATE {$this->getTable('jirafe_analytics/data_type')} SET `table_name` = 'sales_flat_quote', `id_field` = 'entity_id'  WHERE `type` = 'cart';
    
    UPDATE {$this->getTable('jirafe_analytics/data_type')} SET `table_name` = 'catalog_category_entity', `id_field` = 'entity_id'  WHERE `type` = 'category';
    
    UPDATE {$this->getTable('jirafe_analytics/data_type')} SET `table_name` = 'customer_entity', `id_field` = 'entity_id' WHERE `type` = 'customer';
    
    UPDATE {$this->getTable('jirafe_analytics/data_type')} SET `table_name` = 'sales_flat_order', `id_field` = 'entity_id'  WHERE `type` = 'order';
    
    UPDATE {$this->getTable('jirafe_analytics/data_type')} SET `table_name` = 'catalog_product_entity', `id_field` = 'entity_id'  WHERE `type` = 'product';
    
    UPDATE {$this->getTable('jirafe_analytics/data_type')} SET `table_name` = 'admin_user', `id_field` = 'user_id'   WHERE `type` = 'employee';
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/install')};
    CREATE TABLE {$this->getTable('jirafe_analytics/install')} (
        `id` int(10) unsigned NOT NULL ,
        `task` varchar(64) NOT NULL,
        `completed_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    INSERT INTO {$this->getTable('jirafe_analytics/install')} (`id`,`task`) VALUES
        (1, 'credentials'),
        (2, 'convert'),
        (3, 'batch'),
        (4, 'export');
    ");
    
    
    ");
$installer->endSetup();
