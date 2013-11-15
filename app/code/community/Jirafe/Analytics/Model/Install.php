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
    * Run installer
    * 
    * @return string
    * Triggered by jirafe_analytics_installer cron
    */
    public function run()
    {
      try {
          
          /**
           * Check cache for previous run of installer
           */
          $cache = Mage::app()->getCache();
          $installer = $cache->load('jirafe_analytics_installer');
          
          if ( !$installer = $cache->load('jirafe_analytics_installer') ) {
              $response = $this->createCredentials();
              Mage::log($response,null,'installer.log');
              if ( $response === 'error' ) {
                  Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Install::run()', "Unable to install admin user.", null );
              } else {
                  Mage::helper('jirafe_analytics')->log( 'INSTALLER', 'Jirafe_Analytics_Model_Install::run()', 'Installed admin credentials', null );
              }
              $cache->save( $response, 'jirafe_analytics_installer', array('jirafe_analytics_cache'), null);
          } else {
              $response = 'credentials already installed';
          }
          
          return "installer=$installer response=$response";
          
       } catch (Exception $e) {
            return 'DATA ERROR: Jirafe_Analytics_Model_Install::run(): ' . $e->getMessage();
       }
    }
      
    /**
     * create authentication credentials for remote REST/Oauth access
     *
     * @return string
     */
    public function createCredentials()
    {
       if (!$adminRoleID = Mage::getSingleton('jirafe_analytics/install_admin_role')->getId()) {
           Mage::throwException('Error creating admin user role');
       }
       
       if (!$api2RoleId = Mage::getSingleton('jirafe_analytics/install_api2_role')->getId()) {
           Mage::throwException('Error creating api2 user role');
       }
       
       if (!$api2Attributes = Mage::getSingleton('jirafe_analytics/install_api2_attribute')->setAll()) {
           Mage::throwException('Error creating api2 attribures');
       }
       
       if (!$oauthSecret = Mage::getSingleton('jirafe_analytics/install_oauth_customer')->getSecret()) {
           Mage::throwException('Error creating oauth customer.');
       }
       
       if (!$user = Mage::getSingleton('jirafe_analytics/install_admin_user')->create( $adminRoleID, $api2RoleId, $oauthSecret)) {
           Mage::throwException('Error creating admin user.');
       }
       
       return 'Successfully installed admin credentials: ' . Mage::helper('jirafe_analytics')->getCurrentDt();
    }

}