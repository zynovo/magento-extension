<?php

/**
 * Queue Error Model
 *
 * If API attempt fails, store error message
 * 
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Queue_Error extends Jirafe_Analytics_Model_Abstract
{
    /**
     * Class construction & resource initialization
     */
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/queue_error');
    }
    
    /**
     * Write response for each API queue error
     * 
     * @param array $attempt         cURL reponse data for single API attempt
     * @param array $queueAttemptId  queue attempt key
     * @return boolean
     * @throws Exception if unable to save error to db
     */
    
    public function add( $attempt = null, $queueAttemptId = null )
    {
        try {
            if ( $attempt && $queueAttemptId ) {
                 
                /**
                 * Save error data into jirafe_analytics_queue_error table
                 */
                
                $this->setQueueId( $attempt['queue_id'] );
                $this->setQueueAttemptId( $queueAttemptId );
                $this->setResponse( $attempt['response'] );
                $this->setCreatedDt( $attempt['created_dt'] );
                $this->save();
                return true;
            } else {
                $this->_log( 'ERROR', 'Jirafe_Analytics_Model_Queue_Error::add()' , 'Empty attempt record.');
                return false;
            }
            
        } catch (Exception $e) {
            Mage::throwException('QUEUE ERROR ERROR Jirafe_Analytics_Model_Queue_Attempt::add(): ' . $e->getMessage());
        }
    }
}