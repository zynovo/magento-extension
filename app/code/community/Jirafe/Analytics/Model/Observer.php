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
     * Capture cart save event
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function cartSave( Varien_Event_Observer $observer )
    {   
        try {
            if ( Mage::getSingleton('core/session')->getJirafeProcessCart() ) {
                $quote = $observer->getCart()->getQuote();
                $json = Mage::getModel('jirafe_analytics/cart')->getJson( $quote );
                $queue = Mage::getModel('jirafe_analytics/queue');
                $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::CART );
                $queue->setContent( $json );
                $queue->setStoreId( $quote->getStoreId() );
                $queue->setCreatedDt( $this->_getCreatedDt() );
                $queue->save();
                Mage::getSingleton('core/session')->setJirafeProcessCart( false );
                return true;
            } else {
                return true;
            }
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Observer::cartProductAdd()', $e->getMessage());
            return false;
        }
        
    }
    
    /**
     * Capture update cart item event
     *
     * @return boolean
     */
    
    public function cartUpdateItem()
    {
        try {
            Mage::getSingleton('core/session')->setJirafeProcessCart( true );
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Observer::cartAddItem()', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Capture remove item cart event
     *
     * @return boolean
     */
    
    public function cartRemoveItem()
    {
        try {
            Mage::getSingleton('core/session')->setJirafeProcessCart( true );
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Observer::cartRemoveItem()', $e->getMessage());
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
            $queue->setContent( Mage::getModel('jirafe_analytics/category')->getJson( $observer->getCategory() ) );
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Observer::categorySave()', $e->getMessage());
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
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Observer::categoryDelete()', $e->getMessage());
            return false;
        }
    }
    
   
    /**
     * Capture customer save event
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function customerSave( Varien_Event_Observer $observer )
    {
        try {
            if ( Mage::getSingleton('core/session')->getJirafeProcessCustomer() ) {
                $queue = Mage::getModel('jirafe_analytics/queue');
                $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::CUSTOMER );
                $queue->setContent( Mage::getModel('jirafe_analytics/customer')->getJson( $observer->getCustomer() ) );
                $queue->setStoreId( $observer->getCustomer()->getStoreId() );
                $queue->setCreatedDt( $this->_getCreatedDt() );
                $queue->save();
                Mage::getSingleton('core/session')->setJirafeProcessCustomer( false );
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Observer::customerSave()', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Capture admin customer save event
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function adminCustomerSave( Varien_Event_Observer $observer )
    {
        try {
            Mage::getSingleton('core/session')->setJirafeProcessCustomer( true );
            $this->customerSave( $observer );
            return true;
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Observer::customerSave()', $e->getMessage());
            return false;
        }
    }
    
    
    /**
     * Capture customer load event
     *
     * @return boolean
     */
    
    public function customerLoad()
    {
        try {
            Mage::getSingleton('core/session')->setJirafeProcessCustomer( true );
            return true;
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Observer::customerLoad()', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Capture customer register event
     *
     * @return boolean
     */
    
    public function customerRegister()
    {
        try {
            Mage::getSingleton('core/session')->setJirafeProcessCustomer( true );
            return true;
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Observer::customerRegister()', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Capture order save event
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function orderSave( Varien_Event_Observer $observer )
    {
        try {
            $order = $observer->getOrder()->getData();
            if ($order['status'] == 'pending' || $order['status'] == 'cancelled' || $order['status'] == 'complete') {
                $order['payment'] = $observer->getOrder()->getPayment()->getData();
                $order['items'] = array();
                foreach($observer->getOrder()->getAllVisibleItems() as $item) {
                    $order['items'][] = $item->getData();
                }
                
                $queue = Mage::getModel('jirafe_analytics/queue');
                $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::ORDER );
                $queue->setContent( Mage::getModel('jirafe_analytics/order')->getJson( $order ) );
                $queue->setStoreId( $order['store_id'] );
                $queue->setCreatedDt( $this->_getCreatedDt() );
                $queue->save();
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Observer::orderPlaceAfter()', $e->getMessage());
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
            $order = $observer->getOrder()->getData();
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::ORDER );
            $queue->setContent( Mage::getModel('jirafe_analytics/order')->getJson( $order ) );
            $queue->setStoreId( $order['store_id'] );
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Observer::orderPlaceAfter()', $e->getMessage());
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
            $product = $observer->getProduct();
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::PRODUCT );
            $queue->setContent( Mage::getModel('jirafe_analytics/product')->getJson( $product->getEntityId() ) );
            $queue->setStoreId( $product->getStoreId() );
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Observer::productSave()', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Capture admin user add and modify events
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function employeeSave( Varien_Event_Observer $observer )
    {
        try {
            $userId = $observer->getObject()->getUserId();
            $queue = Mage::getModel('jirafe_analytics/queue');
            $queue->setTypeId( Jirafe_Analytics_Model_Queue_Type::EMPLOYEE );
            $queue->setContent( Mage::getModel('jirafe_analytics/employee')->getJson( $userId ) );
            $queue->setCreatedDt( $this->_getCreatedDt() );
            $queue->save();
            return true;
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Observer::employeeSave()', $e->getMessage());
            return false;
        }
    }
}