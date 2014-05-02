<?php

/**
 * Cart Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Cart extends Jirafe_Analytics_Model_Abstract implements Jirafe_Analytics_Model_Pagable
{
    protected $_fields = array('id', 'create_date', 'change_date', 'subtotal', 'total', 'total_tax', 'total_shipping', 'total_payment_cost', 'total_discounts', 'currency');

    /**
     * Convert cart array into JSON object
     *
     * @param  array $quote
     * @param  boolean $isEvent
     * @return mixed
     */
    public function getJson($quote=null, $isEvent=true)
    {
        if ($quote) {
            return json_encode($this->getArray($quote, $isEvent));
        } else {
            return false;
        }
    }

    /**
     * Create cart array of data required by Jirafe API
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param  boolean $isEvent
     * @return mixed
     */
    public function getArray($quote=null, $isEvent=true)
    {
        try {
            if (!$quote) {
                return false;
            }
            $data = array();
            $fieldMap = $this->_getFieldMap('cart', $quote);
            $currency = $fieldMap['currency']['magento'];
            $items = Mage::getModel('jirafe_analytics/cart_item')->getItems($quote['entity_id'], $quote['store_id'], $currency);

            $previousItems = $this->_getPreviousItems($quote['entity_id']);

            $data = array_merge(
                $this->_mapFields($fieldMap, $this->_fields),
                array(
                    'items' => $items,
                    'cookies' => $isEvent ? : (object)null,
                    'customer' => $this->_getCustomer($quote, false),
                    'previous_items' => $previousItems
                )
            );

            $helper = Mage::helper('jirafe_analytics');
            if ($helper->shouldConvertCurrency($currency)) {
                $baseCurrency = $helper->fetchBaseCurrencyCode();
                $data[$fieldMap["currency"]["api"]] = $baseCurrency;
                $data[$fieldMap["total"]["api"]] = $helper->convertCurrency($data[$fieldMap["total"]["api"]], $currency);
                $data[$fieldMap["total_tax"]["api"]] = $helper->convertCurrency($data[$fieldMap["total_tax"]["api"]], $currency);
                $data[$fieldMap["total_shipping"]["api"]] = $helper->convertCurrency($data[$fieldMap["total_shipping"]["api"]], $currency);
                $data[$fieldMap["total_payment_cost"]["api"]] = $helper->convertCurrency($data[$fieldMap["total_payment_cost"]["api"]], $currency);
                $data[$fieldMap["total_discounts"]["api"]] = $helper->convertCurrency($data[$fieldMap["total_discounts"]["api"]], $currency);
            }

            if ($isEvent && $visit = $this->_getVisit()) {
                $data['visit'] = $visit;
            }

            if ($isEvent && $cookies = $this->_getCookies()) {
                $data['cookies'] = $cookies;
            }

            Mage::getSingleton('core/session')->setJirafePrevQuoteId($quote['entity_id']);
            Mage::getSingleton('core/session')->setJirafePrevQuoteItems($items);

            return $data;
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Cart::getArray()', $e);
            return false;
        }
    }

    /**
     * Get items from previous instance of cart from session
     *
     * @param string $quoteId
     * @return mixed
     */
    protected function _getPreviousItems ($quoteId = null)
    {
        try {
            if ($quoteId == Mage::getSingleton('core/session')->getJirafePrevQuoteId()) {
                return Mage::getSingleton('core/session')->getJirafePrevQuoteItems();
            } else {
                return array();
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Cart::_getPreviousItems()', $e);
            return array();
        }
    }

    public function getDataType() {
        return Jirafe_Analytics_Model_Data_Type::CART;
    }

    /**
     * Create array of cart historical data
     *
     * @param string $filter
     * @return Zend_Paginator
     */
    public function getPaginator($websiteId, $lastId = null)
    {
        $columns = $this->_getAttributesToSelect('cart');
        $columns[] = 'store_id';

        /**
         * After an quote is converted to an order, tax, shipping
         * and discount values are added to quote. After these additions,
         * the quote represents the cart object.
         */

        if(($key = array_search('grand_total', $columns)) !== false) {
            unset($columns[$key]);
        }

        $columns[] = 'subtotal as grand_total';

        $collection = Mage::getModel('sales/quote')
            ->getCollection()
            ->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns($columns);
        $collection->order('main_table.entity_id ASC');

        if ($lastId) {
            $collection->where("main_table.entity_id > ?", $lastId);
        }

        return Zend_Paginator::factory($collection);
    }
}

