<?php

/**
 * Api2 Data Type Rest Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Api2_Data_Type_Rest extends Jirafe_Analytics_Model_Api2_Data_Type
{
    
    /**
     * Get all data
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $data = array();
        
        $collection = Mage::getModel('jirafe_analytics/data_type')->getCollection();
        
        foreach ($collection->getData() as $item) {
            $data[] = $item;
        }
        return $data;
    }
    
    /**
     * Retrieve entity data not available
     *
     * @throws Mage_Api2_Exception
     * @return array
     */
    protected function _retrieve()
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
    
    /**
     * Data create not available
     *
     * @param array $data
     */
    protected function _create(array $data)
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
    
    /**
     * Data update not available
     *
     * @param array $data
     */
    protected function _update(array $data)
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
    
    /**
     * Data delete not available
     */
    protected function _delete()
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
    
    
    
}
