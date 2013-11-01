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
       if ( $params['function'] === 'convert') {
           $this->_convert( $params );
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
       } else {
           $this->_critical(self::REQUEST_FUNCTION_INVALID);
       }
    }
    
    /**
     * Convert historical data into JSON
     *
     * @return array
     */
    protected function _convert( $params = null )
    {
        try {
            
            Mage::helper('jirafe_analytics')->overridePhpSettings( $params );
            
            $history = array();
            $history = array_merge($history, Mage::getModel('jirafe_analytics/cart')->getHistoricalData() );
            $history = array_merge($history, Mage::getModel('jirafe_analytics/category')->getHistoricalData() );
            $history = array_merge($history, Mage::getModel('jirafe_analytics/customer')->getHistoricalData() );
            $history = array_merge($history, Mage::getModel('jirafe_analytics/employee')->getHistoricalData() );
            $history = array_merge($history, Mage::getModel('jirafe_analytics/order')->getHistoricalData() );
            $history = array_merge($history, Mage::getModel('jirafe_analytics/product')->getHistoricalData() );
            
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
