<?php

/**
 * Cart Item Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Cart_Item extends Jirafe_Analytics_Model_Cart
{
    /**
     * Create cart item array of data required by Jirafe API
     *
     * @param  Mage_Sales_Model_Quote $quote
     * @return mixed
     */
    
    public function getItems( $quote = null )
    {
        try {
            if ($quote) {
                $count = 1;
                $data = array();
                foreach($quote->getAllItems() as $item) {
                    $product = Mage::getModel('jirafe_analytics/product')->getArray( $item->getProductId(), $quote->getStoreId()  );
                    $previousItems = null;
                    $customer = null;
                    $visit = null;
                    $data[] = array(
                        'id' => $item->getItemId(),
                        'create_date' => $this->_formatDate( $item->getCreatedAt() ),
                        'change_date' => $this->_formatDate( $item->getUpdatedAt() ),
                        'cart_item_number' => $count,
                        'quantity' => $item->getQty(),
                        'price' => $this->_formatCurrency(  $item->getPrice() ),
                        'discount_price' => $this->_formatCurrency(  $item->getDiscountAmount() ),
                        'product' => $product
                    );
                    $count++;
                }
                return $data;
            } else {
                return array();
            }
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Cart_Item::getItems(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
}