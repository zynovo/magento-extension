<?php

/**
 * Api2 Map Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Api2_Map extends Mage_Api2_Model_Resource
{
    
   /**
     * Get current map
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $collection = Mage::getModel('jirafe_analytics/map')
            ->getCollection();
        $mapData = array();
        
         foreach ($collection->getItems() as $field) {
            $mapData[] = $field->toArray();
        }
        return $mapData;
    }
    
    /**
     * Get Magento field options
     *
     * @return array
     */
    protected function _retrieveOptions()
    {
        $collection = Mage::getModel('jirafe_analytics/map')->getCollection();
        $mapData = array();
    
        foreach ($collection->getItems() as $field) {
            $mapData[] = $field->toArray();
        }
        return $mapData;
    }
    
}
