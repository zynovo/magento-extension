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
                  * Get data ids associated with batch
                  */
                 $batch = Mage::getSingleton('jirafe_analytics/batch_data')
                     ->getCollection()
                     ->addFieldToFilter('batch_id',array('eq',$attempt['batch_id']))
                     ->load()
                     ->getData();
                 
                 /**
                  * Separate API responses in order of batch items
                  */
                 $response = array();
                 
                 foreach( json_decode($attempt['response'],true) as $key => $value) {
                     $response = array_merge($response,$value);
                 }
                 
                 /**
                  *  Counter var to tie order of data in response json to batch_order
                  */
                 $pos = 0;
                
                 foreach ($batch as $data) {
                  
                     /**
                      * Append response and attempt to data object
                      */
                     $data = array_merge( $data,$response[$pos] );
                     $data['success'] = isset($data['success']) ? $data['success'] : false;
                     $data['created_dt'] = $attempt['created_dt'];
                     
                     /**
                      *  Create record of attempt
                      */
                     $obj = new $this;
                     $obj->setDataId( $data['data_id'] );
                     $obj->setCreatedDt( $data['created_dt'] );
                     $obj->save();
                     
                     /**
                      *   Update data element with success or failure
                      */
                     $element = Mage::getSingleton('jirafe_analytics/data')->load( $data['data_id'] );
                     $element->setAttemptCount( intval( $element->getAttemptCount() ) + 1);
                     $element->setSuccess( $data['success'] ? 1 : 0 );
                     $element->setCompletedDt( $data['success'] ? $data['created_dt'] : null);
                     $element->save();
                     
                     /**
                      * If API failure, create error record
                      */
                     if ( !$data['success'] ) {
                         $data['errors'] = isset($data['errors']) ? json_encode( $data['errors'] ) : null;
                         $data['error_type'] = isset($data['error_type']) ? $data['error_type'] : null;
                         Mage::getModel('jirafe_analytics/data_error')->add( $data, $obj->getId() );
                     }
                     
                     $pos = $pos +1;
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