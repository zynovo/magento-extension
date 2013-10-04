<?php

/**
 * Api Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Api extends Jirafe_Analytics_Model_Abstract
{
    
    public $orgId = null;
    
    public $siteId = null;
    
    public $eventApiUrl = null;
    
    public $accessToken = null;
    
    public $logging = false;
    
    public $batchSize = null;
    
    public $maxExecutionTime = null;
    
    public $memoryLimit = null;
    
    public $procNice = null;
    
    public $threading = null;
    
    public $maxAttempts = null;
    
    public $pos = null;
    
    public function _construct() 
    {
        /**
         * Account Settings
         */
        $this->orgId = Mage::getStoreConfig('jirafe_analytics/account/org_id');
        $this->siteId = Mage::getStoreConfig('jirafe_analytics/account/site_id');
        
        /**
         * Debug Settings
         */
        $this->logging = Mage::getStoreConfig('jirafe_analytics/debug/logging');
        
        /**
         * Production or Stage Authentication Settings
         */
        if (Mage::getStoreConfig('jirafe_analytics/sandbox/enable')) {
            $this->eventApiUrl = Mage::getStoreConfig('jirafe_analytics/sandbox/event_api_url') . $this->siteId . '/';
            $this->accessToken = Mage::getStoreConfig('jirafe_analytics/sandbox/access_token');
        } else {
            $this->eventApiUrl = Mage::getStoreConfig('jirafe_analytics/production/event_api_url') . $this->siteId . '/';
            $this->accessToken = Mage::getStoreConfig('jirafe_analytics/production/access_token');
        }
        
        /**
         * PHP Override Settings
         */
        $this->maxExecutionTime = Mage::getStoreConfig('jirafe_analytics/php/max_execution_time');
        $this->memoryLimit = Mage::getStoreConfig('jirafe_analytics/php/memory_limit');
        $this->procNice = Mage::getStoreConfig('jirafe_analytics/php/proc_nice');
        
        
        /**
         * cURL settings
         */
        $this->threading  = Mage::getStoreConfig('jirafe_analytics/curl/threading');
        $this->batchSize = Mage::getStoreConfig('jirafe_analytics/tuning/batch_size');
        $this->maxAttempts = Mage::getStoreConfig('jirafe_analytics/tuning/max_attempts');
        
        $this->pos = 1;
        
        if (!is_numeric($this->batchSize)) {
            $this->batchSize = 5;
        }
    }
    
    
    public function send( $data = null ) 
    {
        
        try {
            if ( $this->logging ) {
                $startTime = time();
                Mage::log('BEGIN Jirafe_Analytics_Model_Api::send()',null,'jirafe_analytics.log');
                Mage::log('----- START TIME = ' . date("H:i:s", $startTime) . ' UTC',null,'jirafe_analytics.log');
                Mage::log('----- EVENT API URL = ' . $this->eventApiUrl,null,'jirafe_analytics.log');
                Mage::log('----- ACCESS TOKEN = ' .  $this->accessToken,null,'jirafe_analytics.log');
                Mage::log('----- BATCH SIZE = ' . $this->batchSize,null,'jirafe_analytics.log');
            }
            
            /**
             * Set PHP max_execution_time in seconds
             * Excessively large numbers or 0 (infinite) will hurt server performance
             */
            
            if (is_numeric($this->maxExecutionTime)) {
                
                ini_set("max_execution_time", $this->maxExecutionTime);
                
                if ($this->logging) {
                    Mage::log('----- PHP SETTING OVERRIDE: max_execution_time = ' . $this->maxExecutionTime,null,'jirafe_analytics.log');
                }
            }
            
            /**
             * Set PHP memory_limit: Number + M (megabyte) or G (gigabyte)
             * Excessively large numbers will hurt server performance
             * Format: 1024M or 1G
             */
            if (strlen($this->memoryLimit) > 1) {
                
                ini_set("memory_limit", $this->memoryLimit);
                
                if ($this->logging) {
                    Mage::log('----- PHP SETTING OVERRIDE: memory_limit = ' . $this->memoryLimit,null,'jirafe_analytics.log');
                }
            }
            
            /**
             * Set PHP nice value.
             * Lower numbers = lower priority
             */
            if (is_numeric($this->procNice)) {
                
                proc_nice($this->procNice);
                
                if ($this->logging) {
                    Mage::log('----- PHP SETTING OVERRIDE: proc_nice = ' . $this->procNice,null,'jirafe_analytics.log');
                }
            }
            
            /**
             * store curl resource information for queue, queue_attempt and queue_error
             */
            $resource = array();
            
            if ( $this->threading === 'multi') {
                
                /**
                 * Process using multithreaded cURL
                 */
                
                $count = 1;
                $batch = array();
                $stop =false;
                
                /**
                 * Create batches of URLs and JSON in arrays with $this->batchSize elements
                 */
                
                foreach($data as $row) {
                
                   if ($count > $this->batchSize) {
                        $resource[] = $this->_processMulti($batch);
                        $batch = array();
                        $count = 1;
                    }
                    
                    $batch[] = array(
                            'queue_id' => $row['id'],
                            'url' => $this->eventApiUrl . $row['type'],
                            'json' =>  $row['content'] );
                    
                    $count++;
                }
                
                /**
                 * Final batch may be less than $this->batchSize
                 * Process separately
                 */
                if (count($batch) > 0 && !$stop) {
                    $resource[] = $this->_processMulti($batch);
                }
            } else {
                
                /**
                 * Process using standard single threaded cURL
                 */
                foreach($data as $row) {
                    
                    $item = array(
                        'queue_id' => $row['id'],
                        'url' => $this->eventApiUrl . $row['type'],
                        'json' =>  $row['content'] );
                    
                    $resource[] = $this->_processSingle( $item );
                }
            }
            
            if ($this->logging) {
                
                /**
                 * Log the total execution time
                 */
                $endTime = time();
                $totalTime = $endTime - $startTime;
                
                Mage::log('----- END TIME = ' . date("H:i:s", $endTime) . ' UTC',null,'jirafe_analytics.log');
                Mage::log("----- TOTAL PROCESSING TIME = $totalTime seconds",null,'jirafe_analytics.log');
                Mage::log('END Jirafe_Analytics_Model_Api::send()',null,'jirafe_analytics.log');
                
                $this->_logServerLoad('processing complete');
            }
            
            return $resource;
            
        } catch (Exception $e) {
            Mage::log('ERROR: ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Send batch data using standard single threaded cURL
     *
     * @param array $batch
     * @return array
     */
    protected function _processSingle( $item = null )
    {
        $thread = curl_init();
        curl_setopt( $thread, CURLOPT_URL, $item['url'] );
        curl_setopt( $thread, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->accessToken,
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
        $resource[ $resourceId ]['created_dt'] = $this->_getCreatedDt();
        $resource[ $resourceId ]['queue_id'] = $item['queue_id'];
        
        $response = curl_exec($thread);
        $resource[ $resourceId ]['response'] = $response;
        
        $info = curl_getinfo($thread) ;
        $resource[ $resourceId ]['http_code'] = $info['http_code'] ;
        $resource[ $resourceId ]['total_time'] = $info['total_time'];
        
        curl_close($thread);
        
        return $resource;
      
    }
    
    /**
     * Send batch data using multi-threaded cURL
     * 
     * @param array $batch
     * @return array
     */
    protected function _processMulti( $batch )
    {
        /**
         * Initialize multithreaded cURL handle
         */
        $mh = curl_multi_init();
        
        /**
         * store cURL threads in separate array for closing
         */
        $ch = array();
         
        /**
         * store resource information for logging
         */
        $resource = array();
        
        /**
         * Add all urls and json from batch to multithread cURL handle
         */
        for ($i = 0; $i < $this->batchSize; $i++) {
            
            if (isset($batch[$i])) {
                $thread = curl_init();
                curl_setopt($thread, CURLOPT_URL, $batch[$i]['url']);
                curl_setopt($thread, CURLOPT_HTTPHEADER, array(
                    'Authorization: Bearer ' . $this->accessToken,
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($batch[$i]['json'])));
                curl_setopt($thread, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($thread, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($thread, CURLINFO_HEADER_OUT, true);
                curl_setopt($thread, CURLOPT_MAXREDIRS, $this->batchSize);
                curl_setopt($thread, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($thread, CURLOPT_POSTFIELDS,$batch[$i]['json']);
                
                if ($this->logging) {
                   curl_setopt($thread, CURLOPT_VERBOSE, true);
                }
                
                curl_multi_add_handle($mh, $thread);
                $ch[] = $thread;
                $resource[intval($thread)]['queue_id'] = $batch[$i]['queue_id'];
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
            $resource[intval($thread)]['created_dt'] = $this->_getCreatedDt();
        }
        
        curl_multi_close($mh);
        
        return $resource;
    }
    
    /**
     * wrapper for curl_multi_exec to handle curl_multi_select wait issues
     */
    protected function _curl_multi_exec($mh, &$still_running ) 
    {
        do {
            $rv = curl_multi_exec( $mh, $still_running );
        } while ($rv == CURLM_CALL_MULTI_PERFORM);
        return $rv;
    }
    
    /**
     * If option is enabled in module system config, log server load averages
     */
    protected function _logServerLoad($message = null)
    {
        
        if (Mage::getStoreConfig('jirafe_analytics/debug/server_load')) {
            try {
                $loadAvg = exec("uptime | awk -F'load average:' '{ print $2 }'");
                Mage::log("SERVER LOAD AVG ($message): $loadAvg",null,'jirafe_analytics.log');
            } catch (Exception $e) {
                Mage::log('ERROR logging server load average: ' . $e->getMessage(),null,'jirafe_analytics.log');
            }
             
        }
    }
}