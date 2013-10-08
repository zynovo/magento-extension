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
     * Create JSON object for customer registration events
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return mixed
     */
    
    public function getRegisterJson( $customer )
    {
        
        try {
            $data = array(
                'customer_id' => $customer->getEntityId(),
                'first_name' => $customer->getFirstName(),
                'last_name' => $customer->getLastName(),
                'email' => $customer->getEmail(),
                'is_subscribed' => $customer->getIsSubscribed(),
                'store_id' =>  $customer->getStoreId(),
                'website_id' =>  $customer->getWebsiteId(),
                'change_date' => $this->_formatDate( $customer->getUpdatedAt() ),
                'create_date' => $this->_formatDate( $customer->getCreatedAt() )
            );
            
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Customer::getRegisterJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Create JSON object for admin customer creation events
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return mixed
     */
    
    public function getAddJson( $customer )
    {
        try {
            $data = array(
                'customer_id' => $customer->getEntityId(),
                'first_name' => $customer->getFirstName(),
                'last_name' => $customer->getLastName(),
                'email' => $customer->getEmail(),
                'is_subscribed' => $customer->getIsSubscribed(),
                'store_id' =>  $customer->getStoreId(),
                'website_id' =>  $customer->getWebsiteId(),
                'change_date' => $this->_formatDate( $customer->getUpdatedAt() ),
                'create_date' => $this->_formatDate( $customer->getCreatedAt() )
            );
    
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Customer::getAddJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Create JSON object for admin customer modification events
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return mixed
     */
    
    public function getModifyJson( $customer )
    {
        try {
            $data = array(
                'customer_id' => $customer->getEntityId(),
                'first_name' => $customer->getFirstName(),
                'last_name' => $customer->getLastName(),
                'email' => $customer->getEmail(),
                'is_subscribed' => $customer->getIsSubscribed(),
                'store_id' =>  $customer->getStoreId(),
                'website_id' =>  $customer->getWebsiteId(),
                'change_date' => $this->_formatDate( $customer->getUpdatedAt() ),
                'create_date' => $this->_formatDate( $customer->getCreatedAt() )
            );
            
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Customer::getModifyJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Create JSON object for admin customer deletion events
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return mixed
     */
    
    public function getDeleteJson( $customer )
    {
        try {
            $data = array(
                'customer_id' => $customer->getEntityId(),
                'first_name' => $customer->getFirstName(),
                'last_name' => $customer->getLastName(),
                'email' => $customer->getEmail(),
                'is_subscribed' => $customer->getIsSubscribed(),
                'store_id' =>  $customer->getStoreId(),
                'website_id' =>  $customer->getWebsiteId(),
                'change_date' => $this->_formatDate( $customer->getUpdatedAt() ),
                'create_date' => $this->_formatDate( $customer->getCreatedAt() )
            );
    
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Customer::getDeleteJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
}