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
     * Create order array of data required by Jirafe API
     *
     * @param array $order
     * @return mixed
     */
    
    public function getArray( $order = null, $cancelled = false )
    {
       // Mage::log($order->getData(),null,'order.log');
        try {
            if ($cancelled) {
                $data = array(
                    'order_number' => $order['increment_id'],
                    'status' => $order['status'],
                    'cancel_date' => $this->_formatDate( $order['updated_at'] )
                );
            } else {
                $items = Mage::getModel('jirafe_analytics/order_item')->getItems( $order );
                $data = array(
                    'order_number' => $order['increment_id'],
                    'cart_id' => $order['quote_id'],
                    'status' => $order['status'],
                    'order_date' => $this->_formatDate( $order['created_at'] ),
                    'create_date' => $this->_formatDate( $order['created_at'] ),
                    'change_date' => $this->_formatDate( $order['updated_at'] ),
                    'subtotal' => $this->_formatCurrency( $order['subtotal'] ),
                    'total' => $this->_formatCurrency( $order['grand_total'] ),
                    'total_tax' => $this->_formatCurrency( $order['tax_amount'] ),
                    'total_shipping' => $this->_formatCurrency( $order['shipping_amount'] ),
                    'total_payment_cost' => $this->_formatCurrency( $order['payment']['amount_paid'] ),
                    'total_discounts' => $this->_formatCurrency( $order['discount_amount'] ),
                    'currency' => $order['order_currency_code'],
                    'cookies' => null,
                    'items' => $items,
                    'previous_items' => $this->_getPreviousItems( $order['entity_id'] ),
                    'customer' => $this->_getCustomer( $order ),
                    'visit' => $this->_getVisit()
                );
                
                Mage::getSingleton('core/session')->setJirafePrevOrderId( $order['entity_id'] );
                Mage::getSingleton('core/session')->setJirafePrevOrderItems( $items );
            }
            return $data;
            
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Order::getOrder(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Get items from previous instance of order from session
     *
     * @param int $quoteId
     * @return array
     */
    
    protected function _getPreviousItems ( $orderId = null )
    {
        try {
            if ($orderId == Mage::getSingleton('core/session')->getJirafePrevOrderId()) {
                return Mage::getSingleton('core/session')->getJirafePrevOrderItems();
            } else {
                return array();
            }
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Order::_getPreviousItems(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    
    /**
     * Convert order array into JSON object
     *
     * @param  array $order
     * @return mixed
     */
    
    public function getJson( $order = null, $cancelled = false )
    {
        if ($order) {
            return json_encode( $this->getArray( $order, $cancelled ) );
        } else {
            return false;
        }
        
    }
}