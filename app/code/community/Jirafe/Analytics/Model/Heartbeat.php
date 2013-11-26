<?php

/**
 * Heartbeat Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 * 
 * 
 */

class Jirafe_Analytics_Model_Heartbeat extends Jirafe_Analytics_Model_Curl
{
    /**
     * Get site info for heartbeat
     * 
     * @return array
     * @throws Exception 
     */
    protected function _getSites() 
    {
        try {
            $version = Mage::getConfig()->getNode()->modules->Jirafe_Analytics->version;
            $eventApiUrl = 'https://' . Mage::getStoreConfig('jirafe_analytics/general/event_api_url');
            $instanceId = php_uname();
            $stores = Mage::app()->getStores();
            
            $site = array();
            foreach ( $stores as $store ) {
                
                $storeId = $store->getId();
                $siteId = Mage::getStoreConfig('jirafe_analytics/general/site_id', $storeId);
                
                if ( is_numeric($siteId) ) {
                 
                    $clientId = Mage::getStoreConfig('jirafe_analytics/general/client_id', $storeId);
                    $accessToken =  Mage::getStoreConfig('jirafe_analytics/general/access_token', $storeId);
                    $isEnabled = Mage::getStoreConfig('jirafe_analytics/general/enabled', $storeId);
                    
                    if ( !isset( $site[ $siteId ] ) ) {
                     
                        $site[ $siteId ] = array(
                            'request' => array(
                                'version' => strval( $version ),
                                'client_id' => strval( $clientId ),
                                'site_id' => strval( $siteId ),
                                'is_enabled' => $isEnabled ? true : false,
                                'instance_id' => strval( $instanceId ),
                                'message' => 'Hello'
                            ),
                            'params' => array(
                                'token' => $accessToken,
                                'url' => $eventApiUrl . $siteId . '/heartbeat'
                             )
                       );
                    } 
                    
                    if ( !$site[ $siteId ]['request']['is_enabled'] && $isEnabled ) {
                        $site[ $siteId ]['request']['is_enabled'] = true;
                    }
                    
                    if ( !$site[ $siteId ]['params']['token'] && $accessToken ) {
                        $site[ $siteId ]['params']['token'] = strval( $accessToken );
                    }
                    
                    if ( !$site[ $siteId ]['request']['client_id'] && $clientId ) {
                        $site[ $siteId ]['request']['client_id'] = strval( $clientId );
                    }
                }
            }
            
            return $site;
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Heartbeat::_getSites()', $e->getMessage(), $e);
            return false;
        }
    }
    
    
    /**
     * Send heartbeat to Jirafe via REST + purge old data
     * Trigger by cron
     * 
     * @return array
     * @throws Exception
     */
    public function send()
    {
        try {
            $returnData = array();
            $sites = $this->_getSites();
            
            foreach ( $sites as $site ) {
                
                $params = array(
                    'url' => $site['params']['url'],
                    'token' => $site['params']['token'],
                    'json' => json_encode( $site['request'] )
                );
                Mage::log( $params,null,'heartbeat.log');
                
                Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Heartbeat::send()', "request: " . json_encode( $site ) );
                
                $results = $this->_processSingle( $params );
                
                foreach($results as $result) { 
                 
                    Mage::log( $result,null,'heartbeat.log');
                    
                    $response = json_decode( $result['response'] );
                    if (!$response->success) {
                        Mage::helper('jirafe_analytics')->log( 'ERROR', 'Jirafe_Analytics_Model_Heartbeat::send()', $result );
                    }
                    
                }
                
                $returnData[] = $results;
            }
             
            /**
             * Purge processed data older than Mage::getStoreConfig('jirafe_analytics/general/purge_time') minutes
             */
             
            Mage::getModel('jirafe_analytics/data')->purgeData();
             
            /**
             * Purge log messages older than Mage::getStoreConfig('jirafe_analytics/debug/purge_time') minutes
            */
            if ($this->logging) {
                Mage::getModel('jirafe_analytics/log')->purgeData();
            }
             
            return json_encode($returnData);
            
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Heartbeat::send()', $e->getMessage(), $e);
            return false;
        }
    }
}