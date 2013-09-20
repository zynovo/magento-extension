<?php

/**
 * Event Observer Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Observer
{

    
    protected $_magentoSession = null;
    
    /**
     * Capture add product to cart event
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function cartProductAdd(Varien_Event_Observer $observer)
    {
        try {
            $this->_magentoSession = Mage::getSingleton('core/session');
            $product = $observer->getProduct();
            $item = Mage::getModel('jirafe_analytics/cart_item');
            $item->setCartId($this->_getCartId($observer->getQuoteItem()->getQuote()->getEntityId()));
            $item->setProductId($product->getEntityId());
            $item->setSku($product->getSku());
            $item->setQuantity($product->getCartQty());
            $item->setPrice($product->getPrice());
            $item->setSessionId($this->_getSessionId());
            $item->setStatusId(Jirafe_Analytics_Model_Status::ADD);
            $item->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR cartProductAdd(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
        
    }
    
    
    /**
     * Capture cart update event
     * 
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function cartUpdateItemComplete(Varien_Event_Observer $observer) 
    {
       // Mage::log('cartUpdateItemComplete',null,'observer.log');
    }
    
    /**
     * Capture cart save event
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function cartSaveAfter(Varien_Event_Observer $observer) 
    {
       // Mage::log('cartSaveAfter',null,'observer.log');
    }
    
    /**
     * Capture customer registration event
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function customerRegister(Varien_Event_Observer $observer) 
    {
        try {
            $this->_magentoSession = Mage::getSingleton('core/session');
            $customer = Mage::getModel('jirafe_analytics/customer');
            $customerId = $observer->getCustomer()->getEntityId();
            $customer->setSessionId($this->_getSessionId($customerId));
            $customer->setEntityId( $customerId);
            $customer->setFirstName( $observer->getCustomer()->getFirstname());
            $customer->setLastName( $observer->getCustomer()->getLastname());
            $customer->setEmail( $observer->getCustomer()->getEmail());
            $customer->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR customerRegister(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    
    /**
     * Capture order save event
     * 
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function orderPlaceAfter(Varien_Event_Observer $observer) 
    {
        Mage::log('orderPlaceAfter',null,'observer.log');
    }
    
    
    /**
     * Capture category save event
     * 
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function categorySave(Varien_Event_Observer $observer) 
    {
         try {
            $category = Mage::getModel('jirafe_analytics/category');
            $category->setEntityId($observer->getCategory()->getEntityId());
            $category->setAdminUserId(Mage::getSingleton('admin/session')->getUser()->getUserId());
            if ($observer->getCategory()->getCreatedAt() === $observer->getCategory()->getUpdatedAt()) {
                $category->setStatusId(Jirafe_Analytics_Model_Status::ADD);
            } else {
                $category->setStatusId(Jirafe_Analytics_Model_Status::MODIFY);
            }
            $category->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR categoryDelete(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Capture event for changes in products associated with a category
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function categoryChangeProducts(Varien_Event_Observer $observer) 
    {
        Mage::log('categoryChangeProducts',null,'observer.log');
    }
    
    /**
     * Capture category delete event
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function categoryDelete(Varien_Event_Observer $observer) 
    {
        try {
            $category = Mage::getModel('jirafe_analytics/category');
            $category->setEntityId($observer->getCategory()->getEntityId());
            $category->setAdminUserId(Mage::getSingleton('admin/session')->getUser()->getUserId());
            $category->setStatusId(Jirafe_Analytics_Model_Status::DELETE);
            $category->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR categoryDelete(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     *Capture product save event 
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function productSave(Varien_Event_Observer $observer) 
    {
        try {
            $product = Mage::getModel('jirafe_analytics/product');
            $product->setEntityId($observer->getProduct()->getId());
            $product->setAdminUserId(Mage::getSingleton('admin/session')->getUser()->getUserId());
            if ($observer->getProduct()->getCreatedAt() === $observer->getProduct()->getUpdatedAt()) {
                $product->setStatusId(Jirafe_Analytics_Model_Status::ADD);
            } else {
                $product->setStatusId(Jirafe_Analytics_Model_Status::MODIFY);
            }
            $product->save();
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
    
    public function productStatusUpdate(Varien_Event_Observer $observer) 
    {
        try {
            $product = Mage::getModel('jirafe_analytics/product');
            $product->setEntityId($observer->getProduct()->getId());
            $product->setAdminUserId(Mage::getSingleton('admin/session')->getUser()->getUserId());
            $product->setStatusId(Jirafe_Analytics_Model_Status::DELETE);
            $product->save();
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
    
    public function productDelete(Varien_Event_Observer $observer) 
    {
        try {
            $product = Mage::getModel('jirafe_analytics/product');
            $product->setEntityId($observer->getProduct()->getId());
            $product->setAdminUserId(Mage::getSingleton('admin/session')->getUser()->getUserId());
            $product->setStatusId(Jirafe_Analytics_Model_Status::DELETE);
            $product->save();
            return true;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR productDelete(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Capture order cancel events
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function orderCancel(Varien_Event_Observer $observer) 
    {
        Mage::log('orderCancel',null,'observer.log');
    }
    
    /**
     * Capture customer attribute delete events
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function customerAttributeDelete(Varien_Event_Observer $observer) 
    {
        Mage::log('customerAttributeDelete',null,'observer.log');
    }
    
    
    /**
     * Capture customer save events
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function customerAttributeSave(Varien_Event_Observer $observer) 
    {
        Mage::log('customerAttributeSave',null,'observer.log');
    }
    
    /**
     * Check for Jirafe session id 
     * Create id and create new Jirafe session if doesn't exist
     *
     * @return boolean
     */
    
    protected function _getSessionId( $customerId = null ) 
    {
        
        try {
           $jirafeSessionId = $this->_magentoSession->getJirafeSessionId();
           if (empty($jirafeSessionId)) {
                $visitorData = $this->_magentoSession->getVisitorData();
                $jirafeSession = Mage::getModel('jirafe_analytics/session');
                $jirafeSession->setSessionKey($visitorData['session_id']);
                $jirafeSession->setIpAddress($visitorData['remote_addr']);
                if ($customerId) {
                    $jirafeSession->setCustomerId($customerId);
                }
                $store = Mage::app()->getStore();
                $jirafeSession->setStoreId($store->getStoreId());
                $jirafeSession->setStoreCurrencyCode(implode($store->getAvailableCurrencyCodes()));
                
                $customerSession = Mage::getSingleton('customer/session');
                
                if($customerSession->isLoggedIn()) {
                    $jirafeSession->setCustomerId($customerSession->getCustomer()->getId());
                }
               
                $jirafeSession->save();
                $jirafeSessionId = $jirafeSession->getId();
                $this->_magentoSession->setJirafeSessionId($jirafeSessionId);
            } else {
                /*
                 * UPDATE EXISTING SESSION 
                 */
            }
            
            return $jirafeSessionId;
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR _getSessionId(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Check for cart for current quote
     * Create id and create new Jirafe cart if doesn't exist
     *
     * @param string $_quoteId
     * @return string
     */
    
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
       
       
    }
}