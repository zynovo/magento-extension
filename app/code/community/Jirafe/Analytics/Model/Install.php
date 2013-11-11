<?php

/**
 * Install Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 * 
 * 
 */

class Jirafe_Analytics_Model_Install extends Jirafe_Analytics_Model_Abstract
{
   
    /**
     * install module authentication credentials
     *
     * @return string
     */
    public function credentials()
    {
        try {
         
            $adminRoleID = Mage::getModel('jirafe_analytics/install_admin_role')->getId();
            
            $api2RoleId = Mage::getModel('jirafe_analytics/install_api2_role')->getId();
            
            $oauthSecret = Mage::getModel('jirafe_analytics/install_oauth_customer')->getSecret();
            
            if ($api2RoleId && $adminRoleID && $adminRoleID) {
               $user = Mage::getModel('jirafe_analytics/install_admin_user')->create( $adminRoleID, $api2RoleId, $oauthSecret);
               return true;
            } else {
               return false;
            }
            
        } catch (Exception $e) {
            return 'DATA ERROR: Jirafe_Analytics_Model_Install::credentials(): ' . $e->getMessage();
        }
    }
}