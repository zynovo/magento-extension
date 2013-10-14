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
    
    public function getArray( $order = null )
    {
        try {
            
            $status = $this->_mapOrderStatus( $order['status'] );
            
            if ($status == 'cancelled') {
                $data = array(
                    'order_number' => $order['increment_id'],
                    'status' => $status,
                    'cancel_date' => $this->_formatDate( $order['updated_at'] )
                );
            } else {
                $items = Mage::getModel('jirafe_analytics/order_item')->getItems( $order );
                $data = array(
                    'order_number' => $order['increment_id'],
                    'cart_id' => $order['quote_id'],
                    'status' => $status,
                    'order_date' => $this->_formatDate( $order['created_at'] ),
                    'create_date' => $this->_formatDate( $order['created_at'] ),
                    'change_date' => $this->_formatDate( $order['updated_at'] ),
                    'subtotal' => floatval( $order['subtotal'] ),
                    'total' => floatval( $order['grand_total'] ),
                    'total_tax' => floatval( $order['tax_amount'] ),
                    'total_shipping' => floatval( $order['shipping_amount'] ),
                    'total_payment_cost' => floatval( $order['payment']['amount_paid'] ),
                    'total_discounts' => floatval( $order['discount_amount'] ),
                    'currency' => $order['order_currency_code'],
                    'cookies' => (object) null,
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
            $this->_log('ERROR Jirafe_Analytics_Model_Order::getOrder(): ' . $e->getMessage());
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
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Order::_getPreviousItems()', $e->getMessage());
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
    
    /**
     * Map Magento order status values to Jirafe API values
     *
     * @param  string $status
     * @return string
     */
    
    protected function _mapOrderStatus( $status )
    {
        switch ( $status ) {
            case 'pending':
                return 'placed';
                break;
            case 'complete':
                return 'accepted';
                break;
            case 'canceled':
                return 'cancelled';
                break; 
            default:
                return $status;
                break;
        }
    }
}