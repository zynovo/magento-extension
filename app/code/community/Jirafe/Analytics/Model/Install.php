<?php

/**
 * Install Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 * 
 * 
 */

class Jirafe_Analytics_Model_Install extends Jirafe_Analytics_Model_Abstract
{
    protected $_cache = null;
    
    protected $_tasks = array( 'credentials', 'convert', 'batch', 'export' );
    
    protected $_status = array();
    
    /**
     * Class construction & resource initialization
     * Load installer statuses from db to prevent reruns by cron
     */
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/install');
    }
    
    /**
     * Get installer status
     */
    protected function _getStatus()
    {
        try {
            $this->_cache = Mage::app()->getCache();
             
            if ( $cachedLog = $this->_cache->load('jirafe_analytics_installer') ) {
                $this->_status = json_decode($cachedLog, true);
            } else {
                $collection = Mage::getModel('jirafe_analytics/install')->getCollection();
                foreach( $collection as $item ) {
                    $this->_status[ $item->getTask() ] = $item->getCompletedDt();
                }
                
                /**
                 * refresh status values in cache
                 */
                
                $this->_saveStatusToCache();
             }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Installer::_construct()', $e->getMessage(), $e);
        }
     }
     
    /**
      * Run installer
      * 
      * @return string
      * Triggered by jirafe_analytics_installer cron
      */
    public function run( $message = null )
    {
      try {
          
          /**
           * Check for previous run of credentials installer
           */
          $this->_getStatus();
          
           if ( !$this->_status['credentials'] ) {
              
              $response = $this->createCredentials();
              
              if ( $response === 'error' ) {
                  $message .= 'Installer error: unable to install admin user. ';
                  Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Install::run()', $message, null );
              } else {
                  $message .= 'Successfully installed admin credentials. ';
                  Mage::helper('jirafe_analytics')->log( 'INSTALLER', 'Jirafe_Analytics_Model_Install::run()', $message, null );
              }
              
              $this->_status['credentials'] = Mage::helper('jirafe_analytics')->getCurrentDt();
              
          } else {
              $response = 'credentials already installed';
          }
          
          /**
           * Check cache for previous run of historical data functions
           * /
          
          if ( !$this->_status['convert'] || !$this->_status['batch'] || !$this->_status['export']) {
              
              $params = array();
              
              if ( $maxRecords = Mage::getStoreConfig('jirafe_analytics/installer/max_records') ) {
                  $params['max_records'] = $maxRecords;
              }
              
              if ( $maxExecutionTime = Mage::getStoreConfig('jirafe_analytics/installer/max_execution_time') ) {
                  $params['max_execution_time'] = $maxExecutionTime;
              }
              
              if ( $memoryLimit = Mage::getStoreConfig('jirafe_analytics/installer/memory_limit') ) {
                  $params['memory_limit'] = $memoryLimit;
              }
              
              if ( !$this->_status['convert'] ) {
                  
                  / **
                   * Use last ids as endpoint for historical data
                   * /
                  $params['use_last_ids'] = true;
                  
                  / **
                   *  Convert historical data
                   * /
                  if ( $success = Mage::getModel('jirafe_analytics/data')->convertHistoricalData( $params ) ) {
                      $message .= 'Successfully converted historical data. ';
                      Mage::helper('jirafe_analytics')->log( 'INSTALLER', 'Jirafe_Analytics_Model_Install::run()', $message, null );
                      $this->_status['convert'] =  Mage::helper('jirafe_analytics')->getCurrentDt();
                  } else {
                      $message .= "Installer error: unable to convert historical data (max_records: $maxRecords, max_execution_time: $maxExecutionTime, memory_limit: $memoryLimit). ";
                      Mage::helper('jirafe_analytics')->log( 'ERROR', 'Jirafe_Analytics_Model_Install::run()', $message, null );
                  }
                  
              } else if ( !$this->_status['batch'] ) {
                  
                  / **
                   *  Batch historical data
                   * /
                  if ( $success = Mage::getModel('jirafe_analytics/data')->convertEventDataToBatchData( $params, true ) ) {
                      $message .= 'SUCCESS batching historical data. ';
                      Mage::helper('jirafe_analytics')->log( 'INSTALLER', 'Jirafe_Analytics_Model_Install::run()', $message, null );
                      $this->_status['batch'] = Mage::helper('jirafe_analytics')->getCurrentDt();
                  } else {
                      $message .= "Installer error: unable to batch historical data (max_records: $maxRecords, max_execution_time: $maxExecutionTime, memory_limit: $memoryLimit). ";
                      Mage::helper('jirafe_analytics')->log( 'ERROR', 'Jirafe_Analytics_Model_Install::run()', $message, null );
                  }
                  
              } else if ( !$this->_status['export'] ) {
                  
                  / **
                   *  Transfer batches historical data to Jirafe via cURL
                   * /
                  if ( $continueProcessing = Mage::getModel('jirafe_analytics/batch')->process( $params, true ) ) {
                      $message .= "Successfully exported batch of historical data (max_records: $maxRecords, max_execution_time: $maxExecutionTime, memory_limit: $memoryLimit). ";
                      Mage::helper('jirafe_analytics')->log( 'INSTALLER', 'Jirafe_Analytics_Model_Install::run()', $message, null );
                  } else { 
                      $message .= 'Successfully completed the exporting of historical data. ';
                      Mage::helper('jirafe_analytics')->log( 'INSTALLER', 'Jirafe_Analytics_Model_Install::run()', $message, null );
                      $this->_status['export'] = Mage::helper('jirafe_analytics')->getCurrentDt();
                  }
              }
              */
              $this->_saveStatus();
              
              return $message;
              
       } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Installer::_construct()', $e->getMessage(), $e);
            return 'INSTALLER ERROR: Jirafe_Analytics_Model_Install::run(): ' . $e->getMessage();
       }
    }
      
    /**
     * create authentication credentials for remote REST/Oauth access
     *
     * @return string
     */
    public function createCredentials()
    {
       try {
          
          Mage::app()->cleanCache();
          
          if (!$adminRoleID = Mage::getModel('jirafe_analytics/install_admin_role')->getId()) {
              Mage::throwException('Error creating admin user role');
          }
          
          if (!$api2RoleId = Mage::getModel('jirafe_analytics/install_api2_role')->getId()) {
              Mage::throwException('Error creating api2 user role');
          }
          
          if (!$api2Attributes = Mage::getModel('jirafe_analytics/install_api2_attribute')->setAll()) {
              Mage::throwException('Error creating api2 attribures');
          }
          
          if (!$oauthSecret = Mage::getModel('jirafe_analytics/install_oauth_consumer')->getSecret()) {
              Mage::throwException('Error creating oauth consumer.');
          }
          
          if (!$user = Mage::getModel('jirafe_analytics/install_admin_user')->create( $adminRoleID, $api2RoleId, $oauthSecret)) {
              Mage::throwException('Error creating admin user.');
          }
          
          return 'Successfully installed admin credentials: ' . Mage::helper('jirafe_analytics')->getCurrentDt();
          
       } catch (Exception $e) {
           Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Installer::_construct()', $e->getMessage(), $e);
           return 'INSTALLER ERROR: Jirafe_Analytics_Model_Install::run(): ' . $e->getMessage();
       }
    }
    
    /**
     * Save installer statuses to db
     * 
     * @return boolean
     */
    protected function _saveStatus()
    {
     try {
         
         /**
          * update task statuses with completion days
          */
         foreach( $this->_status as $key => $val ) {
             
             if ( in_array( $key, $this->_tasks ) ) {
                 $task = Mage::getModel('jirafe_analytics/install')
                             ->getCollection()
                             ->addFieldToFilter( 'task', $key )
                             ->getFirstItem();
                 
                 $task->setCompletedDt( $val );
                 $task->save();
             }
         }
          
         /**
          * refresh status values in cache
          * 
          */
         $this->_saveStatusToCache();
         
         return true;
         
        } catch (Exception $e) {
             Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Installer::_saveStatus()', $e->getMessage(), $e);
             return false;
        }
    }
     
    /**
     * Save installer status changes to cache
     * 
     * @return boolean
     */
    protected function _saveStatusToCache()
    {
        try {
            $this->_cache->save( json_encode($this->_status), 'jirafe_analytics_installer', array('jirafe_analytics_installer'), null);
            return true;
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Installer::_saveStatusToCache()', $e->getMessage(), $e);
            return false;
        }
    }
    
    /**
     * Reset status by passing array of tasks
     * 
     * @param $tasks array
     * @return boolean
     */
    public function resetStatus( $tasks = null)
    {
        try {
            
            foreach($tasks as $task) {
                if ( in_array( $key, $this->_tasks ) ) {
                    $this->_status[$task]  = null;
                }
            }
            
            $this->_saveStatus();
            return true;
        } catch (Exception $e) {
           Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Installer::_resetStatus()', $e->getMessage(), $e);
        }
    }
    
    /**
     * Reset data
     *
     * @return string
     * @throws Exception
     */
    public function resetData()
    {
        try {
            $db = Mage::getSingleton('core/resource')->getConnection('core_write');
            $result = $db->query('SET FOREIGN_KEY_CHECKS = 0');
            $result = $db->query('TRUNCATE TABLE jirafe_analytics_batch');
            $result = $db->query('TRUNCATE TABLE jirafe_analytics_data');
            $result = $db->query('TRUNCATE TABLE jirafe_analytics_batch_data');
            $result = $db->query('TRUNCATE TABLE jirafe_analytics_data_attempt');
            $result = $db->query('TRUNCATE TABLE jirafe_analytics_data_error');
            $result = $db->query('ALTER TABLE jirafe_analytics_batch AUTO_INCREMENT = 1');
            $result = $db->query('ALTER TABLE jirafe_analytics_data AUTO_INCREMENT = 1');
            $result = $db->query('ALTER TABLE jirafe_analytics_batch_data AUTO_INCREMENT = 1');
            $result = $db->query('ALTER TABLE jirafe_analytics_data_attempt AUTO_INCREMENT = 1');
            $result = $db->query('ALTER TABLE jirafe_analytics_data_error AUTO_INCREMENT = 1');
            $result = $db->query('SET FOREIGN_KEY_CHECKS = 1');
            return 'Successfully truncated Jirafe Analytics batch and data tables.';
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Data::resetData()', $e->getMessage(), $e);
            Mage::throwException('DATA ERROR: Jirafe_Analytics_Model_Data::resetData(): ' . $e->getMessage());
            return false;
        }
    }

}