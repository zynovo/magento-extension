<?php

/**
 * Data Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 * 
 */

class Jirafe_Analytics_Model_Data extends Jirafe_Analytics_Model_Abstract
{
    
    /**
     * Class construction & resource initialization
     */
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/data');
    }
    
    
    /**
     * Return all store ids from unbatched data
     *
     * @return array
     * @throws Exception if unable to return store ids
     */
    
    protected function _getStores() 
    {
        try {
            return Mage::getModel('core/store')->getCollection()
                ->addFieldToSelect(array('store_id'))
                ->getSelect()
                ->join( array('d'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/data')), "`main_table`.`store_id` = `d`.`store_id`", array())
                ->joinLeft( array('bd'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/batch_data')), "`d`.`id` = `bd`.`data_id`", array())
                ->where('`bd`.`batch_id` is NULL')
                ->distinct(true)
                ->query();
        } catch (Exception $e) {
            Mage::throwException('BATCH DATA ERROR: Jirafe_Analytics_Model_Data::_getStores(): ' . $e->getMessage());
        }
        
    }
    
    /**
     * Return all types from stores with unbatched data
     * 
     * @param string $storeId
     * @return array
     * @throws Exception if unable to return data types
     */
    
    protected function _getStoreTypes( $storeId = null ) 
    {
        try {
            if ( $storeId ) {
               return Mage::getModel('jirafe_analytics/data_type')
                    ->getCollection()
                    ->addFieldToSelect(array('type'))
                    ->getSelect()
                    ->join( array('d'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/data')), "`main_table`.`id` = `d`.`type_id` AND `d`.`json` is not null AND `d`.`store_id` = $storeId",array())
                    ->joinLeft( array('bd'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/batch_data')), "`d`.`id` = `bd`.`data_id`",array())
                    ->where('`bd`.`batch_id` is NULL')
                    ->distinct(true)
                    ->query();
            } else {
                return array();
            }
        } catch (Exception $e) {
            Mage::throwException('BATCH DATA ERROR: Jirafe_Analytics_Model_Data::_getStoreTypes(): ' . $e->getMessage());
        }
    }
    
    /**
     * Return data ready to be batched
     *
     * @param string $storeId
     * @param string $typeId
     * @return array
     * @throws Exception if unable to return data types
     */
    
    protected function _getItems( $storeId = null, $typeId = null )
    {
        try {
            if ( $storeId && $typeId ) {
                return Mage::getModel('jirafe_analytics/data')
                ->getCollection()
                ->addFieldToSelect(array('json','store_id'))
                ->addFieldToFilter('`main_table`.`json`', array('neq' => ''))
                ->addFieldToFilter('`main_table`.`store_id`', array('eq' => $storeId))
                ->getSelect()
                ->join( array('dt'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/data_type')), "`main_table`.`type_id` = `dt`.`id` AND `dt`.`id` = $typeId",array('dt.type'))
                ->joinLeft( array('bd'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/batch_data')), "`main_table`.`id` = `bd`.`data_id`",array())
                ->where('`bd`.`batch_id` is NULL')
                ->query();
            } else {
                return array();
            }
        } catch (Exception $e) {
            Mage::throwException('BATCH DATA ERROR: Jirafe_Analytics_Model_Data::_getItems(): ' . $e->getMessage());
        }
    }
    
    /**
     * Convert event data to batch
     *
     * @return boolean
     * @throws Exception if create batch array fails
     */
    public function convertEventDataToBatchData( $params = null,  $historical = false )
    {
        try {
            
            /**
             * Performance tuning options: override server php settings
             */
            
            Mage::helper('jirafe_analytics')->overridePhpSettings( $params );
            
            /**
             * Get user configurable maximum size of json object in bytes
             */
            $maxSize = Mage::getStoreConfig('jirafe_analytics/curl/max_size');
            
            /**
             * Separate data by store id since each store has a separate site_id and oauth token
             * 
             * Get all stores with data ready to be batched
             */
            foreach( $this->_getStores() as $store ) {
                
                /**
                 * Initialize batch database object and array container
                 */
                $batchContainer = array();
                $batch = Mage::getModel('jirafe_analytics/batch');
                $batch->save();
                
                /**
                 * Get all available object types for each store
                 */
                foreach( $this->_getStoreTypes( $store['store_id'] ) as $type ) {
                    
                    /**
                     * Initialize array container for items of each type
                     */
                    $typeContainer = array();
                    
                    /**
                     * Initialize test container for evaluating size of JSON object
                     */
                    $testContainer = array();
                    
                    /**
                     * Separate data by object type for batching
                     */
                    
                    foreach( $this->_getItems( $store['store_id'], $type['id'] ) as $item ) {
                        
                        $content = json_decode( $item['json'] );
                        
                        /**
                         * Evaluate size of JSON
                         */
                        $typeTestContainer = $typeContainer;
                        $testContainer[] = $content;
                        
                        if ( $maxSize < strlen( json_encode( array( $type['type'] => $testContainer ) ) ) ) {
                            /**
                             * Save and close current batch
                             */
                            $this->_saveBatch( $batch, $store['store_id'], json_encode( array( $type['type'] => $typeContainer ) ), $historical );
                            
                            /**
                             * Create new batch
                             */
                            $batch = Mage::getModel('jirafe_analytics/batch');
                            $batch->save();
                            
                            /**
                             * Reset containers
                             */
                            $typeContainer = array();
                            $testContainer = array();
                        }
                        
                        $typeContainer[] = $content;
                        
                        /**
                         * Associate data with batch
                         */
                        $batchData = Mage::getModel('jirafe_analytics/batch_data');
                        $batchData->setBatchId( $batch->getId() );
                        $batchData->setDataId( $item['id'] );
                        $batchData->save();
                    }
                    
                    $batchContainer[ $type['type'] ] = $typeContainer;
                }
                
                /**
                 * Save and close current batch
                 */
                $this->_saveBatch( $batch, $store['store_id'], json_encode($batchContainer), $historical );
                
            }
            
            return true;
            
        } catch (Exception $e) {
            Mage::throwException('BATCH DATA ERROR: Jirafe_Analytics_Model_Data::convertEventDataToBatchData(): ' . $e->getMessage());
        }
    }
    
    /**
     * Save data to batch
     *
     * @return boolean
     * @throws Exception if failure to save batch
     */
    protected function _saveBatch( $batch = null, $storeId = null, $json = null, $historical = false )
    {
        try {
            if ( $batch && $storeId && $json) {
                $batch->setStoreId( $storeId );
                $batch->setJson( $json );
                $batch->setCreatedDt( Mage::helper('jirafe_analytics')->getCurrentDt()  );
                
                if ($historical) {
                    $batch->setHistorical(1);
                }
                $batch->save();
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            Mage::throwException('BATCH DATA ERROR: Jirafe_Analytics_Model_Data::_saveBatch(): ' . $e->getMessage());
        }
    }
}

