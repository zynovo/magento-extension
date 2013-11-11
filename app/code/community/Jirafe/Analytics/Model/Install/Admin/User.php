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

require_once 'Mage/Adminhtml/controllers/Permissions/UserController.php';

class Jirafe_Analytics_Model_Install_Admin_User extends Mage_Adminhtml_Permissions_UserController
{
    /**
     * Override parent constructor to mimic controller behavior
     */
    public function __construct()
    {
        $request = Mage::getSingleton('jirafe_analytics/install_request');
        $response = Mage::getSingleton('jirafe_analytics/install_response');
        parent::__construct ($request, $response, array());
      
    }
    
    /**
     * Create admin permissions role
     * 
     * @param string $name
     */
    public function create( $roleId = null, $apiRoleId = null, $password = null )
    {
        try {
         
            $username = Mage::getStoreConfig('jirafe_analytics/installer/admin_username');
            
            $user = Mage::getModel('admin/user')
                ->getCollection()
                ->addFieldToFilter('username',array('eq',$username))
                ->getFirstItem();
            
            
            if ( !$user->getId() ) {
                $data =  array(
                  'username'=> $username,
                  'firstname' => Mage::getStoreConfig('jirafe_analytics/installer/admin_firstname'),
                  'lastname' => Mage::getStoreConfig('jirafe_analytics/installer/admin_lastname'),
                  'email' => Mage::getStoreConfig('jirafe_analytics/installer/admin_email'),
                  'is_active' => Mage::getStoreConfig('jirafe_analytics/installer/admin_is_active'),
                  'password' => $password
                );
                
                $user = Mage::getModel('admin/user');
                $user->setData($data);
                $user->save();
            }
            
            $id = $user->getUserId();
            
            $user->setRoleIds(array( $roleId ))
                ->setRoleUserId( $id )
                ->saveRelations();
            
            $user->setApiRoleIds( array( $apiRoleId ) )
                ->setRoleUserId( $id )
                 ->saveRelations();
            
            return $user;
           
        } catch (Exception $e) {
            Mage::throwException('ADMIN ROLE ERROR: Jirafe_Analytics_Model_Install_Admin_User::create(): ' . $e->getMessage());
        }
    }

}
