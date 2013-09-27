<?php

/**
 * Admin User Model
 *
 * Rewrite of Mage_Admin_Model_User to create required observers
 * 
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */
class Jirafe_Analytics_Model_Admin_User extends Mage_Admin_Model_User
{
    
    /**
     * Delete admin user from database
     *
     * @return Mage_Core_Model_Abstract
     */
    
    public function delete()
    {
       parent::delete();
       Mage::dispatchEvent('admin_user_delete', array('object'=>$this));

    }
}