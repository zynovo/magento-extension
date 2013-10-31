<?php

/**
 * Api2 Data Rest Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Api2_Data_Rest extends Jirafe_Analytics_Model_Api2_Data
{
    
    /**
     * Retrieve information about specified data item
     *
     * @throws Mage_Api2_Exception
     * @return array
     */
    protected function _retrieve()
    {
        return $this->_getData();
    }
    
    /**
     * Get all data
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $data = array();
        
        $collection = Mage::getModel('jirafe_analytics/data')
            ->getCollection();
        /*
            ->join( array('t'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/data_type')), 'main_table.type_id = t.id', array('t.type'), array())
            ->joinLeft( array('bd'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/batch_data')), 'main_table.id = bd.data_id', array('bd.batch_id'), array())
            ->joinLeft( array('b'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/batch')), 'bd.batch_id = b.id', array('b.attempt_count', 'b.success', 'b.completed_dt'), array());
        */
        foreach ($collection->getData() as $item) {
            $data[] = $item;
        }
        return $data;
        /*
            
            ->query();*/
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
     * Data delete not available
     */
    protected function _delete()
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }
    
    
    
}
