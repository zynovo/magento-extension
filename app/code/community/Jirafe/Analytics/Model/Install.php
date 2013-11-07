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
           // $apiRoleId = $this->_getApiRoleId(Mage::getStoreConfig('jirafe_analytics/authentication/admin_role'));
           // $this->_setApi2AclRoles( $apiRoleId );
            $this->_setAdminRoleAndAcl(Mage::getStoreConfig('jirafe_analytics/authentication/api2_role'));
            $this->_setOauthCustomer(Mage::getStoreConfig('jirafe_analytics/authentication/oauth_customer'));
        } catch (Exception $e) {
            Zend_Debug::dump($e);
            Mage::throwException('DATA ERROR: Jirafe_Analytics_Model_Install::createCredentials(): ' . $e->getMessage());
        }
    }
    
    /**
     * Get Api Role Id. If role doesn't exist, create it 
     *
     * @return string
     */
    protected function _getApiRoleId( $roleName = null )
    {
        try {
            if ( $roleName) {
                $resource = Mage::getSingleton('core/resource');
                $data = $resource->getConnection('core_read')
                        ->fetchCol("SELECT `entity_id` FROM `api2_acl_role` WHERE `role_name` = '$roleName' LIMIT 1");
                
                if (count($data)) {
                    return $data[0];
                } else {
                    $resource->getConnection('core_write')
                        ->query("INSERT IGNORE INTO `api2_acl_role` (`created_at`, `role_name`) VALUES (CURDATE(), '$roleName')");
                    $id = $resource->getConnection('core_read')
                        ->fetchCol("SELECT LAST_INSERT_ID()");
                    return $id[0];
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            Mage::throwException('DATA ERROR: Jirafe_Analytics_Model_Install::_getApiRoleId(): ' . $e->getMessage());
        } 
    }
    
    /**
     * Get Admin Role Id. If role doesn't exist, createit
     *
     * @return string
     */
    protected function _setAdminRoleAndAcl( $roleName = null )
    {
        try {
            if ( $roleName) {
                return Mage::getModel('jirafe_analytics/install_admin_role')->create( $roleName );
            } else {
                return null;
            }
        } catch (Exception $e) {
            Mage::throwException('DATA ERROR: Jirafe_Analytics_Model_Install::_setAdminRoleAndAcl(): ' . $e->getMessage());
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
            if ( $roleName) {
                return Mage::getModel('jirafe_analytics/install_oauth_customer')->create( $name );
            } else {
                return null;
            }
        } catch (Exception $e) {
            Mage::throwException('DATA ERROR: Jirafe_Analytics_Model_Install::_setOauthCustomer(): ' . $e->getMessage());
        }
    }
    
    /**
     * Associate api role with acl roles
     *
     * @return boolean
     */
    protected function _setApi2AclRoles( $roleId = null )
    {
        try {
            if ( $roleId) {
                $roles = array(
                    array('map','retrieve'),
                    array('map','update'),
                    array('field','retrieve'),
                    array('history','update'),
                    array('batch','retrieve'),
                    array('batch_attempt','retrieve'),
                    array('batch_error','retrieve'),
                    array('data','retrieve'),
                    array('data_type','retrieve'),
                    array('log','retrieve')
                 );
                
                $connetion = Mage::getSingleton('core/resource')->getConnection('core_write');
                foreach ($roles as $role) {
                    $connetion->query(sprintf("INSERT INTO `api2_acl_rule` (`role_id`, `resource_id`, `privilege`) VALUES (%d,'%s','%s')",$roleId,$role[0],$role[1]));
                }
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            Mage::throwException('DATA ERROR: Jirafe_Analytics_Model_Install::_setApi2AclRoles(): ' . $e->getMessage());
        }
    }
}