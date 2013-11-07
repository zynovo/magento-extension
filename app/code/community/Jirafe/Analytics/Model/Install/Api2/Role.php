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

class Request extends Zend_Controller_Request_Abstract {}

class Response extends Zend_Controller_Response_Abstract {}

require_once 'Mage/Api2/controllers/Adminhtml/Api2/RoleController.php';

class Jirafe_Analytics_Model_Install_Admin_Role extends Mage_Api2_Adminhtml_Api2_RoleController
{
    /**
     * Override parent constructor to mimic controller behavior
     */
    public function __construct()
    {
        $request = new Request;
        $response = new Response;
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
            /*            
            if (!$role->getId()) {

                $role = Mage::getModel('api2/acl_global_role');

        $roleUsers  = $request->getParam('in_role_users', null);
        parse_str($roleUsers, $roleUsers);
        $roleUsers = array_keys($roleUsers);

        $oldRoleUsers = $this->getRequest()->getParam('in_role_users_old');
        parse_str($oldRoleUsers, $oldRoleUsers);
        $oldRoleUsers = array_keys($oldRoleUsers);

        $session = $this->_getSession();

        try {
            $role->setRoleName($this->getRequest()->getParam('role_name', false))
                    ->save();

            foreach($oldRoleUsers as $oUid) {
                $this->_deleteUserFromRole($oUid, $role->getId());
            }

            foreach ($roleUsers as $nRuid) {
                $this->_addUserToRole($nRuid, $role->getId());
            }

            $rule = Mage::getModel('api2/acl_global_rule');
            if ($id) {
                $collection = $rule->getCollection();
                $collection->addFilterByRoleId($role->getId());

                foreach ($collection as $model) {
                    $model->delete();
                }
            }

            $ruleTree = Mage::getSingleton(
                'api2/acl_global_rule_tree',
                array('type' => Mage_Api2_Model_Acl_Global_Rule_Tree::TYPE_PRIVILEGE)
            );
            $resources = $ruleTree->getPostResources();
            $id = $role->getId();
            foreach ($resources as $resourceId => $privileges) {
                foreach ($privileges as $privilege => $allow) {
                    if (!$allow) {
                        continue;
                    }

                    $rule->setId(null)
                            ->isObjectNew(true);

                    $rule->setRoleId($id)
                            ->setResourceId($resourceId)
                            ->setPrivilege($privilege)
                            ->save();
                }
            }
            
        */
            return $role->getId();
        } catch (Exception $e) {
            Mage::throwException('ADMIN ROLE ERROR: Jirafe_Analytics_Model_Install_Admin_Role::create(): ' . $e->getMessage());
        }

    }
    
    
}
