<?php

/**
 * Event Observer Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Observer extends Jirafe_Analytics_Model_Abstract
{

    /**
     * Capture add product to cart event
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function cartSave( Varien_Event_Observer $observer )
    {
        try {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $json = Mage::getModel('jirafe_analytics/cart')->getJson( $quote );
            
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::CART );
            $queue->setContent( $json );
            $queue->setStoreId( $quote->getStoreId() );
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR cartProductAdd(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    
  
    
    
    /**
     * Capture customer registration event
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function customerRegister( Varien_Event_Observer $observer ) 
    {
        try {
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::CUSTOMER );
            $queue->setContent( Mage::getModel('jirafe_analytics/customer')->getRegisterJson( $observer->getCustomer() ) );
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR customerRegister(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Capture category save event
     * 
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function categorySave( Varien_Event_Observer $observer ) 
    {
         try {
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::CATEGORY );
            
            if ($observer->getCategory()->getCreatedAt() === $observer->getCategory()->getUpdatedAt()) {
                $queue->setContent( Mage::getModel('jirafe_analytics/category')->getAddJson( $observer->getCategory() ) );
            } else {
                $queue->setContent( Mage::getModel('jirafe_analytics/category')->getModifyJson( $observer->getCategory() ) );
            }
            
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR categorySave(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /** 
     * Capture category delete event
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function categoryDelete( Varien_Event_Observer $observer ) 
    {
         try {
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::CATEGORY );
            $queue->setContent( Mage::getModel('jirafe_analytics/category')->getDeleteJson( $observer->getCategory() ) );
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR categoryDelete(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
   
    
    /**
     * Capture customer delete events
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function customerDelete( Varien_Event_Observer $observer )
    {
        try {
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::CUSTOMER );
            $queue->setContent( Mage::getModel('jirafe_analytics/customer')->getDeleteJson( $observer->getCustomer() ) );
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR customerDelete(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Capture customer save events
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function customerSave( Varien_Event_Observer $observer )
    {
        try {
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::CUSTOMER );
    
            if ($observer->getCustomer()->getCreatedAt() === $observer->getCustomer()->getUpdatedAt()) {
                $queue->setContent( Mage::getModel('jirafe_analytics/customer')->getAddJson( $observer->getCustomer() ) );
            } else {
                $queue->setContent( Mage::getModel('jirafe_analytics/customer')->getModifyJson( $observer->getCustomer() ) );
            }
    
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR customerSave(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Capture order save event
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function orderPlaceAfter( Varien_Event_Observer $observer )
    {
        try {
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::ORDER );
            $queue->setContent( Mage::getModel('jirafe_analytics/order')->getAddJson( $observer ) );
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR orderPlaceAfter(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Capture order cancel events
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function orderCancel( Varien_Event_Observer $observer ) 
    {
        try {
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::ORDER );
            $queue->setContent( Mage::getModel('jirafe_analytics/order')->getCancelJson( $observer ) );
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR orderPlaceAfter(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    
    /**
     *
     * Capture product save event
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
     
    public function productSave( Varien_Event_Observer $observer )
    {
        try {
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::PRODUCT );
    
            if ($observer->getProduct()->getCreatedAt() === $observer->getProduct()->getUpdatedAt()) {
                $queue->setContent( Mage::getModel('jirafe_analytics/product')->getAddJson( $observer->getProduct() ) );
            } else {
                $queue->setContent( Mage::getModel('jirafe_analytics/product')->getModifyJson( $observer->getProduct() ) );
            }
    
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR productSave(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Capture product status change events
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function productStatusUpdate( Varien_Event_Observer $observer )
    {
        try {
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::PRODUCT );
            $queue->setContent( Mage::getModel('jirafe_analytics/product')->getStatusChangeJson( $observer->getProduct() ) );
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR productStatusUpdate(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Capture product delete events
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function productDelete( Varien_Event_Observer $observer )
    {
        try {
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::PRODUCT );
            $queue->setContent( Mage::getModel('jirafe_analytics/product')->getDeleteJson( $observer->getProduct() ) );
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR productSave(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    public function adminUserSave( Varien_Event_Observer $observer )
    {
        try {
            $user = $observer->getObject();
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::USER );
            if ($user->getModified() === $user->getCreated()) {
                $queue->setContent( Mage::getModel('jirafe_analytics/user')->getAddJson( $user ) );
            } else {
                $queue->setContent( Mage::getModel('jirafe_analytics/user')->getModifyJson( $user ) );
            }
            
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR adminUserSave(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    public function adminUserDelete( Varien_Event_Observer $observer )
    {
        try {
            $user = $observer->getObject();
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::USER );
            $queue->setContent( Mage::getModel('jirafe_analytics/user')->getDeleteJson( $user ) );
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR adminUserDelete(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    
    
    
   /* Check for Jirafe session id 
     * Create id and create new Jirafe session if doesn't exist
     *
     * @param  integer $customerId
     * @return boolean
    
    
    protected function _getSessionId( $customerId = null ) 
    {
        
        try {
           $jirafeSessionId = $this->_magentoSession->getJirafeSessionId();
           
           if (empty($jirafeSessionId)) {
           
                * Create new Jirafe session
                * 
                * HTTP Header/Vistor data
              
                $visitorData = $this->_magentoSession->getVisitorData();
                $jirafeSession = Mage::getModel('jirafe_analytics/session');
                $jirafeSession->setSessionKey($visitorData['session_id']);
                $jirafeSession->setIpAddress($visitorData['remote_addr']);
               
             
                 * Session store data
               
                $store = Mage::app()->getStore();
                $jirafeSession->setStoreId($store->getStoreId());
                $jirafeSession->setStoreCurrencyCode(implode($store->getAvailableCurrencyCodes()));
                
              
                 * Customer data
             
                if ($customerId) {
                  
                     * Use customerId created by customer registration
                   
                    $jirafeSession->setCustomerId($customerId);
                } else {
                
                     * Use customerId from LoggedIn user data in session
                 
                    $customerSession = Mage::getSingleton('customer/session');
                    if($customerSession->isLoggedIn()) {
                        $jirafeSession->setCustomerId($customerSession->getCustomer()->getId());
                    }
                }
                
                $jirafeSession->setCreatedDt($this->_getCreatedDt());
                $jirafeSession->save();
                $jirafeSessionId = $jirafeSession->getId();
                $this->_magentoSession->setJirafeSessionId($jirafeSessionId);
            } else {
            
                 * Add customer id to existing Jirafe session
             
                $jirafeSession = Mage::getModel('jirafe_analytics/session')->load($jirafeSessionId);
                if (!$jirafeSession->getCustomerId()) {
                    if ($customerId) {
                        $jirafeSession->setCustomerId($customerId);
                    } else {
                        $customerSession = Mage::getSingleton('customer/session');
                        if($customerSession->isLoggedIn()) {
                            $jirafeSession->setCustomerId($customerSession->getCustomer()->getId());
                        }
                    }
                    $jirafeSession->setModifiedDt($this->_getCreatedDt());
                    $jirafeSession->save();
                }
            }
            
            return $jirafeSessionId;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR _getSessionId(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
 
     * Check for cart for current quote
     * Create id and create new Jirafe cart if doesn't exist
     *
     * @param integer $_quoteId
     * @return string
    
    
    protected function _getCartId($_quoteId)
    {
        
        try {
            if (!$_quoteId = $this->_magentoSession->getJirafeQuoteId()) {
                $jirafeCart = Mage::getModel('jirafe_analytics/cart');
                $jirafeCart->setSessionId($this->_magentoSession->getJirafeSessionId());
                $jirafeCart->setQuoteId($_quoteId);
                $jirafeCart->save();
                $_cartId = $jirafeCart->getId();
                $this->_magentoSession->setJirafeCartId($_cartId);
                $this->_magentoSession->setJirafeQuoteId($_quoteId);
            }
            return $_cartId;
        } catch (Exception $e) {
            Mage::log("OBSERVER ERROR _getCartId($_quoteId): " . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
       
       
    }*/
}