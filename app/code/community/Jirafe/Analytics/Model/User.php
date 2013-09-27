<?php

/**
 * User Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_User extends Jirafe_Analytics_Model_Abstract
{

    /**
     * Create JSON object for admin user add events
     *
     * @param Varien_Event_Observer $customer
     * @return mixed
     */
    
    public function getAddJson( $user )
    {
        try {
            $data = array(
                'user_id' => $user->getUserId(),
                'username' => $user->getUsername(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'email' => $user->getEmail(),
                'is_active' => $user->getIsActive(),
                'change_date' => $this->_formatDate( $user->getModified() ),
                'create_date' => $this->_formatDate( $user->getCreated() )
            );
            
            return 'USER::getAddJson json=' . json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_User::getAddJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Create JSON object for admin user modify events
     *
     * @param Varien_Event_Observer $customer
     * @return mixed
     */
    
    public function getModifyJson( $user )
    {
        try {
            $data = array(
                'user_id' => $user->getUserId(),
                'username' => $user->getUsername(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'email' => $user->getEmail(),
                'is_active' => $user->getIsActive(),
                'change_date' => $this->_formatDate( $user->getModified() ),
                'create_date' => $this->_formatDate( $user->getCreated() )
            );
            
            return 'USER::getModifyJson json=' . json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_User::getModifyJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Create JSON object for admin user delete events
     *
     * @param Varien_Event_Observer $customer
     * @return mixed
     */
    
    public function getDeleteJson( $user )
    {
        try {
            $data = array(
                'user_id' => $user->getUserId(),
                'username' => $user->getUsername(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'email' => $user->getEmail(),
                'is_active' => $user->getIsActive(),
                'change_date' => $this->_formatDate( $user->getModified() ),
                'create_date' => $this->_formatDate( $user->getCreated() )
            );
    
            return 'USER::getDeleteJson json=' . json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_User::getDeleteJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
}