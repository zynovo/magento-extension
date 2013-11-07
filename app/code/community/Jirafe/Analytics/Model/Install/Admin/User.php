<?php

/**
 * Install Admin User Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 * 
 * 
 */

class Jirafe_Analytics_Model_Install_Admin_User extends Mage_Admin_Model_User
{
    
    protected $_username = 'jirafe';
    
    protected $_firstname = 'Jirafe';
    
    protected $_lastname = 'User';
    
    protected $_email = 'testuser1256@jirafe.com';
    
    protected $_password = 'password1234';
    
    public function getUsername()
    {
        return $this->_username;
    }
    
    public function getFirstname()
    {
        return $this->_firstname;
    }
    
    public function getLastName()
    {
        return $this->_lastname;
    }
    
    public function getEmail() 
    {
        return $this->_email;
    }
    
    public function getPassword() 
    {
        return "password1234";
    }
    
    public function getExtra() 
    {
        return "";
    }
    
    public function getUserId( $username ) {
        try {
            
                parent::_beforeSave();
                $this->save();
                return $this->getId();

           
            return $this;
        } catch (Exception $e) {
            Mage::throwException('INSTALLER ERROR: Jirafe_Analytics_Model_Install_User::getId(): ' . $e->getMessage());
        }
        
    }
}