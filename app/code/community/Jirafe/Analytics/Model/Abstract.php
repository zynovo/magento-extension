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
    protected $_rootMap = null;
    
    protected $_mappedFields = null;
    
    /**
     * Get API to Magento field map array
     *
     * @return void
     * @throws Exception if unable to load or create field map array
     */
    
    protected function _setRootMap()
    {
        try {
            
                                            
            if (!$this->_rootMap = Mage::registry('jirafe_analytics_map') 
                                            || $regenerate = Mage::registry('jirafe_analytics_regenerate_map') ) {
                
                $this->_rootMap = Mage::getModel('jirafe_analytics/map')->getArray();
                Mage::register('jirafe_analytics_map', $this->_rootMap);
                
                if ($regenerate) {
                    Mage::register('jirafe_analytics_regenerate_map');
                }
            }
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR Jirafe_Analytics_Model_Abstract::_getMap(): ' . $e->getMessage());
        }
    }
    
    /**
     * Map fields in API to Magento using rootMap
     *
     * @return array
     * @throws Exception if unable to load or create field map array
     */
    
    protected function _getFieldMap( $element, $data )
    {
        try {
            if (!$this->_rootMap) {
                $this->_setRootMap();
            }
            
            $fieldMap = array();
            
            foreach ( $this->_rootMap[ $element ] as $key => $row ) {
                    
                    /**
                     * Check for multi-dimensional array mapping
                     * i.e. $order['payment']['amount_paid']
                     * @var array  $multitkey
                     * @var string $value
                     */
                    
                    $multitkey = explode( '|', $row['magento'] );
                    
                    if ( count( $multitkey ) == 2 ) {
                        $value = @$data[ $multitkey[0] ][ $multitkey[1] ];
                    } else {
                        $value = @$data[ $row['magento'] ];
                    }
                    
                    /**
                     * If value is empty, replace with default value from mapping table
                     */
                    
                    if ( strval( $value ) === '' ) {
                        $value = $row['default'];
                    }
                    
                    /**
                     * Convert value to proper type according to API requirements
                     */
                    
                    switch ( $row['type'] ) {
                        case 'float':
                            $value = floatval( $value );
                            break;
                        case 'int':
                            $value = intval( $value );
                            break;
                         case 'datetime':
                            $value = $this->_formatDate( $value );
                            break;
                         case 'boolean':
                            $value = (boolean) $value;
                            break;
                        default:
                            $value = strval( $value );
                            break;
                    }
                    
                    /**
                     * Separate data into multi-dimensional array 
                     * for use in creating model json objects
                     * 
                     */
                    $fieldMap[ $key ] = array( 'api' => $row['api'], 'magento' => $value );
                
            }
            
            return $fieldMap;
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR Jirafe_Analytics_Model_Abstract::_getFieldMap(): ' . $e->getMessage());
        }
    }
    
    /**
     * Return array of mapped Magento fields by element
     *
     * @param string  $element
     * @return array
     * 
     */
    
    protected function _getMagentoFieldsByElement( $element )
    {
        try {
            if ($element) {
                
                $magentoFields = Mage::getModel('jirafe_analytics/map')
                                                ->getCollection()
                                                ->addFieldToSelect('magento')
                                                ->addFilter('element',$element)
                                                ->getData();
                
                return $this->_flattenArray( $magentoFields );
            } else {
                return array();
            }
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR Jirafe_Analytics_Model_Abstract::_getFieldMap(): ' . $e->getMessage());
        }
    }
    
    /**
     * Flatten arrays structure recursively
     * 
     * @param array   $inArray
     * @param string  $subkey
     * @return array
     * @throws Exception if unable to flatten array
     */
    
    protected function _flattenArray( $inArray = null, $subkey = null )
    {
        try {
            Zend_Debug::dump($inArray);
            $outArray = array();
           
            if ($inArray) {
                $flatArray = new RecursiveIteratorIterator(new RecursiveArrayIterator($inArray));
                
                
                foreach($flatArray as $field) {
                    $outArray[] = ($subkey ? $subkey . '|' : '' ) . $field;
                }
            }
            Zend_Debug::dump($outArray);
  
            return $outArray;
        } catch (Exception $e) {
            Mage::throwException('UTILITY FUNCTION ERROR Jirafe_Analytics_Model_Abstract::_flattenArray(): ' . $e->getMessage());
        }
    }
    
    /**
     * Extract visit data from Jirafe cookie
     *
     * @return array
     * @throws Exception if unable to access $_COOKIE data
     */
    
    protected function _getVisit()
    {
        try {
            return array(
                'visit_id' => isset($_COOKIE['jirafe_vid']) ? $_COOKIE['jirafe_vid'] : '',
                'visitor_id' => isset($_COOKIE['jirafe_vis']) ? $_COOKIE['jirafe_vis'] : '',
                'pageview_id' => '',
                'last_pageview_id' => ''
            );
        } catch (Exception $e) {
            Mage::throwException('VISIT OBJECT ERROR Jirafe_Analytics_Model_Abstract::_getVisit(): ' . $e->getMessage());
        }
    }
    
    /**
     * Get all Jirafe cookie data
     *
     * @return array
     * @throws Exception if unable to access $_COOKIE data
     */
    
    protected function _getCookies()
    {
        try {
            return array(
                'jirafe_ratr' => isset($_COOKIE['jirafe_ratr']) ? $_COOKIE['jirafe_ratr'] : '',
                'jirafe_lnd' => isset($_COOKIE['jirafe_lnd']) ? $_COOKIE['jirafe_lnd'] : '',
                'jirafe_ref' => isset($_COOKIE['jirafe_ref']) ? $_COOKIE['jirafe_ref'] : '',
                'jirafe_vis' => isset($_COOKIE['jirafe_vis']) ? $_COOKIE['jirafe_vis'] : '',
                'jirafe_reftyp' => isset($_COOKIE['jirafe_reftyp']) ? $_COOKIE['jirafe_reftyp'] : '',
                'jirafe_typ' => isset($_COOKIE['jirafe_typ']) ? $_COOKIE['jirafe_typ'] : '',
                'jirafe_vid' => isset($_COOKIE['jirafe_vid']) ? $_COOKIE['jirafe_vid'] : '' 
            );
        } catch (Exception $e) {
             Mage::throwException('COOKIE OBJECT ERROR Jirafe_Analytics_Model_Abstract::_getCookies(): ' . $e->getMessage());
        }
    }
    /**
     * Format customer data as array
     *
     * @param mixed $data    Mage_Sales_Model_Quote or Mage_Sales_Model_Order
     * @return array
     * @throws Exception if unable to generate customer object
     */
    
    protected function _getCustomer( $data = null )
    {
        try {
            if ( is_numeric($data['customer_id']) ) {
                $customerId = $data['customer_id'];
            } else if ( Mage::getSingleton('customer/session')->isLoggedIn() ){
                $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            } else {
                $customerId = null;
            }
            
            if ( $customerId ) {
                $customer = Mage::getModel('customer/customer')->load( $customerId );
                return array(
                    'id' => $customer->getData('entity_id'),
                    'create_date' => $this->_formatDate( $customer->getData('created_at') ),
                    'change_date' => $this->_formatDate( $customer->getData('updated_at') ),
                    'email' => $customer->getData('email'),
                    'first_name' => $customer->getData('firstname'),
                    'last_name' => $customer->getData('lastname')
                );
            } else {
                $customer = Mage::getModel('core/session')->getVisitorData();
                $customerId = is_numeric( @$data['visitor_id'] ) ? $data['visitor_id'] : (is_numeric( @$customer['visitor_id'] ) ? $customer['visitor_id'] : 0 );
                if ( isset($data['created_at']) && isset($data['customer_email']) && isset($data['customer_firstname']) && isset($data['customer_lastname']) ) {
                    return array(
                        'id' =>  $customerId,
                        'create_date' => $this->_formatDate( $data['created_at'] ),
                        'change_date' => $this->_formatDate( $data['created_at'] ),
                        'email' => $data['customer_email'],
                        'first_name' => $data['customer_firstname'],
                        'last_name' => $data['customer_lastname']
                    );
                } else {
                    return array(
                        'id' =>  $customerId,
                        'create_date' => $this->_formatDate( $customer['first_visit_at'] ),
                        'change_date' => $this->_formatDate( $customer['last_visit_at'] ),
                        'email' => '',
                        'first_name' => 'GUEST',
                        'last_name' => 'USER'
                    );
                }
            }
        } catch (Exception $e) {
             Mage::throwException('CUSTOMER OBJECT ERROR Jirafe_Analytics_Model_Abstract::_getCustomer(): ' . $e->getMessage());
        }
    }
    
    /**
     * Format store values as catalog array
     * 
     * @param int $storeId
     * @return array
     * @throws Exception if unable to generate catalog object
     */
    
    protected function _getCatalog( $storeId = null )
    {
        try {
            if (is_numeric( $storeId )) {
                return array(
                    'id' => $storeId,
                    'name' => Mage::getModel('core/store')->load( $storeId )->getName());
            } else {
                return array(
                    'id' => '',
                    'name' => '');
            }
        } catch (Exception $e) {
             Mage::throwException('CATALOG OBJECT ERROR Jirafe_Analytics_Model_Abstract::_getCatalog(): ' . $e->getMessage());
        }
    
    }
    
    /**
     * Write log messages to db
     *
     * @param  string $message 
     * @return boolean
     */
    
    protected function _log( $type = null, $location = null, $message = null )
    {
        try {
            $log = Mage::getModel('jirafe_analytics/log');
            $log->setType( $type );
            $log->setLocation( $location );
            $log->setMessage( $message );
            $log->setCreatedDt( $this->_getCreatedDt() );
            $log->save();
            return true;
        } catch (Exception $e) {
            Mage::throwException('LOGGING ERROR Jirafe_Analytics_Model_Abstract::_log(): ' . $e->getMessage());
        }
    }
    
    /**
     * Log server load averages
     *
     * @param  string $message    message to add to log file
     * @return boolean
     * @throws Exception if sys_getloadavg() fails
     */
    
    protected function _logServerLoad( $location = null )
    {
        /**
         * @var array $load    set of three sampled server load averages
         */
        
        if (Mage::getStoreConfig('jirafe_analytics/debug/server_load')) {
            try {
                $load = sys_getloadavg();
                if (is_numeric($load[0]) && is_numeric($load[1]) && is_numeric($load[2])) {
                    $this->_log('DEBUG', $location, 'SERVER LOAD AVG: ' . number_format($load[0],2) . ' ' . number_format($load[1],2) . ' '. number_format($load[2],2));
                    return true;
                } else {
                    $this->_log('ERROR', 'Jirafe_Analytics_Model_Abstract::_logServerLoad()', $e->getMessage());
                    return false;
                }
            } catch (Exception $e) {
                $this->_log('ERROR', 'Jirafe_Analytics_Model_Abstract::_logServerLoad()', $e->getMessage());
            }
        }
    }
    
    /**
     * Format date to Jirafe API requirements: UTC in the ISO 8601:2004 format
     *
     * @param datetime $date
     * @return datetime
     */
    
    protected function _formatDate( $date )
    {
        try {
            return date( DATE_ISO8601, strtotime( $date) );
        } catch (Exception $e) {
            Mage::throwException('UTILITY ERROR Jirafe_Analytics_Model_Abstract::_formatDate(): ' . $e->getMessage());
        }
    }
    
    /**
     * Current DataTime in UTC/GMT to avoid MySQL possible timezone configuration issues
     *
     * @return string
     */
    
    protected function _getCreatedDt() 
    {
        try {
            return gmdate('Y-m-d H:i:s');
        } catch (Exception $e) {
            Mage::throwException('UTILITY ERROR Jirafe_Analytics_Model_Abstract::_getCreatedDt(): ' . $e->getMessage());
        }
    }
}