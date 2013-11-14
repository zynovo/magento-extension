<?php

/**
 * Data Error Model
 *
 * If API attempt fails, store error message
 * 
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Data_Error extends Jirafe_Analytics_Model_Abstract
{
    /**
     * Class construction & resource initialization
     */
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/data_error');
    }
    
    /**
     * Write response for each API data error
     * 
     * @param array $attempt        cURL reponse data for single API attempt
     * @param array $dataAttemptId  data attempt key
     * @return boolean
     * @throws Exception
     */
    
    public function add( $attempt = null, $dataAttemptId = null )
    {
        try {
            if ( $attempt && $dataAttemptId ) {
                 
                /**
                 * Save error data into jirafe_analytics_data_error table
                 */
                
                $this->setDataId( $attempt['data_id'] );
                $this->setDataAttemptId( $dataAttemptId );
                $this->setResponse( $attempt['response'] );
                $this->setCreatedDt( $attempt['created_dt'] );
                $this->save();
                return true;
            } else {
                Mage::helper('jirafe_analytics')->log( 'ERROR', 'Jirafe_Analytics_Model_Data_Error::add()' , 'Empty attempt record.');
                return false;
            }
            
        } catch (Exception $e) {
            Mage::throwException('BATCH ERROR ERROR Jirafe_Analytics_Model_Data_Attempt::add(): ' . $e->getMessage());
        }
    }
}