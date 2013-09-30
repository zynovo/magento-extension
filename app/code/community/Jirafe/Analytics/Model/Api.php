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
    }
    
    public function send( $type = null, $json = null ) 
    {
        
        $ch = curl_init( $this->eventApiUrl . $type);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$json);
        
        if ($this->debug) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_STDERR,$f = fopen(Mage::getBaseDir('var') . '/log/jirafe_debug_curl.log', "a+"));
        }
        
        $response = curl_exec ($ch);
        
        if ($this->debug) {
            Mage::log($response,null,'jirafe_api.log');
        
        curl_close($ch);
        return $response;
    }
    
    
}