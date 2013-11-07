<?php

/**
 * Install Api2 Attribute Model
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

require_once 'Mage/Api2/controllers/Adminhtml/Api2/AttributeController.php';

class Jirafe_Analytics_Model_Install_Admin_Attribute extends Mage_Api2_Adminhtml_Api2_AttributeController
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

              $request = $this->getRequest();

        $type = $request->getParam('type');

       
        }

        
        $session = $this->_getSession();

        try {
           
            $ruleTree = Mage::getSingleton(
                'api2/acl_global_rule_tree',
                array('type' => Mage_Api2_Model_Acl_Global_Rule_Tree::TYPE_ATTRIBUTE)
            );

          
            $attribute = Mage::getModel('api2/acl_filter_attribute');

            
            $collection = $attribute->getCollection();
            $collection->addFilterByUserType($type);

           
            foreach ($collection as $model) {
                $model->delete();
            }

            foreach ($ruleTree->getPostResources() as $resourceId => $operations) {
                if (Mage_Api2_Model_Acl_Global_Rule::RESOURCE_ALL === $resourceId) {
                    $attribute->setUserType($type)
                        ->setResourceId($resourceId)
                        ->save();
                } else {
                    foreach ($operations as $operation => $attributes) {
                        $attribute->setId(null)
                            ->isObjectNew(true);

                        $attribute->setUserType($type)
                            ->setResourceId($resourceId)
                            ->setOperation($operation)
                            ->setAllowedAttributes(implode(',', array_keys($attributes)))
                            ->save();
                    }
                }
            }

            $session->addSuccess($this->__('The attribute rules were saved.'));
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addException($e, $this->__('An error occurred while saving attribute rules.'));
        }

        */
            return $role->getId();
        } catch (Exception $e) {
            Mage::throwException('ADMIN ROLE ERROR: Jirafe_Analytics_Model_Install_Admin_Role::create(): ' . $e->getMessage());
        }

    }
    
    
}
