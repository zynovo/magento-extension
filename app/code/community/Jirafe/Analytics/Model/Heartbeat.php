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
     * Send heartbeat to Jirafe via cron
     * 
     * @return void
     * @throws Exception if unable to send heartbeat
     */
    public function send() 
    {
        try {
            Mage::log('send heartbeat',null,'jirafe_heartbeat.log');
        } catch (Exception $e) {
            Mage::throwException('HEARTBEAT ERROR: Jirafe_Analytics_Model_Heartbeat::send(): ' . $e->getMessage());
        }
    }
}