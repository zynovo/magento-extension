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
     * Create Oauth admin credentials
     *
     * @return string
     */
    public function createCredentials()
    {
        try {
            $message = "Installing administrative credentials for Jirafe Analytics module . .  .<BR/><BR/>";
            
            $api2Role = Mage::getStoreConfig('jirafe_analytics/installer/api2_role');
            $adminRole = Mage::getStoreConfig('jirafe_analytics/installer/admin_role');
            $oauthCustomer = Mage::getStoreConfig('jirafe_analytics/installer/oauth_customer');
            
            if ( $this->_setApi2Role( $api2Role ) ) {
                $message .= "Successfully installed '$api2Role' Web Services Role.<BR/>";
            } else {
                $message .= "Failure installing '$api2Role' Web Services Role.<BR/>";
            }
            
            if ( $this->_setAdminRole( $adminRole ) ) {
                $message .= "Successfully installed '$adminRole' Admin Permissions Role.<BR/>";
            } else {
                $message .= "Failure installing '$adminRole' Admin Permissions Role.<BR/>";
            }
            
            if ( $this->_setOauthCustomer( $oauthCustomer ) ) {
                $message .= "Successfully installed '$oauthCustomer' Oauth Customer.<BR/>";
            } else {
                $message .= "Failure installing '$oauthCustomer' Oauth Customer.<BR/>";
            }
            return $message;
        } catch (Exception $e) {
            return 'DATA ERROR: Jirafe_Analytics_Model_Install::credentials(): ' . $e->getMessage();
        }
    }
    
    /**
     * Get Api Role Id. If role doesn't exist, create it 
     *
     * @return string
     */
    protected function _setApi2Role( $name = null )
    {
        try {
            if ( $name) {
                return Mage::getModel('jirafe_analytics/install_api2_role')->create( $name );
            } else {
                return null;
            }
        } catch (Exception $e) {
            Mage::throwException('DATA ERROR: Jirafe_Analytics_Model_Install::_setApi2Role(): ' . $e->getMessage());
        } 
    }
    
    /**
     * Get Admin Role Id. If role doesn't exist, createit
     *
     * @return string
     */
    protected function _setAdminRole( $name = null )
    {
        try {
            if ( $name) {
                return Mage::getModel('jirafe_analytics/install_admin_role')->create( $name );
            } else {
                return null;
            }
        } catch (Exception $e) {
            Mage::throwException('DATA ERROR: Jirafe_Analytics_Model_Install::_setAdminRole(): ' . $e->getMessage());
        }
    }
    
    /**
     * Set Oauth Customer. If role doesn't exist, createit
     *
     * @return string $name
     */
    protected function _setOauthCustomer( $name = null )
    {
        try {
            if ( $name) {
                return Mage::getModel('jirafe_analytics/install_oauth_customer')->create( $name );
            } else {
                return null;
            }
        } catch (Exception $e) {
            Mage::throwException('DATA ERROR: Jirafe_Analytics_Model_Install::_setOauthCustomer(): ' . $e->getMessage());
        }
    }
    
}