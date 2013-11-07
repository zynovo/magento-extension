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
            $apiRoleId = $this->_getApiRoleId('Jirafe');
            $this->_setApi2AclRoles( $apiRoleId );
            $this->_setAdminRoleAndAcl('Jirafe');
            
        } catch (Exception $e) {
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
                return Mage::getModel('jirafe_analytics/install_admin_role')->getId( $roleName );
            } else {
                return null;
            }
        } catch (Exception $e) {
            Mage::throwException('DATA ERROR: Jirafe_Analytics_Model_Install::_setAdminRoleAndAcl(): ' . $e->getMessage());
        }
    }
    /**
     * Get Admin User Id. If doesnt' exist, create new
     *
     * @return string
     */
    protected function _getAdminUserId( $username = null, $firstname = null, $lastname = null, $email = null, $password = null )
    {
        /*
        try {
            if ( $username ) {
                $resource = Mage::getSingleton('core/resource');
                $user = $resource->getConnection('core_read')
                    ->fetchCol("SELECT `user_id` FROM `admin_user` WHERE `username` = '$username' LIMIT 1");
                
                if (count($user)) {
                    return $user[0];
                } else {
                    $sql = sprintf("INSERT INTO `admin_user` (`firstname`, `lastname`, `email`, `username`, `password`, `created`, `modified`, `is_active`, `extra`) 
                                    VALUES ('%s', '%s', '%s', '%s', '%s', CURDATE(), CURDATE(), 1, 'N;')",
                                    $firstname,
                    $lastname,
                    $username,
                    $email;
                    )
                    $resource->getConnection('core_write')
                        ->query("INSERT INTO `admin_user` (`firstname`, `lastname`, `email`, `username`, `password`, `created`, `modified`, `is_active`, `extra`) 
VALUES ('Jirafe', 'User', 'test@jirafe.com', 'Jirafe2', 'c48b70a36c721f9e0bcc70a3c0edd0fa2d6b0831f3e8c6d36a292fa39395de22:6egPaEJRBCwehhNK9vbTcLJJI2VVjemh', '2013-11-05 21:53:42', '2013-11-05 21:53:42', 1, 'N;')
                                                        ");
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
        */
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