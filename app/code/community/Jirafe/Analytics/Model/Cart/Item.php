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
     * @param  string $quoteId
     * @param  string $storeId
     * @return mixed
     */
    
    public function getItems( $quoteId = null, $storeId = null )
    {
        try {
            if ($quoteId) {
                
                $columns = $this->_getAttributesToSelect( 'cart_item' );
                $columns[] = 'product_id';
                
                $collection = Mage::getModel('sales/quote_item')->getCollection()->getSelect();
                $collection->reset(Zend_Db_Select::COLUMNS)->columns( $columns);
                $collection->where( "`main_table`.`quote_id` = $quoteId" );
                
                $count = 1;
                $data = array();
                
                foreach($collection->query() as $item) {
                    
                    /**
                     * Get field map array
                     */
                    
                    $fieldMap = $this->_getFieldMap( 'cart_item', $item );
                    
                    $data[] = array(
                        $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                        $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento'],
                        $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                        'cart_item_number' => "$count",
                        $fieldMap['quantity']['api'] => $fieldMap['quantity']['magento'],
                        $fieldMap['price']['api'] => $fieldMap['price']['magento'],
                        $fieldMap['discount_price']['api'] => $fieldMap['discount_price']['magento'],
                        'product' => Mage::getModel('jirafe_analytics/product')->getArray( $item['product_id'], $storeId, false, null )
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