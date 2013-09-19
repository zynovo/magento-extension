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
     * @return mixed
     */
    
    public function cartProductAdd(Varien_Event_Observer $observer)
    {
        try {
            $this->_magentoSession = Mage::getSingleton('core/session');
            $product = $observer->getProduct();
            $item = Mage::getModel('jirafe_analytics/cart_item');
            $item->setCartId($this->_getCartId($observer));
            $item->setProductId($product->getEntityId());
            $item->setSku($product->getSku());
            $item->setQuantity($product->getCartQty());
            $item->setPrice($product->getPrice());
            $item->setSessionId($this->_getSessionId());
            $item->save();
        } catch (Exception $e) {
            Mage::log('OBSERVER ERROR cartProductAdd: ' . $e->getMessage(),null,'jirafe_analytics.log');
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
     * @return mixed
     */
    
    public function cartSaveAfter(Varien_Event_Observer $observer) 
    {
       // Mage::log('cartSaveAfter',null,'observer.log');
    }
    
    /**
     * Capture customer registration event
     * 
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function customerRegister(Varien_Event_Observer $observer) 
    {
       // Mage::log('customerRegister',null,'observer.log');
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
        Mage::log('categorySave',null,'observer.log');
    }
    
    /**
     * Capture event for changes in products associated with a category
     * 
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function categoryChangeProducts(Varien_Event_Observer $observer) 
    {
        Mage::log('categoryChangeProducts',null,'observer.log');
    }
    
    /**
     * Capture category delete event
     * 
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function categoryDelete(Varien_Event_Observer $observer) 
    {
        Mage::log('categoryDelete',null,'observer.log');
    }
    
    /**
     *Capture product save event 
     * 
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function productSave(Varien_Event_Observer $observer) 
    {
        Mage::log('productSave',null,'observer.log');
    }
    
    /**
     * Capture product status change events
     * 
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function productStatusUpdate(Varien_Event_Observer $observer) 
    {
    }
    
    /**
     * Capture product delete events
     * 
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function productDelete(Varien_Event_Observer $observer) 
    {
        Mage::log('productDelete',null,'observer.log');
    }
    
    /**
     * Capture order cancel events
     * 
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function orderCancel(Varien_Event_Observer $observer) 
    {
        Mage::log('orderCancel',null,'observer.log');
    }
    
    /**
     * Capture customer attribute delete events
     * 
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function customerAttributeDelete(Varien_Event_Observer $observer) 
    {
        Mage::log('customerAttributeDelete',null,'observer.log');
    }
    
    
    /**
     * Capture customer save events
     * 
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function customerAttributeSave(Varien_Event_Observer $observer) 
    {
        Mage::log('customerAttributeSave',null,'observer.log');
    }
    
    /**
     * Check for Jirafe session id 
     * Create id and insert session data into table if doesn't exist
     *
     * @return string
     */
    
    protected function _getSessionId() 
    {
        
        
       // if (!$_session_id = $this->_magentoSession->getJirafeSessionId()) {
            $visitorData = $this->_magentoSession->getVisitorData();
            $jirafeSession = Mage::getModel('jirafe_analytics/session');
            $jirafeSession->setSessionKey($visitorData['session_id']);
            $jirafeSession->setIpAddress($visitorData['remote_addr']);
            
            $store = Mage::app()->getStore();
            $jirafeSession->setStoreId($store->getStoreId());
            $jirafeSession->setStoreCurrencyCode(implode($store->getAvailableCurrencyCodes()));
            
            $customerSession = Mage::getSingleton('customer/session');
            
            if($customerSession->isLoggedIn()) {
                $jirafeSession->setCustomerId($customerSession->getCustomer()->getId());
            }
           
            $jirafeSession->save();
            $_session_id = $jirafeSession->getId();
            $this->_magentoSession->setJirafeSessionId($_session_id);
       // }
        
        return $_session_id;
    }
    
    /**
     * Check for Jirafe cart id 
     * Create id and insert cart data into table if doesn't exist
     *
     * @return string
     */
    
    protected function _getCartId($observer)
    {
        //if (!$_cartId = $this->_magentoSession->getJirafeCartId()) {
            $jirafeCart = Mage::getModel('jirafe_analytics/cart');
            $jirafeCart->setSessionId($this->_magentoSession->getJirafeSessionId());
            $jirafeCart->setQuoteId($observer->getQuoteItem()->getQuote()->getEntityId());
            
            $jirafeCart->save();
            $_cartId = $jirafeCart->getId();
            $this->_magentoSession->setJirafeCartId($_cartId);
        // }
        
        return $_cartId;
    }
}