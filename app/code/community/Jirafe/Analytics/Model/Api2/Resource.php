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
    /**
     *  Default error messages
     */
    const FIELD_MAPPING_ERROR_INVALID_ELEMENT = 'Field mapping error: invalid element.';
    const FIELD_MAPPING_ERROR_INVALID_KEY = 'Field mapping error: invalid key.';
    const FIELD_MAPPING_ERROR_INVALID_FIELD = 'Field mapping error: invalid element.';
    const FIELD_MAPPING_REQUEST_DATA_INVALID = 'The field mapping request data is invalid.';

    
    /**
     *  Default success messages
     */
    const FIELD_MAPPING_UPDATE_SUCCESSFUL = 'Field mapping update successful.';
    
    /**
     * Dispatch
     * 
     * Adding rending of success status to default API2 dispatch function
     */
    public function dispatch()
    {
        switch ($this->getActionType() . $this->getOperation()) {
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
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_UPDATE:
                $this->_errorIfMethodNotExist('_multiUpdate');
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::FIELD_MAPPING_REQUEST_DATA_INVALID);
                }
                $filteredData = $this->getFilter()->collectionIn($requestData);
                $this->_multiUpdate($filteredData);
                $this->_render($this->getResponse()->getMessages());
                $this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_MULTI_STATUS);
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
                self::FIELD_MAPPING_REQUEST_DATA_INVALID => Mage_Api2_Model_Server::HTTP_BAD_REQUEST,
            ));
    }
}
