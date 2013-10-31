<?php

/**
 * Default Helper
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    public $logging = false;
    
    protected $_maxExecutionTime = null;
    
    protected $_memoryLimit = null;
    
    protected $_procNice = null;
    
    /**
     * Class construction & resource initialization
     */
    
    protected function _construct()
    {
         /**
         * Set debug properties to Mage::getStoreConfig() values
         */
        
        $this->logging = Mage::getStoreConfig('jirafe_analytics/debug/logging');
        
        /**
         * Set PHP override properties to Mage::getStoreConfig() values
         */
        
        $this->_maxExecutionTime = Mage::getStoreConfig('jirafe_analytics/php/max_execution_time');
        $this->_memoryLimit = Mage::getStoreConfig('jirafe_analytics/php/memory_limit');
        $this->_procNice = Mage::getStoreConfig('jirafe_analytics/php/proc_nice');
    }
    
    /**
     * Write log messages to db
     *
     * @param  string $message
     * @return boolean
     */
    
    public function log( $type = null, $location = null, $message = null )
    {
        try {
            if ( Mage::getStoreConfig('jirafe_analytics/debug/type') == 'db' ) {
                $log = Mage::getModel('jirafe_analytics/log');
                $log->setType( $type );
                $log->setLocation( $location );
                $log->setMessage( $message );
                $log->setCreatedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                $log->save();
            } else {
                Mage::log( "$location: $message ($type):",null,'jirafe_analytics.log');
            }
            return true;
        } catch (Exception $e) {
            Mage::throwException('HELPER ERROR Jirafe_Analytics_Helper_Data::log(): ' . $e->getMessage());
        }
    }
    
    /**
     * Log server load averages
     *
     * @param  string $message    message to add to log file
     * @return boolean
     * @throws Exception if sys_getloadavg() fails
     */
    
    public function logServerLoad( $location = null )
    {
        /**
         * @var array $load    set of three sampled server load averages
         */
        
        if (Mage::getStoreConfig('jirafe_analytics/debug/server_load')) {
            try {
                $load = sys_getloadavg();
                if (is_numeric($load[0]) && is_numeric($load[1]) && is_numeric($load[2])) {
                    
                    $message = 'SERVER LOAD AVG: ' . number_format($load[0],2) . ' ' . number_format($load[1],2) . ' '. number_format($load[2],2);
                    
                    if ( Mage::getStoreConfig('jirafe_analytics/debug/type') == 'db' ) {
                        Mage::helper('jirafe_analytics')->log('DEBUG', $location, $message);
                    } else {
                        Mage::log( "$location: $message",null,'jirafe_analytics.log');
                    }
                    
                    return true;
                } else {
                    Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Helper_Data::logServerLoad()', $e->getMessage());
                    return false;
                }
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Helper_Data::logServerLoad()', $e->getMessage());
            }
        }
    }
    
    /**
     * Current DataTime in UTC/GMT to avoid MySQL possible timezone configuration issues
     *
     * @return string
     */
    public function getCurrentDt()
    {
        return gmdate('Y-m-d H:i:s');
    }
    
    /**
     * Performance tuning options: override server php settings
     *
     * @return void
     */
    public function overridePhpSettings( $params = null ) 
    {
        
        if (isset($params['max_execution_time'])) {
            $this->_maxExecutionTime = $params['max_execution_time'];
        }
        
        if (isset($params['memory_limit'])) {
            $this->_memoryLimit = $params['memory_limit'];
        }
        
        if (isset($params['proc_nice'])) {
            $this->procNice = $params['proc_nice'];
        }
        
        /**
         * Set PHP max_execution_time in seconds
         * Excessively large numbers or 0 (infinite) will hurt server performance
         */
        
        if ( is_numeric( $this->_maxExecutionTime ) ) {
            
            ini_set('max_execution_time', $this->_maxExecutionTime );
            
            if ( $this->_logging ) {
                Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::_overridePhpSettings()', 'max_execution_time = ' . $this->_maxExecutionTime );
            }
        }
        
        /**
         * Set PHP memory_limit: Number + M (megabyte) or G (gigabyte)
         * Excessively large numbers will hurt server performance
         * Format: 1024M or 1G
         */
        
        if (strlen( $this->_memoryLimit ) > 1) {
            
            ini_set("memory_limit", $this->_memoryLimit );
            
            if ( $this->_logging ) {
                Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::_overridePhpSettings()', 'memory_limit = ' . $this->_memoryLimit );
            }
        }
        
        /**
         * Set PHP nice value.
         * Lower numbers = lower priority
         */
        
        if (is_numeric( $this->_procNice )) {
            
            proc_nice( $this->_procNice );
            
            if ( $this->_logging ) {
                Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Curl::_overridePhpSettings()', 'proc_nice = ' . $this->_procNice );
            }
        }
    }
}