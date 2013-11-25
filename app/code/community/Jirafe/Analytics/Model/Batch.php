<?php

/**
 * Batch Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 * 
 * @property int $maxRecords    maximum number of records to process
 * @property int $maxAttempts   maximum attempts before failure
 * @property int $maxSize       maximum size of json object in bytes
 * 
 */

class Jirafe_Analytics_Model_Batch extends Jirafe_Analytics_Model_Abstract
{
    
    protected $_maxAttempts = null;
    
    protected $_maxRecords = null;
    
    /**
     * Class construction & resource initialization
     *
     * Load user configurable variables from Mage::getStoreConfig() into object property scope
     */
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/batch');
        
        /**
         * User configurable maximum number of attempts with error before marking record as failure
         */
        $this->maxAttempts =  intval(Mage::getStoreConfig('jirafe_analytics/curl/max_attempts'));
        
        /**
         * User configurable maximum number of records per processing
         */
        $this->maxRecords = intval(Mage::getStoreConfig('jirafe_analytics/curl/max_records'));
        
    }
    
    /**
     * Process batch of records via cron or direct call
     * Get all records that need to be sent to Jirafe and pass to api
     * 
     * @param array $params        params for overriding default collection filters
     * @param boolean $historical  select historical or current event data
     * @throws Exception if unable to process batch
     */
    
    public function process( $params = null, $historical = false ) 
    {
        try {
            
            if ( $historical ) {
                
                $historicalEQ = 'eq';
                
                /**
                 * Batch conversion done as separate step for historical data
                 */
                
            } else {
                
                $historicalEQ = 'neq';
                
                /**
                 * Convert data into batches before processing
                 */
                
                Mage::getModel('jirafe_analytics/data')->convertEventDataToBatchData( $params, false );
            }
            
            if (isset($params['max_records'])) {
                $this->maxRecords = $params['max_records'];
            }
            
            $data = $this->getCollection()
                ->addFieldToSelect(array('json','store_id'))
                ->addFieldToFilter('`main_table`.`completed_dt`', array('is' => new Zend_Db_Expr('null')))
                ->addFieldToFilter('`main_table`.`json`', array('neq' => ''))
                ->addFieldToFilter('`main_table`.`historical`', array($historicalEQ => '1'))
                ->setOrder('created_dt ASC')
                ->getSelect();
            
            if (is_numeric($this->maxRecords)) {
                $data->limit( $this->maxRecords );
            }
            
            /**
             * Record API attempt. 
             * Update batch with information from attempt
             */
            if (  $response = Mage::getSingleton('jirafe_analytics/curl')->sendJson( $data->query(), $params ) ) {
                foreach ($response as $batch) {
                    foreach ($batch as $attempt) {
                        $this->updateBatch( $attempt );
                        Mage::getModel('jirafe_analytics/data_attempt')->add( $attempt );
                    }
                }
                return true;
            } else {
                /**
                 * No data to process
                 */
                return false;
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Batch::process()', $e->getMessage(), $e);
            return false;
        }
    }
    
    /**
     * Update batch with information from attempt
     *  
     * @param array $attempt    cURL attempt data
     * @return boolean
     * @throws Exception if unable to update jirafe_analytics_batch
     */
    
    public function updateBatch( $attempt = null )
    {
        try {
            if ($attempt) {
                $batch = Mage::getModel('jirafe_analytics/batch')->load( $attempt['batch_id'] );
                if ($batch) {
                    $batch->setHttpCode( $attempt['http_code'] );
                    $batch->setTotalTime( $attempt['total_time'] );
                    $batch->setCompletedDt( $attempt['created_dt'] );
                    $batch->save();
                } else {
                    return true;
                }
            } else {
                Mage::helper('jirafe_analytics')->log( 'ERROR', 'Jirafe_Analytics_Model_Batch::updateBatch()' ,'attempt object null');
                return false;
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Batch::updateBatch()', $e->getMessage(), $e);
            return false;
        }
    }
    
}