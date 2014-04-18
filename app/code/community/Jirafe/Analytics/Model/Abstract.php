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
    protected $_mappedFields = null;

    protected function _mapFields($fieldMap, $fields, $left='api', $right='magento')
    {
        $data = array();
        foreach ($fields as $field) {
            if (array_key_exists($field, $fieldMap) && 
                array_key_exists($left, $fieldMap[$field]) &&
                array_key_exists($right, $fieldMap[$field])) {
                $data[$fieldMap[$field][$left]] = $fieldMap[$field][$right];
            }
        }
        return $data;
    }

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

            if (!$rootMap = $cache->load('jirafe_analytics_map')) {
                $rootMap = json_encode(Mage::getModel('jirafe_analytics/map')->getArray());
                $cache->save($rootMap, 'jirafe_analytics_map', array('jirafe_analytics_map'), null);
            }

            return json_decode($rootMap,true);

        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, $e->getMessage(), $e);
            return false;
        }
    }

    /**
     * Map fields in API to Magento using rootMap
     *
     * @return array
     * @throws Exception if unable to load or create field map array
     */
    protected function _getFieldMap($element, $data)
    {
        try {
            $fieldMap = array();
            $rootMap = $this->_getRootMap();

            // Build map for selected element
            foreach ($rootMap[$element] as $key => $row) {
                $value = @$data[$row['magento']];

                // If value is empty, replace with default value from mapping table
                if (strval($value) === '') {
                    $value = $row['default'];
                }

                // Convert value to proper type according to API requirements
                switch ($row['type']) {
                case 'float':
                    $value = floatval($value);
                    break;
                case 'int':
                    $value = intval($value);
                    break;
                case 'datetime':
                    $value = $this->_formatDate($value);
                    break;
                case 'boolean':
                    $value = (boolean)$value;
                    break;
                default:
                    $value = strval($value);
                    break;
                }
                // Separate data into multi-dimensional array for use in creating model json objects.
                $fieldMap[$key] = array('api' => $row['api'], 'magento' => $value);
            }
            return $fieldMap;
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, $e->getMessage(), $e);
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
    protected function _getMagentoFieldsByElement($element)
    {
        try {
            if ($element) {

                $magentoFields = Mage::getModel('jirafe_analytics/map')
                                                ->getCollection()
                                                ->addFieldToSelect('magento')
                                                ->addFieldToFilter('element',array('eq'=>$element));

                return $this->_flattenArray($magentoFields->getData());
            } else {
                return array();
            }

        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, $e->getMessage(), $e);
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
    protected function _getAttributesToSelect($element = null)
    {
        try {
            $attributes = array();
            if ($element) {
                 $cache = Mage::app()->getCache();
                 $cacheKey = "jirafe_analytics_fields_$element";

                 if ($cachedAttributes = $cache->load($cacheKey)) {
                     $attributes = json_decode($cachedAttributes);
                 } else {
                    $fields = $this->_getMagentoFieldsByElement($element);

                    foreach($fields as $field) {
                        if (trim($field) && !in_array($field,$attributes)) {
                        $attributes[] = $field;
                        }
                    }

                    $cache->save(json_encode($attributes), $cacheKey, array($cacheKey), null);
                }
           }
            return $attributes;
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, $e->getMessage(), $e);
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
    protected function _flattenArray($inArray = null)
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
                'visit_id' => isset($_COOKIE['jirafe_vid']) ? $_COOKIE['jirafe_vid'] : '',
                'visitor_id' => isset($_COOKIE['jirafe_vis']) ? $_COOKIE['jirafe_vis'] : '',
                'pageview_id' => isset($_COOKIE['jirafe_pvid']) ? $_COOKIE['jirafe_pvid'] : '',
                'last_pageview_id' => isset($_COOKIE['jirafe_lpvid']) ? $_COOKIE['jirafe_lpvid'] : ''
           );
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, $e->getMessage(), $e);
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
            Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, $e->getMessage(), $e);
            return false;
        }
    }

    private function _getCustomerId($data)
    {
        if (isset($data['customer_id'])) {
            $customerId = $data['customer_id'];
        } else if (Mage::getSingleton('customer/session')->isLoggedIn()){
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        } else {
            $customerId = null;
        }
        return $customerId;
    }

    private function _getVisitorId($customer, $data)
    {
        if (array_key_exists('visitor_id', $data)) {
            $visitor_id = $data['visitor_id'];
            if (is_numeric($visitor_id)) {
                return $visitor_id;
            } else {
                if (array_key_exists('visitor_id', $customer)) {
                    $visitor_id = $customer['visitor_id'];
                    if (is_numeric($visitor_id)) {
                        return $visitor_id;
                    }
                }
            }
        }
        return 0;
    }

    private function _validateArray($data, $values)
    {
        foreach($values as $value) {
            if (!isset($data[$value])) {
                return false;
            }
        }
        return true;
    }

    private function _getVisitDate($customer, $data, $customerKey, $dataKey)
    {
        if (array_key_exists($customerKey, $customer)) {
            if ($customer[$customerKey]) {
                return $customer[$customerKey];
            } else {
                if (array_key_exists($dataKey, $data)) {
                    if ($data[$dataKey]) {
                        return $data[$dataKey];
                    }
                }
            }
        }
        return Mage::helper('jirafe_analytics')->getCurrentDt();
    }

    /**
     * Format customer data as array
     *
     * @param mixed $data    Mage_Sales_Model_Quote or Mage_Sales_Model_Order
     * @return array
     * @throws Exception if unable to generate customer object
     */
    protected function _getCustomer($data = null, $includeCookies = false)
    {
        try {
            $customerId = $this->_getCustomerId($data);

            if (is_numeric($customerId)) {
                $customer = Mage::getModel('customer/customer')->load($customerId);
                return Mage::getModel('jirafe_analytics/customer')->getArray($customer, $includeCookies);
            } else {
                $customer = Mage::getSingleton('core/session')->getVisitorData();
                $customerId = $this->_getVisitorId($customer, $data);

                if ($this->_validateArray($data, array('created_at','customer_email','customer_firstname','customer_lastname'))) {
                    return array(
                        'id' =>  "$customerId",
                        'create_date' => $this->_formatDate($data['created_at']),
                        'change_date' => $this->_formatDate($data['created_at']),
                        'email' => $data['customer_email'],
                        'first_name' => $data['customer_firstname'],
                        'last_name' => $data['customer_lastname']
                   );
                } else {
                    $changeDate = $this->_getVisitDate($customer, $data, 'last_visit_at', 'updated_at');
                    $createDate = $this->_getVisitDate($customer, $data, 'first_visit_at', 'updated_at');
                    return array(
                        'id' =>  "$customerId",
                        'create_date' => $this->_formatDate($createDate),
                        'change_date' => $this->_formatDate($changeDate),
                        'email' => '',
                        'first_name' => 'GUEST',
                        'last_name' => 'USER'
                   );
                }
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, $e->getMessage(), $e);
            return false;
        }
    }

    /**
     * Format date to Jirafe API requirements: UTC in the ISO 8601:2004 format
     *
     * @param datetime $date
     * @return datetime
     */
    protected function _formatDate($date)
    {
        try {
            return date(DATE_ISO8601, strtotime($date));
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, $e->getMessage(), $e);
            return false;
        }
    }

    /**
     * Create array of historical data
     *
     * @param  Zend_Paginator $paginator
     * @param  string $websiteId
     * @return array
     */
    public function getHistoricalData($paginator, $websiteId)
    {
        try {
            $data = array();
            $lastId = null;
            foreach($paginator as $item) {
                $elem = array(
                    'type_id' => $this->getDataType(),
                    'website_id' => $websiteId,
                    'json' => $this->getJson($item, false)
                );

                if ($elem['json']) {
                    $data[] = $elem;
                }
                // hack because some are arrays and some are objects
                if (is_array($item)) {
                    $lastId = $item['entity_id'];
                } else {
                    $lastId = $item->getId();
                }
            }

            return array($lastId, $data);
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, $e);
            return false;
        }
    }
}
