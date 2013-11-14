<?php

/**
 * Data Attempt Model
 *
 * Store cURL response information for every attempt at sending data to Jirafe API
 * 
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Data_Attempt extends Jirafe_Analytics_Model_Abstract
{
    /**
     * Class construction & resource initialization
     */
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/data_attempt');
    }
    
    /**
     * Store data for each API data attempt 
     * 
     * @param array $attempt    cURL reponse data for single API attempt
     * @return boolean
     * @throws Exception if unable to save attempt to db
     */
    
    public function add( $attempt = null )
    {
        try {
            if ( $attempt ) {
                
                /**
                 * Save attempt data into jirafe_analytics_data_attempt table
                 */
                
                $this->setDataId( $attempt['data_id'] );
                $this->setHttpCode( $attempt['http_code'] );
                $this->setTotalTime( $attempt['total_time'] );
                $this->setCreatedDt( $attempt['created_dt'] );
                $this->save();
                
                /**
                 * If API error, create error record
                 */
                
                if ($attempt['http_code'] != '200') {
                    Mage::getSingleton('jirafe_analytics/data_error')->add( $attempt, $this->getId() );
                }
                
                return true;
            } else {
                Mage::helper('jirafe_analytics')->log( 'ERROR', 'Jirafe_Analytics_Model_Data_Attempt::add()' , 'Empty attempt record.');
                return false;
            }
        } catch (Exception $e) {
            Mage::throwException(' Jirafe_Analytics_Model_Data_Attempt::add(): ' . $e->getMessage());
        }
    }
}