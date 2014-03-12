<?php

/**
 * Order Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */
class Jirafe_Analytics_Model_Order extends Jirafe_Analytics_Model_Abstract implements Jirafe_Analytics_Model_Pagable
{

    /**
     * Create order array of data required by Jirafe API
     *
     * @param array $order
     * @param  boolean $isEvent
     * @return mixed
     */
    public function getArray($order = null, $isEvent = true)
    {
        try {
            $fieldMap = $this->_getFieldMap('order', $order);
            $baseCurrency = Mage::app()->getStore()->getBaseCurrencyCode();
            $currency = $baseCurrency != $fieldMap['currency']['magento'] ? $fieldMap['currency']['magento']: null;

            $data = null;
            if ($order['jirafe_status'] == 'cancelled') {
                $data = array(
                    'status' => 'cancelled',
                    $fieldMap['order_number']['api'] => $order['increment_id'],
                    $fieldMap['cancel_date']['api'] => $this->_formatDate( $order['updated_at'] )
                );
            } else if ($isEvent || in_array($order['status'], $this->getAllStatuses())) {
                $items = Mage::getModel('jirafe_analytics/order_item')->getItems($order['entity_id'], $updateCurrency);
                $previousItems = $isEvent ? $this->_getPreviousItems($order['entity_id']) : null;
                $data = array(
                    $fieldMap['order_number']['api']    => $fieldMap['order_number']['magento'],
                    $fieldMap['cart_id']['api']         => $fieldMap['cart_id']['magento'],
                    $fieldMap['order_date']['api']      => $fieldMap['order_date']['magento'],
                    $fieldMap['create_date']['api']     => $fieldMap['create_date']['magento'],
                    $fieldMap['change_date']['api']     => $fieldMap['change_date']['magento'],
                    $fieldMap['subtotal']['api']        => $fieldMap['subtotal']['magento'],
                    $fieldMap['total']['api']           => $fieldMap['total']['magento'],
                    $fieldMap['total_tax']['api']       => $fieldMap['total_tax']['magento'],
                    $fieldMap['total_shipping']['api']  => $fieldMap['total_shipping']['magento'],
                    $fieldMap['total_discounts']['api'] => $fieldMap['total_discounts']['magento'],
                    $fieldMap['currency']['api']        => $fieldMap['currency']['magento'],
                    'items'              => $items,
                    'status'             => $order['jirafe_status'],
                    'customer'           => $this->_getCustomer($order),
                    'previous_items'     => $previousItems ? $previousItems : array(),
                    'total_payment_cost' => 0
                );

                Mage::getSingleton('core/session')->setJirafePrevOrderId($order['entity_id']);
                Mage::getSingleton('core/session')->setJirafePrevOrderItems($items);
            }
            if ($currency) {
                $data = $this->_convertCurrency($fieldMap, $data, $currency, $baseCurrency);
            }
            return $data;

        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log("ERROR", __METHOD__, $e->getMessage());
            return false;
        }
    }

    protected function _convertCurrency($fieldMap, $data, $currency, $baseCurrency)
    {
        $converter = Mage::helper('jirafe_analytics');
        $data[$fieldMap["currency"]["api"]] = $baseCurrency;
        $data[$fieldMap["amount_paid"]["api"]] = $converter->convertCurrency($data[$fieldMap["amount_paid"]["api"]], $currency);
        $data[$fieldMap["amount_authorized"]["api"]] = $converter->convertCurrency($data[$fieldMap["amount_authorized"]["api"]], $currency);
        $data[$fieldMap["total"]["api"]] = $converter->convertCurrency($data[$fieldMap["total"]["api"]], $currency);
        $data[$fieldMap["subtotal"]["api"]] = $converter->convertCurrency($data[$fieldMap["subtotal"]["api"]], $currency);
        $data[$fieldMap["total_tax"]["api"]] = $converter->convertCurrency($data[$fieldMap["total_tax"]["api"]], $currency);
        $data[$fieldMap["total_shipping"]["api"]] = $converter->convertCurrency($data[$fieldMap["total_shipping"]["api"]], $currency);
        $data[$fieldMap["total_discounts"]["api"]] = $converter->convertCurrency($data[$fieldMap["total_discounts"]["api"]], $currency);
        return $data;
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
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Order::_getPreviousItems()', $e->getMessage(), $e);
            return false;
        }
    }

    /**
     * Convert order array into JSON object
     *
     * @param  array $order
     * @param  boolean $isEvent
     * @return mixed
     */
    public function getJson($order = null, $isEvent = true)
    {
        if ($order) {
            $array = $this->getArray( $order, $isEvent );
            if (!$array) {
                return false;
            }
            return json_encode( $array );
        } else {
            return false;
        }
    }

    public function getDataType() {
        return Jirafe_Analytics_Model_Data_Type::ORDER;
    }

    /**
     * Create array of order historical data
     *
     * @param int $websiteId
     * @param int $lastId
     * @return Zend_Paginator
     */
    public function getPaginator($websiteId, $lastId = null)
    {
        $columns = array_merge(
            $this->_getAttributesToSelect('order'),
            array('status', 'store_id', 'entity_id', 'customer_id', 'p.amount_paid', 'p.amount_authorized')
        );
        $storeIds = Mage::app()->getWebsite($websiteId)->getStoreIds();

        $orders = Mage::getModel('sales/order')
            ->getCollection()
            ->getSelect()
            ->joinLeft(array('p'=>Mage::getSingleton('core/resource')->getTableName('sales/order_payment')), 'main_table.entity_id = p.parent_id')
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns($columns)
            ->columns("IF(main_table.status = 'canceled' OR main_table.status = 'cancelled', 'cancelled', 'accepted') as jirafe_status")
            ->where("main_table.store_id in (?)", $storeIds)
            ->where("main_table.status in (?)", $this->getAllStatuses())
            ->order('main_table.entity_id ASC');

        if ($lastId) {
            $orders->where("main_table.entity_id > $lastId");
        }

        return Zend_Paginator::factory($orders);
    }

    protected function getCompleteStatus()
    {
        $config = Mage::getStoreConfig('jirafe_analytics/advanced/status_complete');

        return array_filter(array_map('trim', explode(",", $config)));
    }

    protected function getProcessingStatus()
    {
        $config = Mage::getStoreConfig('jirafe_analytics/advanced/status_processing');

        return array_filter(array_map('trim', explode(",", $config)));
    }

    protected function getAllStatuses()
    {
        return array_merge($this->getCompleteStatus(), $this->getProcessingStatus());
    }
}

