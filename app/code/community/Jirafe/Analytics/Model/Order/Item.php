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
     * @param array $items
     * @return mixed
     */
    
    public function getItems( $order = null )
    {
        try {
            if ($order) {
                
                $count = 1;
                $data = array();
                
                foreach( $order['items'] as $item ) {
                    
                    /**
                     * Get field map array
                     */
                    
                    $fieldMap = $this->_getFieldMap( 'order_item', $item );
                    
                    $data[] = array(
                        $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                        $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento'],
                        $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                        'order_item_number' => "$count",
                        $fieldMap['quantity']['api'] => $fieldMap['quantity']['magento'],
                        $fieldMap['price']['api'] => $fieldMap['price']['magento'],
                        $fieldMap['discount_price']['api'] => $fieldMap['discount_price']['magento'],
                        'product' => Mage::getModel('jirafe_analytics/product')->getArray( $item['product_id'], false )
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