<?php

/**
 * Order Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Order extends Jirafe_Analytics_Model_Abstract
{
    /**
     * Create JSON object for order creation events
     *
     * @param  Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function getAddJson( $observer )
    {
        try {
            
            $order = $observer->getOrder();

            $items = array();
            foreach ($order->getAllItems() as $item) {
                $items[] = array(
                    'item_id' => $item->getItemId(),
                    'product_id' => $item->getProductId(),
                    'sku' => $item->getSku(),
                    'price' => $item->getPrice(),
                    'tax_amount' => $item->getTaxAmount(),
                    'row_total' => $item->getRowTotal());
            }
            
            $data = array(
                'order_id' => $order->getEntityId(),
                'order_number' => $order->getIncrementId(),
                'grand_total' => $order->getGrandTotal(),
                'shipping_amount' => $order->getShippingAmount(),
                'tax_amount' => $order->getTaxAmount(),
                'total_paid' => $order->getTotalPaid(),
                'discount_amount' => $order->setDiscountAmount(),
                'change_date' => $this->_formatDate( $order->getUpdatedAt() ),
                'create_date' => $this->_formatDate( $order->getCreatedAt() ),
                'items' => $items
            );
            
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Order::getAddJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }

    /**
     * Create JSON object for order cancel events
     *
     * @param  Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function getCancelJson( $observer )
    {
        try {
            $order = $observer->getOrder();
            $data = array(
                'order_id' => $order->getEntityId(),
                'order_number' => $order->getIncrementId(),
                'change_date' => $this->_formatDate( $order->getUpdatedAt() ),
                'create_date' => $this->_formatDate( $order->getCreatedAt() ),
            );
            
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Order::getCancelJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
}