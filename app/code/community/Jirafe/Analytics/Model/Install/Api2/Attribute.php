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

require_once 'Mage/Api2/controllers/Adminhtml/Api2/AttributeController.php';

class Jirafe_Analytics_Model_Install_Api2_Attribute extends Mage_Api2_Adminhtml_Api2_AttributeController
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
    public function setAll()
    {
        try {
            $type = 'admin';
            
            Mage::app()->getRequest()->setParam('resource','__root__,group-jirafe_analytics,resource-map,operation-map-read,attribute-map-read-api,attribute-map-read-created_ts,attribute-map-read-default,attribute-map-read-element,attribute-map-read-key,attribute-map-read-magento,attribute-map-read-type,attribute-map-read-updated_dt,operation-map-write,attribute-map-write-api,attribute-map-write-created_ts,attribute-map-write-default,attribute-map-write-element,attribute-map-write-key,attribute-map-write-magento,attribute-map-write-type,attribute-map-write-updated_dt,resource-field,operation-field-read,attribute-field-read-cart,attribute-field-read-cart_item,attribute-field-read-catalog,attribute-field-read-customer,attribute-field-read-employee,attribute-field-read-order,attribute-field-read-order_item,resource-history,operation-history-write,attribute-history-write-element,attribute-history-write-end_date,attribute-history-write-function,attribute-history-write-json_max_size,attribute-history-write-max_execution_time,attribute-history-write-memory_limit,attribute-history-write-proc_nice,attribute-history-write-start_date,resource-batch,operation-batch-read,attribute-batch-read-completed_dt,attribute-batch-read-created_dt,attribute-batch-read-historical,attribute-batch-read-http_code,attribute-batch-read-id,attribute-batch-read-json,attribute-batch-read-store_id,attribute-batch-read-total_time,resource-data_attempt,operation-data_attempt-read,attribute-data_attempt-read-created_dt,attribute-data_attempt-read-data_id,attribute-data_attempt-read-id,resource-data_error,operation-data_error-read,attribute-data_error-read-created_dt,attribute-data_error-read-data_attempt_id,attribute-data_error-read-data_id,attribute-data_error-read-error_type,attribute-data_error-read-errors,attribute-data_error-read-id,resource-data,operation-data-read,attribute-data-read-attempt_count,attribute-data-read-captured_dt,attribute-data-read-completed_dt,attribute-data-read-historical,attribute-data-read-id,attribute-data-read-json,attribute-data-read-store_id,attribute-data-read-success,attribute-data-read-type_id,resource-data_type,operation-data_type-read,attribute-data_type-read-id,attribute-data_type-read-type,resource-log,operation-log-read,attribute-log-read-created_dt,attribute-log-read-id,attribute-log-read-location,attribute-log-read-message,attribute-log-read-type');
            Mage::app()->getRequest()->setParam('type',$type);
            Mage::app()->getRequest()->setParam('all',0);
            
            $ruleTree = Mage::getModel(
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
            return true;
         } catch (Exception $e) {
             Mage::throwException('ADMIN ROLE ERROR: Jirafe_Analytics_Model_Install_Api2_Attribute::set(): ' . $e->getMessage());
         }
    }
}