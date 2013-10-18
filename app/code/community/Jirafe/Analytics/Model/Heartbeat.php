<?php

/**
 * Heartbeat Model
 * 
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 * 
 */

class Jirafe_Analytics_Model_Heartbeat extends Jirafe_Analytics_Model_Api
{
    /**
     * Send heartbeat to Jirafe via REST. Trigger by cron
     * 
     * @return array
     * @throws Exception if unable to send heartbeat
     */
    public function transmit() 
    {
        try {
            $storeId = Mage::app()->getStore('default')->getId();
            $json = json_encode( array( 
                           "instance_id" => (string) Mage::getStoreConfig('jirafe_analytics/general/heartbeat_id'),
                           "version" => (string) Mage::getConfig()->getNode()->modules->Jirafe_Analytics->version,
                           "is_enabled" => (boolean) Mage::getStoreConfig('jirafe_analytics/general/enabled')
                          ) );
            $params = array(
                'url' => $this->eventApiUrl . $this->_getSiteId( $storeId ) . '/heartbeat',
                'token' => $this->_getAccessToken( $storeId ),
                'json' => $json );
            
            Mage::log ( $json, null, 'heartbeat.log');
            $response = $this->_processSingle( $params );
            
            if ( @$response['http_code'] != '200' ) {
                $this->_log( 'ERROR', 'Jirafe_Analytics_Model_Heartbeat::transmit()', json_encode( $response ) );
            }
            
            return $response;
        } catch (Exception $e) {
            Mage::throwException('HEARTBEAT ERROR: Jirafe_Analytics_Model_Heartbeat::transmit(): ' . $e->getMessage());
        }
    }
}