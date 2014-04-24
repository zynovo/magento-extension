<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('jirafe_analytics/data')}
    DROP FOREIGN KEY `jirafe_analytics_data_ibfk_2`;
    ");
$installer->endSetup();
