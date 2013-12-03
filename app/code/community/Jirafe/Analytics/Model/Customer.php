<?php

/**
 * Customer Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Customer extends Jirafe_Analytics_Model_Abstract
{
    /**
     * Create user admin array of data required by Jirafe API
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return mixed
     */
    public function getArray( $customer = null, $includeCookies = false )
    {
        try {
            if ( $customer ) {
                
                $data = $customer->getData();
                
                /**
                 * Get customer address 
                 */
                if ($addressId = $customer->getDefaultBilling()) {
                    $address = Mage::getModel('customer/address')->load( $addressId );
                    foreach ($address->getData() as $key => $value) {
                        if ( !array_key_exists ( $key,$customer ) ) {
                            $data[$key] = $value;
                        }
                    }
                }
                
                /**
                 * Get subscriber information
                 */
                $marketingOptIn = Mage::getModel('newsletter/subscriber')
                                      ->load($customer->getEmail(), 'subscriber_email')
                                      ->getSubscriberStatus();
                
                /**
                 * Get field map array
                 */
                $fieldMap = $this->_getFieldMap( 'customer', $data );
                
                $data = array(
                    $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                    $fieldMap['email']['api'] => $fieldMap['email']['magento'],
                    'name' => $fieldMap['first_name']['magento'] . ' ' . $fieldMap['last_name']['magento'],
                    $fieldMap['first_name']['api'] => $fieldMap['first_name']['magento'],
                    $fieldMap['last_name']['api'] => $fieldMap['last_name']['magento'],
                    $fieldMap['active_flag']['api'] => $fieldMap['active_flag']['magento'] ,
                    $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                    $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento'],
                    'marketing_opt_in' => $marketingOptIn ? true : false
                 );
                
                if ( $addressId ) {
                    $data[ $fieldMap['company']['api'] ] = $fieldMap['company']['magento'];
                    $data[ $fieldMap['phone']['api'] ] = $fieldMap['phone']['magento'];
                }
                if ($includeCookies) {
                  $data['cookies'] = $this->_getCookies();
                }
                
                return $data;
            } else {
               return array();
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Customer::getArray()', $e->getMessage(), $e);
            return false;
        }
    }
    
    /**
     * Convert customer array into JSON object
     *
     * @param array $customer
     * @return mixed
     */
    
    public function getJson( $customer = null, $isVisit = false )
    {
        if ($customer) {
            return json_encode( $this->getArray( $customer, $isVisit ) );
        } else {
            return false;
        }
        
    }
    
    /**
     * Create array of customer historical data
     *
     * @param string $filter
     * @return array
     */
    
    public function getHistoricalData( $filter = null )
    {
        try {
         
            $lastId = isset($filter['last_id']) ? (is_numeric($filter['last_id']) ?  $filter['last_id'] : null): null;
            $startDate = isset($filter['start_date']) ? $filter['start_date'] : null;
            $endDate = isset($filter['end_date']) ? $filter['end_date'] : null;
            
            $data = array();
            
            $customers = Mage::getModel('customer/customer')
                ->getCollection()
                ->addAttributeToSelect('firstname')
                ->addAttributeToSelect('lastname');
            
            if ( $lastId ) {
                $customers->addAttributeToFilter('entity_id', array('lteq' => $lastId));
            } 
            
            if ( $startDate ) {
                $customers->addAttributeToFilter('created_at', array('gteq' => $startDate));
            }
            
            if ( $endDate ) {
                $customers->addAttributeToFilter('created_at', array('lteq' => $endDate));
            }
            
            
            foreach($customers as $customer) {
                $data[] = array(
                    'type_id' => Jirafe_Analytics_Model_Data_Type::CUSTOMER,
                    'store_id' => $customer->getStoreId(),
                    'json' => $this->getJson( $customer )
                );
            }
            
            return $data;
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Customer::getHistoricalData()', $e->getMessage(), $e);
            return false;
        }
       
    }
    
    /**
     * Get customer array for beacon api javascript
     *
     * @return array
     */
    
    public function getCustomer()
    {
        return $this->_getCustomer();
    
    }
}