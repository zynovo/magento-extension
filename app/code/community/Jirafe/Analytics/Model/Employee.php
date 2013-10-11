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
                return array(
                    'id' => $user->getData('user_id'),
                    'active_flag' => is_null($user->getData('is_active')) ? true : ($user->getData('is_active') == '1' ? true : false),
                    'change_date' => $this->_formatDate( $user->getData('modified') ),
                    'create_date' => $this->_formatDate( $user->getData('created') ),
                    'first_name' => $user->getData('firstname'),
                    'last_name' => $user->getData('lastname'),
                    'email' => $user->getData('email'),
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