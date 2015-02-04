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
     * @return array
     */
    public function getItems($orderId = null, $currency = null)
    {
        try {
            if ($orderId) {
                $columns = $this->_getAttributesToSelect('order_item');
                $columns[] = 'product_id';
                $columns[] = 'option.value as attributes';
                $columns[] = 'IF(main_table.row_total > 0, main_table.row_total, parent.row_total) AS row_total';
                $columns[] = 'IF(main_table.discount_amount > 0,main_table.discount_amount ,COALESCE(parent.discount_amount,0)) AS discount_amount';

                $collection = Mage::getModel('sales/order_item')
                    ->getCollection()
                    ->getSelect()
                    ->joinLeft(array('parent'=>Mage::getSingleton('core/resource')->getTableName('sales/order_item')), "main_table.parent_item_id = parent.item_id")
                    ->joinLeft(array('option'=>Mage::getSingleton('core/resource')->getTableName('sales/quote_item_option')), "parent.item_id = option.item_id AND option.code = 'attributes'",array('option.value'))
                    ->reset(Zend_Db_Select::COLUMNS)
                    ->columns( $columns )
                    ->where("main_table.order_id = ?", $orderId)
                    ->where("main_table.product_type != ? AND (parent.product_type != 'bundle' OR parent.product_type is null)", Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE);

                $count = 1;
                $data = array();
                $helper = Mage::helper('jirafe_analytics');

                foreach( $collection->query() as $item ) {
                    $fieldMap = $this->_getFieldMap( 'order_item', $item );

                    $price = floatval($item['row_total']);
                    $discount_price = floatval($item['discount_amount']);

                    $data[] = array(
                        $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                        $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento'],
                        $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                        'order_item_number' => "$count",
                        $fieldMap['quantity']['api'] => $fieldMap['quantity']['magento'],
                        'status' => 'accepted',
                        'price' => $price,
                        'discount_price' => $discount_price,
                        'product' => Mage::getModel('jirafe_analytics/product')->getArray(Mage::getModel('catalog/product')->load($item['product_id']), $item['attributes'])
                    );
                    try {
                        if ($helper->shouldConvertCurrency($currency)) {
                            $fieldMap['price'] = $helper->convertCurrency($price, $currency);
                            $fieldMap['discount_price'] = $helper->convertCurrency($price, $currency);
                        }
                    } catch (Exception $e) {
                        Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, $e->getMessage(), $e);
                        Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, "Error converting currency: $currency");
                    }
                    $count++;
                }
                return $data;
            } else {
                return array('error' => 'no items associated with order');
            }
        } catch (Exception $e) {
             Mage::throwException('ORDER ITEM ERROR Jirafe_Analytics_Model_Cart_Item::getItems(): ' . $e->getMessage());
        }
    }
}
