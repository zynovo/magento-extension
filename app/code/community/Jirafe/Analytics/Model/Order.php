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
     * @param Mage_Sales_Model_Order $order
     * @return mixed
     */
    
    public function getArray( $order = null )
    {
        try {
            $items = Mage::getModel('jirafe_analytics/order_item')->getItems( $order );
            $data = array(
                'order_number' => $order->getIncrementId(),
                'cart_id' => $order->getData('quote_id'),
                'status' => $order->getData('status'),
                'order_date' => $this->_formatDate( $order->getData('created_at') ),
                'create_date' => $this->_formatDate( $order->getData('created_at') ),
                'change_date' => $this->_formatDate( $order->getData('updated_at') ),
                'subtotal' => $this->_formatCurrency( $order->getData('subtotal') ),
                'total' => $this->_formatCurrency( $order->getData('grand_total') ),
                'total_tax' => $this->_formatCurrency( $order->getData('tax_amount') ),
                'total_shipping' => $this->_formatCurrency( $order->getData('shipping_amount') ),
                'total_payment_cost' => $this->_formatCurrency( $order->getPayment()->getData('amount_paid') ),
                'total_discounts' => $this->_formatCurrency( $order->getData('discount_amount') ),
                'currency' => $order->getData('order_currency_code'),
                'cookies' => null,
                'items' => $items,
                'previous_items' => $this->_getPreviousItems( $order->getData('entity_id') ),
                'customer' => $this->_getCustomer( $order->getData() ),
                'visit' => $this->_getVisit()
            );
            
            Mage::getSingleton('core/session')->setJirafePrevOrderId( $order->getData('entity_id') );
            Mage::getSingleton('core/session')->setJirafePrevOrderItems( $items );
            
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
    
    public function getJson( $order = null )
    {
        if ($order) {
            return json_encode( $this->getArray( $order ) );
        } else {
            return false;
        }
        
    }
}