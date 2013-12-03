<?php

/**
 * Api2 History Rest Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Api2_History_Rest extends Jirafe_Analytics_Model_Api2_History
{
    /**
     * History manipulation functions
     *
     * @return array
     */
    protected function _function( array $params )
    {
       Mage::log($params,null,'rest.log');
       if ( $params['function'] === 'convert') {
           if ( Mage::getModel('jirafe_analytics/data')->convertHistoricalData( $params ) ) {
             $this->_successMessage( self::HISTORY_CONVERT_FUNCTION_SUCCESSFUL, Mage_Api2_Model_Server::HTTP_OK );
           } else {
            $this->_critical( self::REQUEST_FUNCTION_NO_DATA );
           }
       } else if ( $params['function'] === 'batch') {
           if ( Mage::getModel('jirafe_analytics/data')->convertEventDataToBatchData( $params, true ) ) {
               $this->_successMessage( self::HISTORY_BATCH_FUNCTION_SUCCESSFUL, Mage_Api2_Model_Server::HTTP_OK );
           } else {
               $this->_critical( self::REQUEST_FUNCTION_NO_DATA );
           }
       } else if ( $params['function'] === 'export') {
           if ( Mage::getModel('jirafe_analytics/batch')->process( $params, true ) ) {
               $this->_successMessage( self::HISTORY_EXPORT_FUNCTION_SUCCESSFUL, Mage_Api2_Model_Server::HTTP_OK );
           } else {
               $this->_critical( self::REQUEST_FUNCTION_NO_DATA );
           }
       } else if ( $params['function'] === 'reset-data') {
           if ( Mage::getModel('jirafe_analytics/install')->resetData() ) {
               $this->_successMessage( self::RESET_DATA_FUNCTION_SUCCESSFUL, Mage_Api2_Model_Server::HTTP_OK );
           } else {
               $this->_critical( self::REQUEST_FUNCTION_ERROR );
           }
       } else {
           $this->_critical(self::REQUEST_FUNCTION_INVALID);
       }
    }
    
    /**
     * Convert historical data into JSON
     *
     * @return array
     */
    public function convert( $params = null )
    {
        try {
            
            Mage::helper('jirafe_analytics')->overridePhpSettings( $params );
            
            $element = isset($params['element'] ) ? trim( $params['element'] ) : null;
            $startDate = isset($params['start_date'] ) ? trim( $params['start_date'] ) : null;
            $endDate = isset($params['end_date'] ) ? trim( $params['end_date'] ) : null;
            
            /**
             * If element name not passed through parameters, convert all element types 
             */
            $all = $element ? false : true;
            
            $history = array();
            
            if ( $element === 'cart' || $all ) {
                $history = array_merge($history, Mage::getModel('jirafe_analytics/cart')->getHistoricalData( $startDate, $endDate ) );
            }
            
            if ( $element === 'category' || $all ) {
                $history = array_merge($history, Mage::getModel('jirafe_analytics/category')->getHistoricalData( $startDate, $endDate ) );
            }
            
            if ( $element === 'customer' || $all ) {
                $history = array_merge($history, Mage::getModel('jirafe_analytics/customer')->getHistoricalData( $startDate, $endDate ) );
            }
            
            if ( $element === 'employee' || $all ) {
                $history = array_merge($history, Mage::getModel('jirafe_analytics/employee')->getHistoricalData( $startDate, $endDate ) );
            }
            
            if ( $element === 'order' || $all ) {
                $history = array_merge($history, Mage::getModel('jirafe_analytics/order')->getHistoricalData( $startDate, $endDate ) );
            }
            
            if ( $element === 'product' || $all ) {
                $history = array_merge($history, Mage::getModel('jirafe_analytics/product')->getHistoricalData( $startDate, $endDate ) );
            }
            
            foreach ($history as $item) {
                $data = Mage::getModel('jirafe_analytics/data');
                $data->setStoreId( $item['store_id'] );
                $data->setTypeId( $item['type_id'] );
                $data->setHistorical( 1 );
                $data->setJson( $item['json'] );
                $data->setCapturedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                $data->save();
                $data = null;
            }
            
            $this->_successMessage( self::HISTORY_CONVERT_FUNCTION_SUCCESSFUL . ' ' . count($history) . ' records processed.', Mage_Api2_Model_Server::HTTP_OK );
            
        } catch (Exception $e) {
            $this->_critical('Historical data conversion error: '. $e->getMessage());
        }
    }
    
    
    /**
     * Retrieve history not available
     *
     * @return array
     */
    protected function _retrieve()
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
    /**
     * Create history not available
     *
     * @param array $data
     */
    protected function _create(array $data)
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
    
    /**
     * Update history not available
     *
     * @param array $data
     */
    protected function _update(array $data)
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
    
    /**
     * Delete history not available
     */
    protected function _delete()
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
    
}
