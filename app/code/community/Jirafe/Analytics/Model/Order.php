<?php

/**
 * Order Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Order extends Jirafe_Analytics_Model_Abstract
{

    /**
     * Create order array of data required by Jirafe API
     *
     * @param array $order
     * @param  boolean $isEvent
     * @return mixed
     */

    public function getArray( $order = null, $isEvent = true )
    {
        try {
            /**
             * Get field map array
             */

            $fieldMap = $this->_getFieldMap( 'order', $order );

            if ($order['jirafe_status'] == 'cancelled') {

                $data = array(
                    $fieldMap['order_number']['api'] => $order['increment_id'],
                    'status' => $order['jirafe_status'],
                    $fieldMap['cancel_date']['api'] => $this->_formatDate( $order['updated_at'] )
                );

            } else {

                $items = Mage::getModel('jirafe_analytics/order_item')->getItems( $order['entity_id'], $order['store_id'] );
                $totalPaymentCost = is_numeric($order['amount_paid']) ? $order['amount_paid'] : ( is_numeric($order['amount_authorized']) ? $order['amount_authorized'] : 0);

                $previousItems =  $isEvent ? $this->_getPreviousItems( $order['entity_id'] ) : null;

                $data = array(
                    $fieldMap['order_number']['api'] => $fieldMap['order_number']['magento'],
                    $fieldMap['cart_id']['api'] => $fieldMap['cart_id']['magento'],
                    'status' => $order['jirafe_status'],
                    $fieldMap['order_date']['api'] => $fieldMap['order_date']['magento'],
                    $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento'],
                    $fieldMap['change_date']['api'] =>$fieldMap['change_date']['magento'],
                    $fieldMap['subtotal']['api'] => $fieldMap['subtotal']['magento'],
                    $fieldMap['total']['api'] => $fieldMap['total']['magento'],
                    $fieldMap['total_tax']['api'] => $fieldMap['total_tax']['magento'],
                    $fieldMap['total_shipping']['api'] => $fieldMap['total_shipping']['magento'],
                    'total_payment_cost' => floatval( $totalPaymentCost),
                    $fieldMap['total_discounts']['api'] => $fieldMap['total_discounts']['magento'],
                    $fieldMap['currency']['api'] => $fieldMap['currency']['magento'],
                    'items' => $items,
                    'previous_items' => $previousItems ? $previousItems : array(),
                    'customer' => $this->_getCustomer( $order )
                );

                Mage::getSingleton('core/session')->setJirafePrevOrderId( $order['entity_id'] );
                Mage::getSingleton('core/session')->setJirafePrevOrderItems( $items );
            }
            return $data;

        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR Jirafe_Analytics_Model_Order::getOrder(): ' . $e->getMessage());
            return false;
        }
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

    public function getJson( $order = null, $isEvent = true )
    {
        if ($order) {
            return json_encode( $this->getArray( $order, $isEvent ) );
        } else {
            return false;
        }

    }

    /**
     * Create array of product historical data
     *
     * @param string $filters
     * @return array
     */

    public function getHistoricalData( $filter = array() )
    {
        try {

            $lastId = isset($filter['last_id']) ? (is_numeric($filter['last_id']) ?  $filter['last_id'] : null): null;
            $startDate = isset($filter['start_date']) ? $filter['start_date'] : null;
            $endDate = isset($filter['end_date']) ? $filter['end_date'] : null;

            $columns = $this->_getAttributesToSelect( 'order' );
            $columns[] = 'store_id';
            $columns[] = 'entity_id';
            $columns[] = 'customer_id';
            $columns[] = 'p.amount_paid';
            $columns[] = 'p.amount_authorized';

            Mage::helper('jirafe_analytics')->log('DEBUG', 'Jirafe_Analytics_Model_Order::getHistoricalData()', 'Building order query', null);

            $orders = Mage::getModel('sales/order')
                ->getCollection()
                ->getSelect()
                ->joinLeft( array('p'=>Mage::getSingleton('core/resource')->getTableName('sales/order_payment')), 'main_table.entity_id = p.parent_id')
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns( $columns )
                ->columns( "IF(main_table.status = 'canceled' OR main_table.status = 'cancelled', 'cancelled', 'accepted') as jirafe_status");

            if ( $lastId ) {
                $where = "main_table.entity_id <= $lastId";
            } else if ( $startDate && $endDate ){
                $where = "created_at BETWEEN '$startDate' AND '$endDate'";
            } else if ( $startDate && !$endDate ){
                $where = "created_at >= '$startDate'";
            } else if ( !$startDate && $endDate ){
                $where = "created_at <= 'endDate'";
            } else {
                $where = null;
            }

            if ($where) {
                $orders->where( $where );
            }

            $data = array();

            //Mage::helper('jirafe_analytics')->log('DEBUG', 'Jirafe_Analytics_Model_Order::getHistoricalData()', 'Preparing pagination of order query', null);

            // Order Query
            //Mage::helper('jirafe_analytics')->log('DEBUG', 'Jirafe_Analytics_Model_Order::getHistoricalData()', 'Order Query: '. $orders->__toString(), null);

            // Pagination
            $currentPage = 1;

            $paginator = Zend_Paginator::factory($orders);
            $paginator->setItemCountPerPage(10)
                ->setCurrentPageNumber($currentPage);
            $pages = $paginator->count();

            $message = sprintf('Page Size: %d', $pages);
            Mage::helper('jirafe_analytics')->log('DEBUG', 'Jirafe_Analytics_Model_Order::getHistoricalData()', $message, null);

            do{
                //$message = sprintf('Iteration # %d', $currentPage);
                //Mage::helper('jirafe_analytics')->log('DEBUG', 'Jirafe_Analytics_Model_Order::getHistoricalData()', $message, null);

                $paginator->setCurrentPageNumber($currentPage);

                foreach($paginator as $order) {

                    $data[] = array(
                        'type_id' => Jirafe_Analytics_Model_Data_Type::ORDER,
                        'store_id' => $order['store_id'],
                        'json' => $this->getJson( $order, false )
                    );
                }
                $currentPage++;
                // 100 milliseconds
                usleep(100 * 1000);
            } while ($currentPage <= $pages);


            return $data;
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Order::getHistoricalData()', $e->getMessage(), $e);
            return false;
        }
    }
}
