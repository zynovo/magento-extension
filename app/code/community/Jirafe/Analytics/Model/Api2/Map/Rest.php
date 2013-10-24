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
        $mapId    = $this->getRequest()->getParam('id');
        $collection = $this->_getCollectionForSingleRetrieve($mapId);

        $map = $collection->getItemById($mapId);

        if (!$map) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $mapData = $map->getData();
      
        return $mapData;
    }
}
