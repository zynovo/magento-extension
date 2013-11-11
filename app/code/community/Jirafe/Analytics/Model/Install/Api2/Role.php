<?php

/**
 * Install Api2 Role Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 * 
 * 
 */

require_once 'Mage/Api2/controllers/Adminhtml/Api2/RoleController.php';

class Jirafe_Analytics_Model_Install_Api2_Role extends Mage_Api2_Adminhtml_Api2_RoleController
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
     * Get id for api2 role or create new
     * 
     * @param string $name
     */
    public function getId()
    {
        try {
         
            $name = Mage::getStoreConfig('jirafe_analytics/installer/api2_role');
            
            $role = Mage::getModel('api2/acl_global_role')
                ->getCollection()
                ->addFieldToFilter('role_name',array('eq',$name))
                ->getFirstItem();
             
             if (!$role->getId()) {
                 $role = Mage::getModel('api2/acl_global_role');
                 $role->setRoleName($name);
                 $role->save();
             }
             
             $rule = Mage::getModel('api2/acl_global_rule');
             
             if ($role->getId()) {
                 $collection = $rule->getCollection();
                 $collection->addFilterByRoleId($role->getId());
                 foreach ($collection as $model) {
                     $model->delete();
                 }
             }
             
             $resources = json_decode('{"map":{"retrieve":1,"update":1},"field":{"retrieve":1},"history":{"update":1},"batch":{"retrieve":1},"batch_attempt":{"retrieve":1},"batch_error":{"retrieve":1},"data":{"retrieve":1},"data_type":{"retrieve":1},"log":{"retrieve":1}}');
             
             foreach ($resources as $resourceId => $privileges) {
                 foreach ($privileges as $privilege => $allow) {
                     if (!$allow) {
                         continue;
                     }
                     
                     $rule->setId(null)
                         ->isObjectNew(true);
                     
                     $rule->setRoleId($role->getId())
                         ->setResourceId($resourceId)
                         ->setPrivilege($privilege)
                         ->save();
                 }
             }
             
             return $role->getId();
         } catch (Exception $e) {
             Mage::throwException('ADMIN ROLE ERROR: Jirafe_Analytics_Model_Install_Admin_Role::getId(): ' . $e->getMessage());
         }
                 
    }
}