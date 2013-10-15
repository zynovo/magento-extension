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
     * @param string $userId
     * @return mixed
     */
    
    public function getArray( $userId = null )
    {
        try {
            if ( $userId ) {
                $user = Mage::getModel('admin/user')->load( $userId );
                
                /**
                 * Get field map array
                 */
                
                $fieldMap = $this->_getFieldMap( 'employee', $user->getData() );
                
                return array(
                    $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                    $fieldMap['active_flag']['api'] => $fieldMap['active_flag']['magento'] ,
                    $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                    $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento'],
                    $fieldMap['email']['api'] => $fieldMap['email']['magento'],
                    $fieldMap['first_name']['api'] => $fieldMap['first_name']['magento'],
                    $fieldMap['last_name']['api'] => $fieldMap['last_name']['magento']
                );
            } else {
               return array();
            }
        } catch (Exception $e) {
            $this->_log( 'ERROR', 'Jirafe_Analytics_Model_Employee::getArray()', $e->getMessage());
            return false;
        }
    }
    
     /**
     * Convert employee array into JSON object
     *
     * @param string $userId
     * @return mixed
     */
    
    public function getJson( $userId = null )
    {
        if ($userId) {
            return json_encode( $this->getArray( $userId ) );
        } else {
            return false;
        }
        
    }
}