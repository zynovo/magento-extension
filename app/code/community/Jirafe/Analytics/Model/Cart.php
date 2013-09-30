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
     * Create JSON object for add item to cart events
     *
     * @param  Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function getItemAddJson( $observer )
    {
        try {
            $product = $observer->getProduct();
            
            $data = array(
                'product_id' => $product->getEntityId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'quantity' => $product->getQuantity(),
                'price' => $product->getPrice(),
                'quote_id' => $observer->getQuoteItem()->getQuote()->getEntityId(),
                'change_date' => $this->_formatDate( $product->getUpdatedAt() ),
                'create_date' => $this->_formatDate( $product->getCreatedAt() )
            );
            
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Cart::getItemAddJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Create JSON object for update item in cart events
     *
     * @param  Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function getItemUpdateJson( $observer )
    {
        try {
            $cart = $observer->getCart();
            
            $data = array(
                'quote_id' => $cart->getQuote()->getEntityId(),
                'change_date' => $this->_formatDate( $product->getUpdatedAt() ),
                'create_date' => $this->_formatDate( $product->getCreatedAt() )
            );
            
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Cart::getItemUpdateJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
   
}
    