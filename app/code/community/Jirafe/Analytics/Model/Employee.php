<?php

/**
 * Employee Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Employee extends Jirafe_Analytics_Model_Abstract
{

    /**
     * Create user admin array of data required by Jirafe API
     *
     * @param Mage_Admin_Model_User $employee
     * @return mixed
     */
    
    public function getArray( $employee = null )
    {
        try {
            if ( $employee ) {
                return array(
                    'id' => $employee->getData('user_id'),
                    'active_flag' => is_null($employee->getData('is_active')) ? true : ($employee->getData('is_active') == '1' ? true : false),
                    'change_date' => $this->_formatDate( $employee->getData('modified') ),
                    'create_date' => $this->_formatDate( $employee->getData('created') ),
                    'first_name' => $employee->getData('firstname'),
                    'last_name' => $employee->getData('lastname'),
                    'email' => $employee->getData('email'),
                );
            } else {
               return array();
            }
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Employee::getArray(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
     /**
     * Convert employee array into JSON object
     *
     * @param array $employee
     * @return mixed
     */
    
    public function getJson( $employee = null )
    {
        if ($employee) {
            return json_encode( $this->getArray( $employee ) );
        } else {
            return false;
        }
        
    }
}