<?php 
require 'app/Mage.php';
$app = Mage::app('default');

$response = Mage::getModel('jirafe_analytics/queue')->process();