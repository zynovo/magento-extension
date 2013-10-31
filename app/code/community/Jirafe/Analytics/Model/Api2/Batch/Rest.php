<?php

/**
 * Api2 Batch Rest Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Api2_Batch_Rest extends Jirafe_Analytics_Model_Api2_Batch
{
    
    /**
     * Current loaded map
     *
     * @var Jirafe_Analytics_Model_Map
     */
    protected $_map;
    
    /**
     * Retrieve information about specified order item
     *
     * @throws Mage_Api2_Exception
     * @return array
     */
    protected function _retrieve()
    {
        return $this->_getMap()->getData();
    }
    
    /**
     * Get all field maps
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        return $this->_getMaps();
    }
    
    /**
     * Map create not available
     *
     * @param array $data
     */
    protected function _create(array $data)
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
    
    /**
     * Map delete not available
     */
    protected function _delete()
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
    
    /**
     * Load map by its element and key in request
     *
     * @return Jirafe_Analytics_Model_Map
     */
    
    protected function _getMap()
    {
        if (is_null($this->_map)) {
            $element = $this->getRequest()->getParam('element');
            $key = $this->getRequest()->getParam('key');
            
            if ( empty($element) || empty($key) ) {
                $this->_critical( self::FIELD_MAPPING_REQUEST_DATA_INVALID );
            } else {
                
                $obj = Mage::getModel('jirafe_analytics/map');
                
                if ( !$obj->validateElement($element) ) {
                    $this->_critical( self::FIELD_MAPPING_ERROR_INVALID_ELEMENT );
                } else if ( !$obj->validateKey( $element, $key ) ) {
                    $this->_critical( self::FIELD_MAPPING_ERROR_INVALID_KEY );
                } else {
                    $map = $obj->getMapByElementKey( $element, $key );
                    if (!($map->getId())) {
                        $this->_critical(self::RESOURCE_NOT_FOUND);
                    } else {
                        $this->_map = $map;
                    }
                }
            }
        }
        return $this->_map;
    }
    
    /**
     * Load all field maps
     *
     * @return Jirafe_Analytics_Model_Map
     */
    
    protected function _getMaps()
    {
        $mapData = array();
        if ( $element = $this->getRequest()->getParam('element') ) {
            if ( !Mage::getModel('jirafe_analytics/map')->validateElement($element) ) {
                $this->_critical( self::FIELD_MAPPING_ERROR_INVALID_ELEMENT );
            } else {
                if ( $maps = Mage::getModel('jirafe_analytics/map')->getCollection()->addFieldToFilter('element',$element) ) {
                    $mapData = $maps->getData();
                } else {
                    $this->_critical(self::RESOURCE_NOT_FOUND);
                }
            }
        } else {
            if ( $maps = Mage::getModel('jirafe_analytics/map')->getCollection() ) {
                foreach ($maps->getData() as $field) {
                    $mapData[] = $field;
                }
            } else {
                $this->_critical(self::RESOURCE_NOT_FOUND);
            }
        }
        
        return $mapData;
    }
}
