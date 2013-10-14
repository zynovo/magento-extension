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
                return array(
                    'id' => $customer->getData('entity_id'),
                    'active_flag' => is_null($customer->getData('is_active')) ? true : ($customer->getData('is_active') == '1' ? true : false),
                    'change_date' => $this->_formatDate( $customer->getData('created_at') ),
                    'create_date' => $this->_formatDate( $customer->getData('updated_at') ),
                    'email' => $customer->getData('email'),
                    'first_name' => $customer->getData('firstname'),
                    'last_name' => $customer->getData('lastname'),
                    'name' => $customer->getData('firstname') . ' ' .$customer->getData('lastname')
                    ,
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
}