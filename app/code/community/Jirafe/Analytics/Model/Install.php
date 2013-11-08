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
     * @return boolean
     */
    public function createCredentials()
    {
        try {
            $this->_setApi2Role(Mage::getStoreConfig('jirafe_analytics/installer/api2_role'));
            $this->_setAdminRole(Mage::getStoreConfig('jirafe_analytics/installer/admin_role'));
            $this->_setOauthCustomer(Mage::getStoreConfig('jirafe_analytics/installer/oauth_customer'));
            return true;
        } catch (Exception $e) {
            Zend_Debug::dump($e);
            Mage::throwException('DATA ERROR: Jirafe_Analytics_Model_Install::credentials(): ' . $e->getMessage());
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