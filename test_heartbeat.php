<?php 
require 'app/Mage.php';
$app = Mage::app('default');

$response = Mage::getModel('jirafe_analytics/curl')->heartbeat();
Zend_Debug::dump( $response );