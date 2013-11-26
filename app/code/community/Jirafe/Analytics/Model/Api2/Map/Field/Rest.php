<?php

/**
 * Api2 Map Field Rest Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Api2_Map_Field_Rest extends Jirafe_Analytics_Model_Api2_Map_Field
{
    
    
    /**
     * Get array of optional Magneto fields
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $fieldData = Mage::getModel('jirafe_analytics/map_field')->getArray();
        
        if ( $fieldData ) {
            return $fieldData;
        } else {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }
    
    /**
     * Map field update not allowed
     *
     * @param array $data
     */
    protected function _update(array $data)
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
    
    /**
     * Map field create not allowed
     *
     * @param array $data
     */
    protected function _create(array $data)
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
    
    /**
     * Map delete field not allowed
     */
    protected function _delete()
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
}
