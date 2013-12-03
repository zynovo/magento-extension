<?php

/**
 * Api2 Resource Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

abstract class Jirafe_Analytics_Model_Api2_Resource extends Mage_Api2_Model_Resource
{
    /**#@+
     *  Action types
     */
    const ACTION_TYPE_FUNCTION  = 'function';
    
    /**
     *  Default error messages
     */
    const FIELD_MAPPING_ERROR_INVALID_ELEMENT = 'Invalid element.';
    const FIELD_MAPPING_ERROR_INVALID_KEY = 'Invalid key.';
    const FIELD_MAPPING_ERROR_INVALID_TYPE = 'Invalid type. Must be either string, int, float or boolean.';
    const FIELD_MAPPING_ERROR_INVALID_FIELD = 'Invalid element.';
    const FIELD_MAPPING_REQUEST_DATA_INVALID = 'Request data is invalid.';
    const REQUEST_FUNCTION_INVALID = 'Request function invalid.';
    const REQUEST_FUNCTION_NO_DATA = 'Request data available.';
    const REQUEST_FUNCTION_ERROR = 'Request function error.';
    
    /**
     *  Default success messages
     */
    const FIELD_MAPPING_UPDATE_SUCCESSFUL = 'Field mapping update successful.';
    const HISTORY_EXPORT_FUNCTION_SUCCESSFUL = 'Historical data successfully exported.';
    const HISTORY_CONVERT_FUNCTION_SUCCESSFUL = 'Historical data successfully converted to JSON.';
    const HISTORY_BATCH_FUNCTION_SUCCESSFUL = 'Historical JSON objects successfully batched for export.';
    const RESET_DATA_FUNCTION_SUCCESSFUL  = 'Reseting of JSON data successful.';
    const RESET_INSTALLER_STATUS_FUNCTION_SUCCESSFUL  = 'Reseting of installer data successful.';
    const LOG_PURGE_SUCCESSFUL = 'Log purge successful.';
    
    /**
     * Dispatch
     * 
     * Adding rending of success status to default API2 dispatch function
     */
    public function dispatch()
    {
        switch ($this->getActionType() . $this->getOperation()) {
            /* Function */
            case self::ACTION_TYPE_FUNCTION . self::OPERATION_UPDATE:
                $this->_errorIfMethodNotExist('_function');
                $requestData = $this->getRequest()->getBodyParams(); 
                if (empty($requestData)) {
                    $this->_critical(self::REQUEST_FUNCTION_INVALID);
                }
                $filteredData = $this->getFilter()->in($requestData);
                if (empty($filteredData)) {
                    $this->_critical(self::REQUEST_FUNCTION_INVALID);
                }
                $this->_function($filteredData);
                $this->_render($this->getResponse()->getMessages());
                break;
            /* Retrieve */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_RETRIEVE:
                $this->_errorIfMethodNotExist('_retrieve');
                $retrievedData = $this->_retrieve();
                $filteredData  = $this->getFilter()->out($retrievedData);
                $this->_render($filteredData);
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_RETRIEVE:
                $this->_errorIfMethodNotExist('_retrieveCollection');
                $retrievedData = $this->_retrieveCollection();
                $filteredData  = $this->getFilter()->collectionOut($retrievedData);
                $this->_render($filteredData);
                break;
            /* Update */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_UPDATE:
                $this->_errorIfMethodNotExist('_update');
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::FIELD_MAPPING_REQUEST_DATA_INVALID);
                }
                $filteredData = $this->getFilter()->in($requestData);
                if (empty($filteredData)) {
                    $this->_critical(self::FIELD_MAPPING_REQUEST_DATA_INVALID);
                }
                $this->_update($filteredData);
                $this->_render($this->getResponse()->getMessages());
                break;
             default:
                $this->_critical(self::REQUEST_METHOD_NOT_IMPLEMENTED);
                break;
        }
    }
    /**
     * Retrieve array with critical errors mapped to HTTP codes
     *
     * @return array
     */
    protected function _getCriticalErrors()
    {
        return array_merge(
            parent::_getCriticalErrors(),
            array(
                self::FIELD_MAPPING_ERROR_INVALID_ELEMENT => Mage_Api2_Model_Server::HTTP_BAD_REQUEST,
                self::FIELD_MAPPING_ERROR_INVALID_KEY => Mage_Api2_Model_Server::HTTP_BAD_REQUEST,
                self::FIELD_MAPPING_ERROR_INVALID_FIELD => Mage_Api2_Model_Server::HTTP_BAD_REQUEST,
                self::FIELD_MAPPING_ERROR_INVALID_TYPE => Mage_Api2_Model_Server::HTTP_BAD_REQUEST,
                self::FIELD_MAPPING_REQUEST_DATA_INVALID => Mage_Api2_Model_Server::HTTP_BAD_REQUEST,
                self::REQUEST_FUNCTION_INVALID => Mage_Api2_Model_Server::HTTP_BAD_REQUEST,
                self::REQUEST_FUNCTION_NO_DATA => Mage_Api2_Model_Server::HTTP_BAD_REQUEST
            ));
    }
}
