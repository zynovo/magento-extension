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
                ->where('`d`.`completed_dt` is NULL')
                ->distinct(true)
                ->query();
            
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Data::_getStores()', $e->getMessage(), $e);
            return false;
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
            if ( is_numeric($storeId) ) {
             
               return Mage::getModel('jirafe_analytics/data_type')
                    ->getCollection()
                    ->addFieldToSelect(array('type'))
                    ->getSelect()
                    ->join( array('d'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/data')), "`main_table`.`id` = `d`.`type_id` AND `d`.`json` is not null AND `d`.`store_id` = $storeId",array())
                    ->where('d.completed_dt is NULL')
                    ->distinct(true)
                    ->query();
               
            } else {
                return array();
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Data::_getStoreTypes()', $e->getMessage(), $e);
            return false;
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
            if ( is_numeric($storeId) && is_numeric($typeId) ) {
             
                return Mage::getModel('jirafe_analytics/data')
                    ->getCollection()
                    ->addFieldToSelect(array('json','store_id'))
                    ->addFieldToFilter('`main_table`.`json`', array('neq' => ''))
                    ->addFieldToFilter('`main_table`.`store_id`', array('eq' => $storeId))
                    ->getSelect()
                    ->join( array('dt'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/data_type')), "`main_table`.`type_id` = `dt`.`id` AND `dt`.`id` = $typeId",array('dt.type'))
                    ->where('`main_table`.`completed_dt` is NULL')
                    ->query();
                
            } else {
                return array();
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Data::_getItems()', $e->getMessage(), $e);
            return false;
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
            if ( isset( $params['json_max_size'] ) ) {
                $maxSize = intval( $params['json_max_size'] );
            } else {
                $maxSize = Mage::getStoreConfig('jirafe_analytics/curl/max_size');
            }
            
            $batchIndex = 0;
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
                $batch = null;
               
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
                        
                        if (!$batch) {
                            $batch = Mage::getModel('jirafe_analytics/batch');
                            $batch->save();
                        }
                        
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
                            $batch = null;
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
                        $batchData->setBatchOrder( $batchIndex );
                        $batchData->save();
                        $batchIndex = $batchIndex + 1;
                    }
                    
                    $batchContainer[ $type['type'] ] = $typeContainer;
                }
                
                /**
                 * Save and close current batch
                 */
                if ( $batchContainer ) {
                    $this->_saveBatch( $batch, $store['store_id'], json_encode($batchContainer), $historical );
                    $batchIndex = 0;
                }
                
            }
            
            return true;
            
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Data::convertEventDataToBatchData()', $e->getMessage(), $e);
            return false;
            
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
            if ( $batch && is_numeric($storeId) && $json) {
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
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Data::_saveBatch()', $e->getMessage(), $e);
            return false;
        }
    }
    
    /**
     * Purge data
     *
     * @return boolean
     * @throws Exception if failure to purge data
     */
    public function purgeData()
    {
        try {
            $minutes = Mage::getStoreConfig('jirafe_analytics/general/purge_time');
            if ( intval($minutes) > 15 ) {
                $resource = Mage::getSingleton('core/resource');
                
                $result = $resource->getConnection('core_write')->query( sprintf("DELETE FROM %s WHERE `id` IN (SELECT `data_id` FROM %s) AND TIMESTAMPDIFF(MINUTE,`captured_dt`,'%s') > %d",
                              $resource->getTableName('jirafe_analytics/data'),
                              $resource->getTableName('jirafe_analytics/batch_data'),
                              Mage::helper('jirafe_analytics')->getCurrentDt(),
                              $minutes) 
                          );
                
                $result = $resource->getConnection('core_write')->query( sprintf("DELETE FROM %s WHERE TIMESTAMPDIFF(MINUTE,`completed_dt`,'%s') > %d",
                              $resource->getTableName('jirafe_analytics/batch'),
                              Mage::helper('jirafe_analytics')->getCurrentDt(),
                              $minutes) 
                          );
                
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Data::purgeData()', $e->getMessage(), $e);
            return false;
        }
    }
    
    /**
     * Convert historical data into JSON
     *
     * @return array
     */
    public function convertHistoricalData( $params = null )
    {
        try {
            
            Mage::helper('jirafe_analytics')->overridePhpSettings( $params );
            
            $element = isset($params['element'] ) ? trim( $params['element'] ) : null;
            $startDate = isset($params['start_date'] ) ? trim( $params['start_date'] ) : null;
            $endDate = isset($params['end_date'] ) ? trim( $params['end_date'] ) : null;
            $useLastIds = isset($params['use_last_ids']) ? (boolean) $params['use_last_ids'] : false;
            
            if ( $useLastIds ) { 
                if ( $success = Mage::getModel('jirafe_analytics/data_type')->captureLastIds() ) {
                    Mage::helper('jirafe_analytics')->log( 'INSTALLER', 'Jirafe_Analytics_Model_Data::convertHistoricalData()', 'Captured last ids', null );
                }
            }
            
            /**
             * If element name not passed through parameters, convert all element types
             */
            
            $all = $element ? false : true;
            
            $history = array();
            
            $filters = array('start_date'=> $startDate, 
                             'end_date' => $endDate, 
                             'last_id' => null);
            
            if ( $element === 'cart' || $all ) {
                if ( $useLastIds ) { 
                    $filters['last_id'] = $this->_getLastId( 'cart'); 
                }
                
                if ( $carts =  Mage::getModel('jirafe_analytics/cart')->getHistoricalData( $filters ) ) {
                    $history = array_merge( $history, $carts );
                }
            }
            
            if ( $element === 'category' || $all ) {
                if ( $useLastIds ) {
                    $filters['last_id'] = $this->_getLastId( 'category');
                }
                
                if ( $categories = Mage::getModel('jirafe_analytics/category')->getHistoricalData( $filters ) ) {
                    $history = array_merge( $history, $categories );
                }
            }
            
            if ( $element === 'customer' || $all ) {
                if ( $useLastIds ) {
                    $filters['last_id'] = $this->_getLastId( 'customer');
                }
                
                if ( $customers = Mage::getModel('jirafe_analytics/customer')->getHistoricalData( $filters ) ) {
                    $history = array_merge( $history, $customers );
                }
               
            }
            
            if ( $element === 'employee' || $all ) {
                
                if ( $useLastIds ) {
                    $filters['last_id'] = $this->_getLastId( 'employee');
                }
                
                if ( $employees = Mage::getModel('jirafe_analytics/employee')->getHistoricalData( $filters ) ) {
                    $history = array_merge( $history, $employees );
                }
            }
            
            if ( $element === 'order' || $all ) {
                if ( $useLastIds ) {
                    $filters['last_id'] = $this->_getLastId( 'order');
                }
                
                if ( $orders = Mage::getModel('jirafe_analytics/order')->getHistoricalData( $filters ) ) {
                    $history = array_merge( $history, $orders );
                }
            }
            
            if ( $element === 'product' || $all ) {
                if ( $useLastIds ) {
                    $filters['last_id'] = $this->_getLastId( 'product');
                }
                
                if ( $products = Mage::getModel('jirafe_analytics/product')->getHistoricalData( $filters ) ) {
                    $history = array_merge( $history,$products  );
                }
            }
            
            if ( $history ) {
                foreach ($history as $item) {
                    $data = Mage::getModel('jirafe_analytics/data');
                    $data->setStoreId( $item['store_id'] );
                    $data->setTypeId( $item['type_id'] );
                    $data->setHistorical( 1 );
                    $data->setJson( $item['json'] );
                    $data->setCapturedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                    $data->save();
                    $data = null;
                }
                Mage::helper('jirafe_analytics')->log( 'INSTALLER', 'Jirafe_Analytics_Model_Data::convertHistoricalData()', count($history) . ' historical records converted.', null );
                return true;
            } else {
                Mage::helper('jirafe_analytics')->log( 'ERROR', 'Jirafe_Analytics_Model_Data::convertHistoricalData()', 'No Historical Data', null );
                return false;
            }
            
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log( 'ERROR', 'Jirafe_Analytics_Model_Data::convertHistoricalData()', $e->getMessage(), $e );
            return false;
        }
    }
    
    /**
     * Get last id of a entity type
     */
    private function _getLastId( $type ) 
    {
     try {
         $type = Mage::getModel('jirafe_analytics/data_type')
                     ->getCollection()
                     ->addFieldToFilter( 'type',array( 'eq',$type ) )
                     ->getFirstItem();
         
         if ( is_numeric($type->getLastId() )) {
             return $type->getLastId();
         } else {
             return null;
         }
     } catch (Exception $e) {
           Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Data::_getLastId()', $e->getMessage(), $e);
           return false;
       }
    }

}