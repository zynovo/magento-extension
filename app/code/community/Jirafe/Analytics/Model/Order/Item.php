<?php

/**
 * Order Item Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Order_Item extends Jirafe_Analytics_Model_Order
{
    
    /**
     * Create array of items in order
     *
     * @param  Mage_Sales_Model_Quote $quote
     * @return mixed
     */
    
    public function getItems( $order = null )
    {
        try {
            if ($order) {
                $count = 1;
                $data = array();
                foreach($order->getAllItems() as $item) {
                    $product = Mage::getModel('jirafe_analytics/product')->getArray( $item->getProductId(), $order->getStoreId()  );
                    $previousItems = null;
                    $customer = null;
                    $visit = null;
                    $data[] = array(
                        'id' => $item->getData('item_id'),
                        'create_date' => $this->_formatDate( $item->getData('created_at') ),
                        'change_date' => $this->_formatDate( $item->getData('updated_at') ),
                        'order_item_number' => $count,
                        'quantity' => $item->getData('qty_ordered'),
                        'price' => $this->_formatCurrency( $item->getData('price') ),
                        'discount_price' => $this->_formatCurrency( $item->getData('discount_amount') ),
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