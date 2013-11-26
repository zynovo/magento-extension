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
     * @return array
     * @throws Exception
     */
    
    protected function _getRootMap()
    {
        try {
            /**
             * Pull map from cache or generate new map
             */
            $cache = Mage::app()->getCache();
            
            if ( !$rootMap = $cache->load('jirafe_analytics_map') ) {
                $rootMap = json_encode( Mage::getModel('jirafe_analytics/map')->getArray() );
                $cache->save( $rootMap, 'jirafe_analytics_map', array('jirafe_analytics_map'), null);
            }
            
            return json_decode($rootMap,true);
            
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Abstract::_getRootMap()', $e->getMessage(), $e);
            return false;
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
         
            $fieldMap = array();
            
            /**
             * Get root map from cache
             */
            $this->_rootMap = $this->_getRootMap();
            
            /**
             * Build map for selected element
             */
            foreach ( $this->_rootMap[ $element ] as $key => $row ) {
                    
                    $value = @$data[ $row['magento'] ];
                    
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
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Abstract::_getFieldMap()', $e->getMessage(), $e);
            return false;
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
                                                ->addFieldToFilter('element',array('eq'=>$element));
                
                return $this->_flattenArray( $magentoFields->getData() );
            } else {
                return array();
            }
            
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Abstract::_getMagentoFieldsByElement()', $e->getMessage(), $e);
            return false;
        }
    }
    
    /**
     * Return string of attributes to select from a collection
     *
     * @param string  $element
     * @return array
     *
     */
    
    protected function _getAttributesToSelect( $element = null )
    {
        try {
            $attributes = array();
            if ($element) {
                 $cache = Mage::app()->getCache();
                 $cacheKey = "jirafe_analytics_fields_$element";
                 
                 if ( $cachedAttributes = $cache->load( $cacheKey ) ) {
                     $attributes = json_decode( $cachedAttributes );
                 } else {
                    $fields = $this->_getMagentoFieldsByElement( $element );
                    
                    foreach( $fields as $field ) {
                        if ( trim($field) && !in_array($field,$attributes) ) {
                        $attributes[] = $field;
                        }
                    }
                    
                    $cache->save( json_encode($attributes), $cacheKey, array($cacheKey), null);
                }
           }
            return $attributes;
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Abstract::_getAttributesToSelect()', $e->getMessage(), $e);
            return false;
        }
    }
    
    
    /**
     * Flatten arrays structure recursively
     * 
     * @param array   $inArray
     * @return array
     * @throws Exception if unable to flatten array
     */
    
    protected function _flattenArray( $inArray = null )
    {
        try {
            $outArray = array();
           
            if ($inArray) {
                $flatArray = new RecursiveIteratorIterator(new RecursiveArrayIterator($inArray));
                
                foreach($flatArray as $field) {
                    $outArray[] = $field;
                }
            }
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
                'visit_id' => isset($_COOKIE['jirafe_vid']) ? $_COOKIE['jirafe_vid'] : '1',
                'visitor_id' => isset($_COOKIE['jirafe_vis']) ? $_COOKIE['jirafe_vis'] : '1',
                'pageview_id' => isset($_COOKIE['jirafe_pvid']) ? $_COOKIE['jirafe_pvid'] : '1',
                'last_pageview_id' => isset($_COOKIE['jirafe_lpvid']) ? $_COOKIE['jirafe_lpvid'] : '1'
            );
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Abstract::_getVisit()', $e->getMessage(), $e);
            return false;
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
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Abstract::_getCookies()', $e->getMessage(), $e);
            return false;
        }
    }
    /**
     * Format customer data as array
     *
     * @param mixed $data    Mage_Sales_Model_Quote or Mage_Sales_Model_Order
     * @return array
     * @throws Exception if unable to generate customer object
     */
    
    protected function _getCustomer( $data = null, $includeCookies = false )
    {
        try {
            if ( isset($data['customer_id']) ) {
                $customerId = $data['customer_id'];
            } else if ( Mage::getSingleton('customer/session')->isLoggedIn() ){
                $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            } else {
                $customerId = null;
            }
            
            if ( is_numeric($customerId) ) {
                $customer = Mage::getSingleton('customer/customer')->load( $customerId );
                return Mage::getSingleton('jirafe_analytics/customer')->getArray( $customer, $includeCookies );
            } else {
                $customer = Mage::getSingleton('core/session')->getVisitorData();
                $customerId = is_numeric( @$data['visitor_id'] ) ? $data['visitor_id'] : (is_numeric( @$customer['visitor_id'] ) ? $customer['visitor_id'] : 0 );
                if ( isset($data['created_at']) && isset($data['customer_email']) && isset($data['customer_firstname']) && isset($data['customer_lastname']) ) {
                    return array(
                        'id' =>  "$customerId",
                        'create_date' => $this->_formatDate( $data['created_at'] ),
                        'change_date' => $this->_formatDate( $data['created_at'] ),
                        'email' => $data['customer_email'],
                        'first_name' => $data['customer_firstname'],
                        'last_name' => $data['customer_lastname']
                    );
                } else {
                    $createDate = $customer['first_visit_at'] ? $customer['first_visit_at'] : ( isset($data['updated_at']) ? $data['updated_at'] : Mage::helper('jirafe_analytics')->getCurrentDt() );
                    $changeDate = $customer['last_visit_at'] ? $customer['last_visit_at'] : ( isset($data['updated_at']) ? $data['updated_at'] : Mage::helper('jirafe_analytics')->getCurrentDt() );
                    
                    return array(
                        'id' =>  "$customerId",
                        'create_date' => $this->_formatDate( $createDate ),
                        'change_date' => $this->_formatDate( $changeDate ),
                        'email' => '',
                        'first_name' => 'GUEST',
                        'last_name' => 'USER'
                    );
                }
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Abstract::_getCustomer()', $e->getMessage(), $e);
            return false;
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
                    'id' => strval($storeId),
                    'name' => Mage::getSingleton('core/store')->load( $storeId )->getName());
            } else {
                return array(
                    'id' => '',
                    'name' => '');
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Abstract::_getCatalog()', $e->getMessage(), $e);
            return false;
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
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Abstract::_formatDate()', $e->getMessage(), $e);
            return false;
        }
    }
    
}