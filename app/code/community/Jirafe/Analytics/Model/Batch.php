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
                
                Mage::getSingleton('jirafe_analytics/data')->convertEventDataToBatchData( $params, false );
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
                        Mage::getSingleton('jirafe_analytics/batch_attempt')->add( $attempt );
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
            Mage::throwException('ERROR', 'Jirafe_Analytics_Model_Batch::process()' . $e->getMessage());
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
                $batch = Mage::getSingleton('jirafe_analytics/batch')->load( $attempt['batch_id'] );
                $attemptNum = intval($batch->getAttemptCount()+1);
                $batch->setAttemptCount($attemptNum);
                
                if ($attempt['http_code'] == '200' || $attemptNum >= $this->maxAttempts) {
                    $batch->setCompletedDt( $attempt['created_dt'] );
                    
                    if ($attempt['http_code'] == '200') {
                        $batch->setSuccess( 1 );
                    }
                }
                
                $batch->save();
                return true;
            } else {
                Mage::helper('jirafe_analytics')->log( 'ERROR', 'Jirafe_Analytics_Model_Batch::updateBatch()' ,'attempt object null');
                return false;
            }
        } catch (Exception $e) {
             Mage::throwException('ERROR', 'Jirafe_Analytics_Model_Batch::updateBatch()'  . $e->getMessage());
        }
    }
    
    /**
     * Purge data
     *
     * @return boolean
     * @throws Exception if failure to purge data
     */
    public function purgeData()
    {
        try {
            $minutes = Mage::getStoreConfig('jirafe_analytics/general/purge_time');
            if ( is_numeric($minutes) ) {
                $resource = Mage::getSingleton('core/resource');
                $sql = sprintf("DELETE FROM %s WHERE TIMESTAMPDIFF(MINUTE,`completed_dt`,'%s') > %d",
                                    $resource->getTableName('jirafe_analytics/batch'),
                                    Mage::helper('jirafe_analytics')->getCurrentDt(),
                                    $minutes);
                $connection = $resource->getConnection('core_write')->query($sql);
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            Mage::throwException('DATA ERROR: Jirafe_Analytics_Model_Data::purge(): ' . $e->getMessage());
        }
    }
}