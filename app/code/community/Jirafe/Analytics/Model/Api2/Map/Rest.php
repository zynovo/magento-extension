<?php

/**
 * Api2 Map Rest Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Api2_Map_Rest extends Jirafe_Analytics_Model_Api2_Map
{
    /**
     * Retrieve information about specified order item
     *
     * @throws Mage_Api2_Exception
     * @return array
     */
    protected function _retrieve()
    {
        
        $mapData = array();
        $mapId = $this->getRequest()->getParam('id');
        $mapData = Mage::getModel('jirafe_analytics/map')->load($mapId)->getData();
        
        if (!$mapData) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        

      
        return $mapData;
    }
}
