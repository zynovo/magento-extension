<?php

/**
 * Batch Error Model
 *
 * If API attempt fails, store error message
 * 
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Batch_Error extends Jirafe_Analytics_Model_Abstract
{
    /**
     * Class construction & resource initialization
     */
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/batch_error');
    }
    
    /**
     * Write response for each API batch error
     * 
     * @param array $attempt         cURL reponse data for single API attempt
     * @param array $batchAttemptId  batch attempt key
     * @return boolean
     * @throws Exception if unable to save error to db
     */
    
    public function add( $attempt = null, $batchAttemptId = null )
    {
        try {
            if ( $attempt && $batchAttemptId ) {
                 
                /**
                 * Save error data into jirafe_analytics_batch_error table
                 */
                
                $this->setBatchId( $attempt['batch_id'] );
                $this->setBatchAttemptId( $batchAttemptId );
                $this->setResponse( $attempt['response'] );
                $this->setCreatedDt( $attempt['created_dt'] );
                $this->save();
                return true;
            } else {
                $this->_log( 'ERROR', 'Jirafe_Analytics_Model_Batch_Error::add()' , 'Empty attempt record.');
                return false;
            }
            
        } catch (Exception $e) {
            Mage::throwException('BATCH ERROR ERROR Jirafe_Analytics_Model_Batch_Attempt::add(): ' . $e->getMessage());
        }
    }
}