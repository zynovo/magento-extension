<?php

/**
 * Cart Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Cart extends Jirafe_Analytics_Model_Abstract
{
    
    /**
     * Create cart array of data required by Jirafe API
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return mixed
     */
    
    public function getArray( $quote = null ) 
    {
        try {
            if ($quote) {
                $items = Mage::getModel('jirafe_analytics/cart_item')->getItems( $quote );
                $data = array(
                    'id' => $quote->getData('entity_id'),
                    'create_date' => $this->_formatDate( $quote->getData('created_at') ),
                    'change_date' => $this->_formatDate( $quote->getData('updated_at') ),
                    'subtotal' => $this->_formatCurrency($quote->getData('subtotal') ),
                    'total' => $this->_formatCurrency( $quote->getData('grand_total') ),
                    'total_tax' => '',
                    'total_shipping' => '',
                    'total_payment_cost' => '',
                    'total_discounts' => '',
                    'currency' => $quote->getData('quote_currency_code'),
                    'cookies' => '',
                    'items' => $items,
                    'previous_items' => $this->_getPreviousItems( $quote->getData('entity_id') ),
                    'customer' => $this->_getCustomer( $quote->getData() ),
                    'visit' => $this->_getVisit()
                    );
                
                Mage::getSingleton('core/session')->setJirafePrevQuoteId( $quote->getData('entity_id') );
                Mage::getSingleton('core/session')->setJirafePrevQuoteItems( $items );
                
                return $data;
            } else {
                return false;
            }
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Cart::getArray(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Get items from previous instance of cart from session
     * 
     * @param int $quoteId
     * @return mixed
     */
    
    protected function _getPreviousItems ( $quoteId = null )
    {
        try {
            if ($quoteId == Mage::getSingleton('core/session')->getJirafePrevQuoteId()) {
                return Mage::getSingleton('core/session')->getJirafePrevQuoteItems();
            } else {
                return array();
            }
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Cart::_getPreviousItems(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Convert cart array into JSON object
     *
     * @param  array $quote
     * @return mixed
     */
    
    public function getJson( $quote = null )
    {
        if ($quote) {
            return json_encode( $this->getArray($quote) );
        } else {
            return false;
        }
        
    }
}