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

    /**
     * Capture add product to cart  event
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    
    public function cartProductAdd(Varien_Event_Observer $observer)
    {
        $productId = $observer->getProduct()->getEntityId();
        $event = $observer->getQuote();
        $session = Mage::getSingleton('core/session');
        $sessionId = $session->getVisitorId();
        Mage::log('cartProductAdd',null,'observer.log');
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
}