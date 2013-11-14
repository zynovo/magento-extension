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
     * @param array $data              data for attempt including cURL reponse
     * @param string $dataAttemptId    id from jirafe_analytics_data_attempt
     * @return boolean
     * @throws Exception
     */
    
    public function add( $data = null, $attemptId = null )
    {
        try {
            if ( $data && is_numeric($attemptId) ) {
                /**
                 * Save response data into jirafe_analytics_data_error table
                 */
                $this->setDataId( $data['data_id'] );
                $this->setDataAttemptId( $attemptId );
                $this->setErrorType( $data['error_type'] );
                $this->setErrors( $data['errors'] );
                $this->setCreatedDt( $data['created_dt'] );
                $this->save();
                return true;
            } else {
                Mage::helper('jirafe_analytics')->log( 'ERROR', 'Jirafe_Analytics_Model_Data_Error::add()' , 'Empty attempt error record.');
                return false;
            }
            
        } catch (Exception $e) {
            Mage::throwException('BATCH ERROR ERROR Jirafe_Analytics_Model_Data_Attempt::add(): ' . $e->getMessage());
        }
    }
}