<?php

/**
 * Order Payment Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Order_Payment extends Jirafe_Analytics_Model_Order
{
    
    /**
     * Create array of payment values for order
     *
     * @param string $orderId
     * @return array
     */
    
    public function getPayment( $orderId = null )
    {
        try {
            if ($orderId) {
                $paymentColumns = $this->_getAttributesToSelect( 'order|payment' );
                
                return Mage::getModel('sales/order_payment')
                    ->getCollection()
                    ->getSelect()
                    ->reset(Zend_Db_Select::COLUMNS)
                    ->columns( $paymentColumns )
                    ->where("parent_id = $orderId")
                    ->query();
            } else {
                return array();
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Order_Payment::getPayment()', $e->getMessage());
            return false;
        }
    }
}