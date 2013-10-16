<?php 
require 'app/Mage.php';
$app = Mage::app('default');

$request = $app->getRequest();


echo '<h2>VALIDATE JSON</h2><h3>PARAM OPTIONS</h3>validate_json.php?orderId=17&cartId=51&productId=146&categoryId=3&customerId=18&employeeId=1 <HR>';

if ( $request->getParam('orderId') ) {
    echo '<h4>Order</h4>';
    $obj = Mage::getModel('sales/order')->load( $request->getParam('orderId'));
    $order = $obj->getData();
    $order['payment'] = $obj->getPayment()->getData();
    $order['items'] = array();
    foreach($obj->getAllVisibleItems() as $item) {
        $order['items'][] = $item->getData();
    }
    
    echo  Mage::getModel('jirafe_analytics/order')->getJson($order);
    echo "<HR>";
}

if ( $request->getParam('cartId') ) {
    echo '<h4>Cart</h4>';
    $quote = Mage::getModel('sales/quote')->load( $request->getParam('cartId') );
    echo  Mage::getModel('jirafe_analytics/cart')->getJson($quote);
    echo "<HR>";
}

if ( $request->getParam('categoryId') ) {
    echo '<h4>Category</h4>';
    $category = Mage::getModel('catalog/category')->load($request->getParam('categoryId'));
    echo  Mage::getModel('jirafe_analytics/category')->getJson( $category );
    echo "<HR>";
}

if ( $request->getParam('productId') ) {
    echo '<h4>Product</h4>';
    echo  Mage::getModel('jirafe_analytics/product')->getJson( $request->getParam('productId') , $request->getParam('siteId') );
    echo "<HR>";
}

if ( $request->getParam('customerId') ) {
    echo '<h4>Customer</h4>';
    $customer = Mage::getModel('customer/customer')->load( $request->getParam('customerId') );
    echo  Mage::getModel('jirafe_analytics/customer')->getJson( $customer );
    echo "<HR>";
}

if ( $request->getParam('employeeId') ) {
    echo '<BR/><h4>Employee</h4>';
    echo  Mage::getModel('jirafe_analytics/employee')->getJson( $request->getParam('employeeId') );
    echo "<HR>";
}

