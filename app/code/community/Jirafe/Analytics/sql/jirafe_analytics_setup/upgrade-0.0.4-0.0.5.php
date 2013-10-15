<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/map')};
    CREATE TABLE {$this->getTable('jirafe_analytics/map')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `object` varchar(16) NOT NULL, 
        `key` varchar(64) NOT NULL,
        `api` varchar(64) NOT NULL,
        `magento` varchar(64) NOT NULL,
        `type` varchar(16) NOT NULL,
        `created_ts` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    INSERT INTO {$this->getTable('jirafe_analytics/map')} (`object`,`key`,`api`,`magento`,`type`) VALUES 
        ('cart', 'id', 'id', 'entity_id', 'int'),
        ('cart', 'create_date', 'create_date', 'created_at', 'datetime'),
        ('cart', 'change_date', 'change_date', 'updated_at', 'datetime'),
        ('cart', 'subtotal', 'subtotal', 'subtotal', 'float'),
        ('cart', 'total', 'total', 'grand_total', 'float'),
        ('cart', 'total_tax', 'total_tax',null,'float'),
        ('cart', 'total_shipping', 'total_shipping',null,'float'),
        ('cart', 'total_payment_cost', 'total_payment_cost', 'grand_total', 'float'),
        ('cart', 'total_discounts', 'total_discounts',null,'float'),
        ('cart', 'currency', 'currency', 'quote_currency_code', 'string'),
        ('cart_item', 'id', 'id', 'item_id', 'int'),
        ('cart_item', 'create_date', 'create_date', 'created_at', 'datetime'),
        ('cart_item', 'change_date', 'change_date', 'updated_at', 'datetime'),
        ('cart_item', 'quantity', 'quantity', 'qty', 'int'),
        ('cart_item', 'price', 'price', 'price', 'float'),
        ('cart_item', 'discount_price', 'discount_price', 'discount_amount', 'float'),
        ('category', 'id','id', 'entity_id', 'int'),
        ('category', 'name','name', 'name', 'string'),
        ('category', 'change_date','change_date', 'updated_at', 'datetime'),
        ('category', 'create_date','create_date', 'created_at', 'datetime'),
        ('customer', 'id', 'id', 'entity_id', 'int'),
        ('customer', 'active_flag', 'active_flag', 'is_active', 'boolean'),
        ('customer', 'change_date', 'change_date', 'created_at', 'datetime'),
        ('customer', 'create_date', 'create_date', 'updated_at', 'datetime'),
        ('customer', 'email', 'email', 'email', 'string'),
        ('customer', 'first_name', 'first_name', 'firstname', 'string'),
        ('customer', 'last_name', 'last_name', 'lastname', 'string'),
        ('customer', 'active_flag', 'id', 'entity_id', 'int'),
        ('employee', 'id', 'active_flag', 'is_active', 'boolean'),
        ('employee', 'change_date', 'change_date', 'created_at', 'datetime'),
        ('employee', 'create_date', 'create_date', 'updated_at', 'datetime'),
        ('employee', 'email', 'email', 'email', 'string'),
        ('employee', 'first_name', 'first_name', 'firstname', 'string'),
        ('employee', 'last_name', 'last_name', 'lastname', 'string'),
        ('order', 'order_number', 'order_number', 'increment_id', 'string'),
        ('order', 'cart_id', 'cart_id', 'quote_id', 'int'),
        ('order', 'order_date', 'order_date', 'created_at', 'datetime'),
        ('order', 'create_date', 'create_date', 'created_at', 'datetime'),
        ('order', 'change_date', 'change_date', 'updated_at', 'datetime'),
        ('order', 'subtotal', 'subtotal', 'subtotal', 'float'),
        ('order', 'total', 'total', 'grand_total', 'float'),
        ('order', 'total_tax', 'total_tax', 'tax_amount', 'float'),
        ('order', 'total_shipping', 'total_shipping', 'shipping_amount', 'float'),
        ('order', 'total_payment_cost', 'total_payment_cost', 'amount_paid', 'float'),
        ('order', 'total_discounts', 'total_discounts', 'discount_amount', 'float'),
        ('order', 'currency', 'currency', 'order_currency_code', 'string'),
        ('order', 'cancel_date', 'cancel_date', 'updated_at', 'datetime'),
        ('order_item', 'id', 'id' ,'item_id', 'int'),
        ('order_item', 'create_date', 'create_date', 'created_at', 'datetime'),
        ('order_item', 'change_date', 'change_date', 'updated_at', 'datetime'),
        ('order_item', 'quantity', 'quantity', 'qty_ordered', 'int'),
        ('order_item', 'price', 'price', 'price', 'float'),
        ('order_item', 'discount_price', 'discount_price', 'discount_amount', 'float'),
        ('product', 'id', 'id', 'entity_id', 'int'),
        ('product', 'create_date', 'create_date', 'created_at', 'datetime'),
        ('product', 'change_date', 'change_date', 'updated_at', 'datetime'),
        ('product', 'name', 'name', 'name', 'string'),
        ('product', 'code', 'code', 'sku', 'string');
");
$installer->endSetup();