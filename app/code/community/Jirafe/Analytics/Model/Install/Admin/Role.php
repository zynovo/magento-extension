<?php

/**
 * Install Admin Role Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 * 
 * 
 */

require_once 'Mage/Adminhtml/controllers/Permissions/RoleController.php';

class Jirafe_Analytics_Model_Install_Admin_Role extends Mage_Adminhtml_Permissions_RoleController
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
    public function create( $name = null )
    {
        try {
            $role = Mage::getModel('admin/roles')
                ->getCollection()
                ->addFieldToFilter('role_name',array('eq',$name))
                ->getFirstItem();
            
            if (!$role->getId()) {
                $resource = '__root__,admin/report,admin/report/jirafe_analytics,admin/system,admin/system/config,admin/system/config/jirafe_analytics';
                $role = Mage::getModel('admin/roles');
                $role->setName($name)
                    ->setRoleType('G')
                    ->setGwsIsAll(1);
                
                $role->save();
                 
                Mage::getModel("admin/rules")
                    ->setRoleId($role->getId())
                    ->setResources(explode(',',$resource))
                    ->saveRel();
            }
  
            return $role->getId();
        } catch (Exception $e) {
            Mage::throwException('ADMIN ROLE ERROR: Jirafe_Analytics_Model_Install_Admin_Role::create(): ' . $e->getMessage());
        }

    }
    
    
}
