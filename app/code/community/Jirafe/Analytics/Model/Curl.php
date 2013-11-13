<?php

/**
 * Curl Model
 *
 * Magneto to Jirafe connectivity via REST API
 * 
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 * 
 *  
 *  @property string $eventApiUrl     Jirafe URL for event API
 *  
 *  @property string $accessToken     Jirafe oauth1 access token
 *  
 *  @property boolean $logging        logging toggle
 *  
 *  @property int $threads            number of threads for multithreaded cURL
 *  
 *  @property int $jsonType           batched or single item json
 *  
 *  @property int $jsonMaxSize        maximum size of json object in bytes
 *  
 *  @property int $maxExecutionTime   php.ini max_execution_time override
 *  
 *  @property int $memoryLimit        php.ini memory_limit override
 *  
 *  @property int $procNice           php.ini proc_nice override
 *  
 *  @property string $threading       single or multi curl
 *  
 *  @property int $maxAttempts        maximum number of records to process
 *  
 *  @property int $pos                array iterator
 */

class Jirafe_Analytics_Model_Curl extends Jirafe_Analytics_Model_Abstract
{
    
    protected $_isEnabled = false;
    
    public $eventApiUrl = null;
    
    public $accessToken = null;
    
    public $logging = false;
    
    public $threads = null;
    
    public $threading = null;
    
    public $maxAttempts = null;
    
    public $pos = 1;
    
    /**
     * Object constructor
     * 
     * Load user configurable variables from Mage::getStoreConfig() into object property scope
     */
    
    public function _construct() 
    {
        
        if ( $this->_isEnabled = Mage::getStoreConfig('jirafe_analytics/general/enabled') ) {
            
            /**
             * Set account properties to Mage::getStoreConfig() values
             */
            
            $this->orgId = Mage::getStoreConfig('jirafe_analytics/general/org_id');
            $this->siteId = Mage::getStoreConfig('jirafe_analytics/general/site_id');
            
            /**
             * Set debug properties to Mage::getStoreConfig() values
             */
            
            $this->logging = Mage::getStoreConfig('jirafe_analytics/debug/logging');
            
            /**
             * Set api URL property to Mage::getStoreConfig() values
             */
            
            $this->eventApiUrl = 'https://' . Mage::getStoreConfig('jirafe_analytics/general/event_api_url');
            
            /**
             * Set cURL properties to Mage::getStoreConfig() values
             */
            
            $this->threading  = Mage::getStoreConfig('jirafe_analytics/curl/threading');
            $this->threads = Mage::getStoreConfig('jirafe_analytics/curl/threads');
            $this->maxAttempts = Mage::getStoreConfig('jirafe_analytics/curl/max_attempts');
            
            /**
             * If threads not supplied by user, set to default
             */
            
            if (!is_numeric($this->threads)) {
                $this->threads = 5;
            }
        }
    }
    
    /**
     * Prepare data to pass to either single of mutli-threaded cURL
     *
     * @param array $data    data from jirafe_analytics_batch that is ready to be sent to Jirafe
     * @return array
     * @throws Exception if logging or calling of single or multi-threaded cURL fails
     */
    
    public function sendJson( $data = null, $params = null ) 
    {
        /**
         * @var array $resource   resource info after cURL completion for logging 
         * @var int $count        array iterator
         * @var array $batch      json data from param segmented into batches
         * @var boolean $stop     loop interupt
         * @var array $row        single record from param data set
         * @var array $item       single record array used for single-threaded cURL
         */
        
        if ( $this->_isEnabled ) {
            try {
                
                /**
                 * Store curl resource information for batch, batch_attempt and batch_error
                 */
                
                $resource = array();
                
                if (count( $data )) {
                    if ( $this->logging ) {
                        $startTime = time();
                        Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'BEGIN', null );
                        Mage::helper('jirafe_analytics')->logServerLoad( 'Jirafe_Analytics_Model_Curl::sendJson', null );
                        Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'START TIME = ' . date("H:i:s", $startTime) . ' UTC', null );
                        Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'EVENT API URL = ' . $this->eventApiUrl, null );
                        Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'BATCH SIZE = ' . $this->threads, null );
                    }
                    
                    Mage::helper('jirafe_analytics')->overridePhpSettings( $params );
                    /**
                     * Determine CURL method
                     */
                    
                    if ( $this->threading === 'multi') {
                        
                        /**
                         * Process using multithreaded cURL
                         */
                        
                        if ( $this->logging ) {
                            Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'CURL: MULTITHREADED', null );
                        }
                        
                        $count = 1;
                        $threadBatch = array();
                        $stop = false;
                        
                        
                        
                        /**
                         * Create CURL threads
                         */
                        
                        foreach($data as $row) {
                        
                           if ($count > $this->threads) {
                                $resource[] = $this->_processMulti($threadBatch);
                                $threadBatch = array();
                                $count = 1;
                            }
                            
                           $item = array(
                                'batch_id' => $row['id'],
                                'url' => $this->eventApiUrl . $this->_getSiteId( $row['store_id'] ) . '/batch',
                                'token' => $this->_getAccessToken( $row['store_id'] ),
                                'json' =>  $row['json'] );
                           
                           if ( $this->logging ) {
                               Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'BATCH ID = ' . $item['batch_id'], null  );
                               Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'ACCESS TOKEN = ' . $item['token'], null  );
                               Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'EVENT API URL = ' . $item['url'], null  );
                               Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'JSON = ' . $item['json'], null  );
                            }
                            
                            $threadBatch[] = $item;
                            $count++;
                        }
                        
                        /**
                         * Final batch may be less than $this->threads. 
                         * Process batch separately.
                         */
                        
                        if (count($threadBatch) > 0 && !$stop) {
                            $resource[] = $this->_processMulti($threadBatch);
                        }
                    } else {
                        
                        if ( $this->logging ) {
                            Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'CURL: SINGLETHREADED', null  );
                        }
                        
                        /**
                         * Process using standard single threaded cURL
                         */
                        
                        foreach($data as $row) {
                            
                            $item = array(
                                'batch_id' => $row['id'],
                                'url' => $this->eventApiUrl . $this->_getSiteId($row['store_id']) . '/batch',
                                'token' => $this->_getAccessToken( $row['store_id'] ),
                                'json' =>  $json );
                            
                            if ( $this->logging ) {
                               Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'BATCH ID = ' . $item['batch_id'], null  );
                               Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'ACCESS TOKEN = ' . $item['token'], null  );
                               Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'EVENT API URL = ' . $item['url'], null  );
                               Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'JSON = ' . $item['json'], null  );
                            }
                            
                            $resource[] = $this->_processSingle( $item );
                        }
                    }
                    
                    if ($this->logging) {
                        
                        /**
                         * Log the total execution time
                         */
                        
                        $endTime = time();
                        $totalTime = $endTime - $startTime;
                        
                        Mage::helper('jirafe_analytics')->logServerLoad( 'Jirafe_Analytics_Model_Curl::send');
                        Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', "TOTAL PROCESSING TIME = $totalTime seconds", null );
                        Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::sendJson()', 'END TIME = ' . date("H:i:s", $endTime) . ' UTC', null );
                       
                    }
                }
                
                return $resource;
            } catch (Exception $e) {
                Mage::throwException('CURL ERROR: Jirafe_Analytics_ModelJirafe_Analytics_Model_Curl::sendJson(): ' . $e->getMessage(), null );
            }
        }
    }
    
    
    /**
     * Send heartbeat to Jirafe via REST + purge old data
     * Trigger by cron
     * @return array
     * @throws Exception if unable to send heartbeat
     */
    public function heartbeat()
    {
        try {
            $storeId = Mage::app()->getStore('default')->getId();
            $json = json_encode( array(
                'instance_id' => (string) Mage::getStoreConfig('jirafe_analytics/general/heartbeat_id'),
                'version' => (string) Mage::getConfig()->getNode()->modules->Jirafe_Analytics->version,
                'is_enabled' => (boolean) Mage::getStoreConfig('jirafe_analytics/general/enabled')
            ) );
            $params = array(
                'url' => $this->eventApiUrl . $this->_getSiteId( $storeId ) . '/heartbeat',
                'token' => $this->_getAccessToken( $storeId ),
                'json' => $json );
            
            $response = $this->_processSingle( $params );
            
            if ( @$response['http_code'] != '200' ) {
                Mage::helper('jirafe_analytics')->log( 'ERROR', 'Jirafe_Analytics_Model_Curl::heartbeat()', json_encode( $response ) );
            }
            
            /**
             * Purge processed data older than Mage::getStoreConfig('jirafe_analytics/general/purge_time') minutes
             */
            
            Mage::getSingleton('jirafe_analytics/data')->purgeData();
            
            Mage::getSingleton('jirafe_analytics/batch')->purgeData();
            
            /**
             * Purge log messages older than Mage::getStoreConfig('jirafe_analytics/debug/purge_time') minutes
             */
            if ($this->logging) {
                Mage::getSingleton('jirafe_analytics/log')->purgeData();
            }
            
            return $response;
        } catch (Exception $e) {
            Mage::throwException('HEARTBEAT ERROR: Jirafe_Analytics_Model_Curl::heartbeat(): ' . $e->getMessage());
        }
    }
    /**
     * Send batch data using standard single threaded cURL
     *
     * @param array $batch    segment of data from jirafe_analytics_batch
     * @return array
     * @throws Exception if curl_exec() fails
     */
    
    protected function _processSingle( $item = null )
    {
        /**
         * @var resource $thread  cURL resource thread for one item
         * @var int $resourceId   cURL resource id
         * @var string $response  URL response json
         * @var array $info       cURL data object
         * @var array $resource   resource info after cURL completion for logging
         */
        
        try {
            
            Mage::helper('jirafe_analytics')->logServerLoad('Jirafe_Analytics_Model_Curl::_processSingle');
            
            $thread = curl_init();
            curl_setopt( $thread, CURLOPT_URL, $item['url'] );
            curl_setopt( $thread, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $item['token'],
                'Content-Type: application/json',
                'Content-Length: ' . strlen($item['json'])) );
            curl_setopt( $thread, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $thread, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt( $thread, CURLINFO_HEADER_OUT, true );
            curl_setopt( $thread, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt( $thread, CURLOPT_POSTFIELDS, $item['json'] );
            
            if ($this->logging) {
                curl_setopt($thread, CURLOPT_VERBOSE, true);
            }
            
            $resourceId = intval($thread);
            $resource[ $resourceId ]['created_dt'] = Mage::helper('jirafe_analytics')->getCurrentDt();
            
            if (isset($item['batch_id'])) {
                $resource[ $resourceId ]['batch_id'] = $item['batch_id'];
            }
            
            $response = curl_exec($thread);
            $resource[ $resourceId ]['response'] = $response;
            
            $info = curl_getinfo($thread) ;
            $resource[ $resourceId ]['http_code'] = $info['http_code'] ;
            $resource[ $resourceId ]['total_time'] = $info['total_time'];
            
            curl_close($thread);
             Mage::helper('jirafe_analytics')->logServerLoad('Jirafe_Analytics_Model_Curl::_processSingle');
            return $resource;
        } catch (Exception $e) {
           Mage::throwException('CURL ERROR: Jirafe_Analytics_Model_Curl::_processSingle(): ' . $e->getMessage());
        }
    }
    
    /**
     * Send batch data using multi-threaded cURL
     * 
     * @param array $batch    json records from jirafe_analytics_batch
     * @return array
     * @throws Exception if curl_multi execution fails
     */
    
    protected function _processMulti( $batch )
    {
        /**
         * @var resource $mh      primary cURL multihandler
         * @var array $ch         cURL threads for closing
         * @var array $resource   resource info after cURL completion for logging
         * @var resource $thread  cURL resource thread for one item
         * @var int $i            array iterator
         * @var array $info       cURL data object
         */
        
        try {
            
            Mage::helper('jirafe_analytics')->logServerLoad('Jirafe_Analytics_Model_Curl::_processMulti');
            
             /**
              * Initialize multithreaded cURL handle
              */
            
            $mh = curl_multi_init();
            
            /**
             * store cURL threads in separate array 
             */
            
            $ch = array();
             
            /**
             * store resource information for logging
             */
            
            $resource = array();
            
            /**
             * Add all urls and json from batch to multithread cURL handle
             */
            
            for ($i = 0; $i < $this->threads; $i++) {
                
                if (isset($batch[$i])) {
                    $thread = curl_init();
                    curl_setopt($thread, CURLOPT_URL, $batch[$i]['url']);
                    curl_setopt($thread, CURLOPT_HTTPHEADER, array(
                        'Authorization: Bearer ' . $batch[$i]['token'],
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($batch[$i]['json'])));
                    curl_setopt($thread, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($thread, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($thread, CURLINFO_HEADER_OUT, true);
                    curl_setopt($thread, CURLOPT_MAXREDIRS, $this->threads);
                    curl_setopt($thread, CURLOPT_CUSTOMREQUEST, 'PUT');
                    curl_setopt($thread, CURLOPT_POSTFIELDS,$batch[$i]['json']);
                    
                    if ($this->logging) {
                       curl_setopt($thread, CURLOPT_VERBOSE, true);
                    }
                    
                    curl_multi_add_handle($mh, $thread);
                    $ch[] = $thread;
                    $resource[intval($thread)]['batch_id'] = $batch[$i]['batch_id'];
                }
            }
            
            $thread = null;
            $still_running = null;
            
            /**
             * Run each individual thread and wait for completion
             */
            
            $this->_curl_multi_exec($mh, $still_running);
            
            do { 
                curl_multi_select($mh); 
                $this->_curl_multi_exec($mh, $still_running);
                while ($info = curl_multi_info_read($mh)) {
                    $resource[intval($info['handle'])]['response'] = curl_multi_getcontent($info['handle']);
                }
            } while ($still_running);
           
            /**
             * close the individual threads
             */
            
            foreach($ch as $thread) {
                $info = curl_getinfo($thread);
                curl_multi_remove_handle($mh, $thread);
                $resource[intval($thread)]['http_code'] = $info['http_code'] ;
                $resource[intval($thread)]['total_time'] = $info['total_time'];
                $resource[intval($thread)]['created_dt'] = Mage::helper('jirafe_analytics')->getCurrentDt();
            }
            
            /**
             * close primary multi-hander
             */
            
            curl_multi_close($mh);
            Mage::helper('jirafe_analytics')->logServerLoad('Jirafe_Analytics_Model_Curl::_processMulti');
            return $resource;
        } catch (Exception $e) {
            Mage::throwException('CURL ERROR: Jirafe_Analytics_Model_Curl::_processMulti(): ' . $e->getMessage());
        }
    }
    
    /**
     * Wrapper for curl_multi_exec to handle curl_multi_select wait issues
     * 
     * @param  resource $mh             primary curl multihandler
     * @param  boolean $still_running   curl subthread has completed
     * @return int
     * @throws Exception if curl_multi_exec() fails
     */
    protected function _curl_multi_exec( $mh, &$still_running ) 
    {
        /**
         * @var int $rv    A cURL code defined in the cURL Predefined Constants.
         */
        try {
            do {
                $rv = curl_multi_exec( $mh, $still_running );
            } while ($rv == CURLM_CALL_MULTI_PERFORM);
            return $rv;
        } catch (Exception $e) {
            Mage::throwException('CURL ERROR: Jirafe_Analytics_Model_Curl::_curl_multi_exec(): ' . $e->getMessage());
        }
    }
    
    /**
     * Determine site Id by store Id
     * 
     * If 0 (admin store) or not number, set to the default value of 1
     * 
     * @param int $storeID    Magento store id from core_store
     * @return int
     * @throws Exception if unable to determine site id
     */
    protected function _getSiteId( $storeId = null ) 
    {
        /**
         * @var int $siteId    Jirafe SiteId
         */
        
        try {
            $siteId = Mage::getStoreConfig( 'jirafe_analytics/general/site_id', $storeId );
            if (!is_numeric($siteId)) {
                $siteId = 0;
            }
            return $siteId;
        } catch (Exception $e) {
            Mage::throwException('API ERROR: Jirafe_Analytics_Model_Curl::_getSiteId(): ' . $e->getMessage());
        }
    }
    
    /**
     * Determine access token by store Id
     *
     * @param int $storeID    Magento store id from core_store
     * @return string
     * @throws Exception if unable to return access token
     */
    protected function _getAccessToken( $storeId = null )
    {
        try {
            return Mage::getStoreConfig( 'jirafe_analytics/general/access_token', $storeId );
        } catch (Exception $e) {
            Mage::throwException('API ERROR: Jirafe_Analytics_Model_Curl::_getAccessToken(): ' . $e->getMessage());
        }
    }
    
}