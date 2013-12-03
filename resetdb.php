<?php 
require 'app/Mage.php';
$app = Mage::app('default');


echo Mage::getModel('jirafe_analytics/install')->resetData();