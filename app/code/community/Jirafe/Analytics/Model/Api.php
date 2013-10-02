<?php

/**
 * Api Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Api extends Mage_Core_Model_Abstract
{
    
    public $orgId = null;
    
    public $siteId = null;
    
    public $eventApiUrl = null;
    
    public $accessToken = null;
    
    public $debug = false;
    
    public $batchSize = null;

    public $maxExecutionTime = null;

    public $memoryLimit = null;

    public $procNice = null;

    public $pos = null;
    
    public function _construct() 
    {
        $this->orgId = Mage::getStoreConfig('jirafe_analytics/settings/org_id');
        $this->siteId = Mage::getStoreConfig('jirafe_analytics/settings/site_id');
        $this->debug = Mage::getStoreConfig('jirafe_analytics/settings/debug');
        
        if (Mage::getStoreConfig('jirafe_analytics/settings/enable_sandbox')) {
            $this->eventApiUrl = Mage::getStoreConfig('jirafe_analytics/sandbox/event_api_url') . $this->siteId . '/';
            $this->accessToken = Mage::getStoreConfig('jirafe_analytics/sandbox/access_token');
        } else {
            $this->eventApiUrl = Mage::getStoreConfig('jirafe_analytics/production/event_api_url') . $this->siteId . '/';
            $this->accessToken = Mage::getStoreConfig('jirafe_analytics/production/access_token');
        }
        
        $this->enableTuning = Mage::getStoreConfig('jirafe_analytics/tuning/enable');
        $this->maxExecutionTime = Mage::getStoreConfig('jirafe_analytics/tuning/max_execution_time');
        $this->memoryLimit = Mage::getStoreConfig('jirafe_analytics/tuning/memory_limit');
        $this->procNice = Mage::getStoreConfig('jirafe_analytics/tuning/proc_nice');
        $this->batchSize = Mage::getStoreConfig('jirafe_analytics/tuning/batch_size');
        $this->maxPos = Mage::getStoreConfig('jirafe_analytics/tuning/max_pos');
        $this->pos = 1;
        
        if (!is_numeric($this->batchSize)) {
            $this->batchSize = 5;
        }
    }
    
    public function send( $data = null ) 
    {
        
        try {
            if ( $this->debug ) {
                $startTime = time();
                Mage::log('BEGIN Jirafe_Analytics_Model_Api::send()',null,'jirafe_analytics.log');
                Mage::log('----- START TIME = ' . date("H:i:s", $startTime) . ' UTC',null,'jirafe_analytics.log');
                Mage::log('----- EVENT API URL = ' . $this->eventApiUrl,null,'jirafe_analytics.log');
                Mage::log('----- ACCESS TOKEN = ' .  $this->accessToken,null,'jirafe_analytics.log');
                Mage::log('----- BATCH SIZE = ' . $this->batchSize,null,'jirafe_analytics.log');
            }
        
            if ( $this->enableTuning ) {
                /**
                 * Debug: limit number of urls
                 */
                if (is_numeric($this->maxPos) && $this->debug) {
                    Mage::log('----- DEBUG SETTING: max number of URLs = ' . $this->maxPos,null,'jirafe_analytics.log');
                }
                /**
                 * Set PHP max_execution_time in seconds
                 * Excessively large numbers or 0 (infinite) will hurt server performance
                 */
                
                if (is_numeric($this->maxExecutionTime)) {
                   // ini_set("max_execution_time", $this->maxExecutionTime);
                    
                    if ($this->debug) {
                        Mage::log('----- PHP SETTING: max_execution_time = ' . $this->maxExecutionTime,null,'jirafe_analytics.log');
                    }
                }
                
                /**
                 * Set PHP memory_limit: Number + M (megabyte) or G (gigabyte)
                 * Excessively large numbers will hurt server performance
                 * Format: 1024M or 1G
                 */
                if (strlen($this->memoryLimit) > 1) {
                   // ini_set("memory_limit", $this->memoryLimit);
                    
                    if ($this->debug) {
                        Mage::log('----- PHP SETTING: memory_limit = ' . $this->memoryLimit,null,'jirafe_analytics.log');
                    }
                }
                
                /**
                 * Set PHP nice value.
                 * Lower numbers = lower priority
                 */
                if (is_numeric($this->procNice)) {
                  //  proc_nice($this->procNice);
                    
                    if ($this->debug) {
                        Mage::log('----- PHP SETTING: proc_nice = ' . $this->procNice,null,'jirafe_analytics.log');
                    }
                }
            }
            
            $count = 1;
            $batch = array();
            $stop =false;
            /**
             * Create batches of URLs and JSON in arrays with $this->batchSize elements
             */
            
            foreach($data as $row) {
            
               if ($count > $this->batchSize) {
                    $this->_process($batch);
                    $batch = array();
                    $count = 1;
                }
                
                $batch[] = array(
                        'queue_id' => $row['id'],
                        'url' => $this->eventApiUrl . $row['type'],
                        'json' =>  $row['content'], );
                $count++;
            }
            
            /**
             * Final batch may be less than $this->batchSize
             * Process separately
             */
            if (count($batch) > 0 && !$stop) {
                $this->_process($batch);
            }
            
            if ($this->debug) {
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
            
            
            
        } catch (Exception $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
    }
    
    protected function _process( $batch )
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
         * Add all urls and json from batch to multithread cURL handle
         */
        for ($i = 0; $i < $this->batchSize; $i++) {
            
            if (isset($batch[$i])) {
                $thread = curl_init();
                curl_setopt($thread, CURLOPT_URL, $batch[$i]['url']);
                curl_setopt($thread, CURLOPT_HTTPHEADER, array(
                        'Authorization: Bearer ' . $this->accessToken,
                        'Content-Type: application/json',
                        'Queue-Id: ' . $batch[$i]['queue_id'],
                        'Content-Length: ' . strlen($batch[$i]['json'])));
                curl_setopt($thread, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($thread, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($thread, CURLINFO_HEADER_OUT, true);
                curl_setopt($thread, CURLOPT_MAXREDIRS, $this->batchSize);
                curl_setopt($thread, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($thread, CURLOPT_POSTFIELDS,$batch[$i]['json']);
                
                if ($this->debug) {
                   curl_setopt($thread, CURLOPT_VERBOSE, true);
                }
                
                curl_multi_add_handle($mh, $thread);
                $ch[] = $thread;
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
            if ($this->debug) {
                while ($info = curl_multi_info_read($mh)) {
                    Mage::log(curl_multi_getcontent($info['handle']),null,'jirafe_analytics.log');
                }
            }
        } while ($still_running);
       
        /**
         * close the individual threads
         */
        foreach($ch as $thread) {
            $info = curl_getinfo($thread);
            curl_multi_remove_handle($mh, $thread);
            if (preg_match('/(?<=Queue\-Id\:\s)\d+/', $info['request_header'], $matches)) {
                $attempt = Mage::getModel('jirafe_analytics/queue_attempt')->record( $matches[0],  $info);
            }
           
        }
        
        curl_multi_close($mh);
        
        return true;
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
        
        if (Mage::getStoreConfig('jirafe_analytics/tuning/log_load')) {
            try {
                $loadAvg = exec("uptime | awk -F'load average:' '{ print $2 }'");
                Mage::log("SERVER LOAD AVG ($message): $loadAvg",null,'jirafe_analytics.log');
            } catch (Exception $e) {
                Mage::log('ERROR logging server load average: ' . $e->getMessage(),null,'jirafe_analytics.log');
            }
             
        }
    }
}