<?php

/**
 * History Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 * 
 */

class Jirafe_Analytics_Model_History extends Jirafe_Analytics_Model_Abstract
{
    /**
     * Get all historical data and insert into data table
     *
     * @return array
     */
    
    public function import()
    {
        try {
            
            $history = array();
            $history = array_merge($history, Mage::getModel('jirafe_analytics/cart')->getHistoricalData() );
            $history = array_merge($history, Mage::getModel('jirafe_analytics/category')->getHistoricalData() );
            $history = array_merge($history, Mage::getModel('jirafe_analytics/customer')->getHistoricalData() );
            $history = array_merge($history, Mage::getModel('jirafe_analytics/employee')->getHistoricalData() );
            $history = array_merge($history, Mage::getModel('jirafe_analytics/order')->getHistoricalData() );
            $history = array_merge($history, Mage::getModel('jirafe_analytics/product')->getHistoricalData() );
            
            foreach ($history as $item) {
                $data = Mage::getModel('jirafe_analytics/data');
                $data->setStoreId( $item['store_id'] );
                $data->setTypeId( $item['type_id'] );
                $data->setHistorical( 1 );
                $data->setJson( $item['json'] );
                $data->setCapturedDt( $this->_getCurrentDt() );
                $data->save();
                $data = null;
            }
            return $history;
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_History::import()', $e->getMessage());
            return false;
        }
    }
    
    
}

