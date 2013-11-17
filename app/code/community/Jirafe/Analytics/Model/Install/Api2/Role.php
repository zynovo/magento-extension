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
             
             Mage::app()->getRequest()->setParam('id',$role->getId());
             Mage::app()->getRequest()->setParam('role_name',$role->getRoleName());
             Mage::app()->getRequest()->setParam('resource','__root__,group-jirafe_analytics,resource-map,privilege-map-retrieve,privilege-map-update,resource-field,privilege-field-retrieve,resource-history,privilege-history-update,resource-batch,privilege-batch-retrieve,resource-data_attempt,privilege-data_attempt-retrieve,resource-data_error,privilege-data_error-retrieve,resource-data,privilege-data-retrieve,resource-data_type,privilege-data_type-retrieve,resource-log,privilege-log-retrieve');
             Mage::app()->getRequest()->setParam('filter_in_role_users',0);
             
             $ruleTree = Mage::getModel(
               'api2/acl_global_rule_tree',
               array('type' => Mage_Api2_Model_Acl_Global_Rule_Tree::TYPE_PRIVILEGE)
             );
             
             foreach ($ruleTree->getPostResources() as $resourceId => $privileges) {
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