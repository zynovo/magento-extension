<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    INSERT INTO {$this->getTable('jirafe_analytics/map')} ( `element`, `key`, `api`, `magento`, `type`, `default` ) VALUES 
        ('customer', 'company', 'company', 'company', 'string', '' ),
        ('customer', 'phone', 'phone', 'telephone', 'string', '' );
");
$installer->endSetup();
