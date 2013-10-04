<?php

/**
 * Queue Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Queue extends Jirafe_Analytics_Model_Abstract
{

    public $maxAttempts = null;
    
    public $maxRecords = null;
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/queue');
        
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
     * Process queue of records via cron
     * Get all records that need to be sent to Jirafe and pass to api
     */
    public function process() 
    {
        $data = $this->getCollection()
            ->addFieldToSelect(array('content'))
            ->addFieldToFilter('`main_table`.`completed_dt`', array('is' => new Zend_Db_Expr('null')))
            ->addFieldToFilter('`main_table`.`content`', array('neq' => ''))
            ->getSelect()
            ->join( array('qt'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/queue_type')), '`main_table`.`type_id` = `qt`.`id`', array('qt.description as type'))
            ->order(array('main_table.created_dt ASC'));
        
        if (is_numeric($this->maxRecords)) {
            $data->limit($this->maxRecords);
        }
        
        $response = Mage::getModel('jirafe_analytics/api')->send( $data->query() );
        
        /**
         * Record API attempt. 
         * Update queue with information from attempt
         */
        foreach ($response as $batch) {
            foreach ($batch as $attempt) {
                $this->updateQueue( $attempt );
                Mage::getModel('jirafe_analytics/queue_attempt')->add( $attempt );
            }
        }
    }
    
    /**
     * Update queue with information from attempt
     */
    public function updateQueue( $attempt )
    {
        
        $queue = Mage::getModel('jirafe_analytics/queue')->load( $attempt['queue_id'] );
        $attemptNum = intval($queue->getAttemptCount()+1);
        $queue->setAttemptCount($attemptNum);
        if ($attempt['http_code'] == '200' || $attemptNum >= $this->maxAttempts) {
            $queue->setCompletedDt( $attempt['created_dt'] );
            
            if ($attempt['http_code'] == '200') {
                $queue->setSuccess( 1 );
            }
        }
        
        $queue->save();
    }
}