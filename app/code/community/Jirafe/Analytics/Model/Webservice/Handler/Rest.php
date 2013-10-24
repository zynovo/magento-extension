<?php

/**
 * Webservice Handler Rest Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Webservice_Handler_Rest
{    
    
    public function options($arg) {
        
        try {
            $username = $arg['apiuser'];
            $apiKey = $arg['apikey'];
            Mage::getSingleton('api/session')->init();
            $user = Mage::getSingleton('api/session')->login($username, $apiKey);
            
            if(!is_object($user)) {
                Mage::throwException(Mage::helper('core')->__('Login error.'));
            }
           
        } catch (Mage_Core_Exception $e) {
            return array(
                "action" => "options", 
                "result" => "error", 
                "resultmessage" => $e->getMessage()
            );
        }
        return array(
            "action" => "options", 
            "result" => "success", 
            "resultmessage" => "options returned"
        );
    }
    
    
}