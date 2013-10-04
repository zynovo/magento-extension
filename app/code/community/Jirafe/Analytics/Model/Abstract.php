<?php

/**
 * Abstract Class Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

abstract class Jirafe_Analytics_Model_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Format Data to Jirafe API requirements: UTC in the ISO 8601:2004 format
     * 
     * @param datetime $date
     * @return datetime
     */
    
    protected function _formatDate( $date )
    {
        return date( DATE_ISO8601, strtotime( $date) );
    }
    
    /**
     * Use PHP generated UTC date to avoid MySQL possible timezone configuration issues
     */
    
    protected function _getCreatedDt() {
        return gmdate('Y-m-d H:i:s');
    }
    
    /**
     * Log server load averages
     *
     * @param  string $message    message to add to log file
     * @return boolean
     * @throws Exception if sys_getloadavg() fails
     */
    
    protected function _logServerLoad( $message = null )
    {
        /**
         * @var array $load    set of three sampled server load averages
         */
        
        if (Mage::getStoreConfig('jirafe_analytics/debug/server_load')) {
            try {
                $load = sys_getloadavg();
                if (is_numeric($load[0]) && is_numeric($load[1]) && is_numeric($load[2])) {
                    Mage::log('SERVER LOAD AVG (' . $message . '): ' . number_format($load[0],2) . ' ' . number_format($load[1],2) . ' '. number_format($load[2],2),null,'jirafe_analytics.log');
                    return true;
                } else {
                    Mage::log('ERROR logging server load average. Invalid data returned by sys_getloadavg().',null,'jirafe_analytics.log');
                    return false;
                }
            } catch (Exception $e) {
                Mage::log('ERROR logging server load average: ' . $e->getMessage(),null,'jirafe_analytics.log');
            }
        }
    }

}