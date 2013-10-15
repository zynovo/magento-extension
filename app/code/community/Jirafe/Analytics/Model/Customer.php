<?php

/**
 * Customer Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Customer extends Jirafe_Analytics_Model_Abstract
{

    /**
     * Create user admin array of data required by Jirafe API
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return mixed
     */
    
    public function getArray( $customer = null )
    {
        try {
            if ( $customer ) {
                
                   
                /**
                 * Get field map array
                 */
                
                $fieldMap = $this->_getFieldMap( 'customer', $customer->getData() );
                
                return array(
                    $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                    $fieldMap['active_flag']['api'] => $fieldMap['active_flag']['magento'] ,
                    $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                    $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento'],
                    $fieldMap['email']['api'] => $fieldMap['email']['magento'],
                    $fieldMap['first_name']['api'] => $fieldMap['first_name']['magento'],
                    $fieldMap['last_name']['api'] => $fieldMap['last_name']['magento'],
                    $fieldMap['name']['api'] => $customer->getData('firstname') . ' ' .$customer->getData('lastname')
                );
            } else {
               return array();
            }
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Customer::getArray()', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convert customer array into JSON object
     *
     * @param array $customer
     * @return mixed
     */
    
    public function getJson( $customer = null )
    {
        if ($customer) {
            return json_encode( $this->getArray( $customer ) );
        } else {
            return false;
        }
        
    }
    
    /**
     * Get customer array for beacon api javascript
     *
     * @return array
     */
    
    public function getCustomer()
    {
        return $this->_getCustomer();
    
    }
}