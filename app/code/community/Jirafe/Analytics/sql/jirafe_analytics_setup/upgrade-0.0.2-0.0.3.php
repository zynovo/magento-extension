<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    UPDATE {$this->getTable('jirafe_analytics/queue_type')} SET `description` = 'employee' WHERE `description` = 'user';
");
$installer->endSetup();
    