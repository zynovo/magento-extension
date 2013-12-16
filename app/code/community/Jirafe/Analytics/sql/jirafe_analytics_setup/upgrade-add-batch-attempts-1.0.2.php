<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('jirafe_analytics/batch')}
    `attempts` int(10) unsigned NOT NULL DEFAULT '0';
        ");
$installer->endSetup();
