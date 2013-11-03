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

    protected $_isEnabled = false;
    
    /**
     * Class construction & variable initialization
     */
    
    protected function _construct()
    {
        $this->_isEnabled = Mage::getStoreConfig('jirafe_analytics/general/enabled');
    }
    
    /**
     * Capture cart save event
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function cartSave( Varien_Event_Observer $observer )
    {   
        if ( $this->_isEnabled ) {
            try {
                if ( Mage::getSingleton('core/session')->getJirafeProcessCart() ) {
                    $quote = $observer->getCart()->getQuote();
                    $json = Mage::getModel('jirafe_analytics/cart')->getJson( $quote );
                    $data = Mage::getModel('jirafe_analytics/data');
                    $data->setTypeId( Jirafe_Analytics_Model_Data_Type::CART );
                    $data->setJson( $json );
                    $data->setStoreId( $quote->getStoreId() );
                    $data->setCapturedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                    $data->save();;
                    Mage::getSingleton('core/session')->setJirafeProcessCart( false );
                    return true;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::cartProductAdd()', $e->getMessage());
                return false;
            }
        }
    }
    
    /**
     * Capture update cart item event
     *
     * @return boolean
     */
    
    public function cartUpdateItem()
    {
        if ( $this->_isEnabled ) {
            try {
                Mage::getSingleton('core/session')->setJirafeProcessCart( true );
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::cartAddItem()', $e->getMessage());
                return false;
            }
        }
    }
    
    /**
     * Capture remove item cart event
     *
     * @return boolean
     */
    
    public function cartRemoveItem()
    {
        if ( $this->_isEnabled ) {
            try {
                Mage::getSingleton('core/session')->setJirafeProcessCart( true );
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::cartRemoveItem()', $e->getMessage());
                return false;
            }
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
        if ( $this->_isEnabled ) {
            try {
                $data = Mage::getModel('jirafe_analytics/data');
                $data->setTypeId( Jirafe_Analytics_Model_Data_Type::CATEGORY );
                $data->setJson( Mage::getModel('jirafe_analytics/category')->getJson( $observer->getCategory() ) );
                $data->setCapturedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                $data->save();
                return true;
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::categorySave()', $e->getMessage());
                return false;
            }
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
        if ( $this->_isEnabled ) {
            try {
                $data = Mage::getModel('jirafe_analytics/data');
                $data->setTypeId( Jirafe_Analytics_Model_Data_Type::CATEGORY );
                $data->setJson( Mage::getModel('jirafe_analytics/category')->getDeleteJson( $observer->getCategory() ) );
                $data->setCapturedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                $data->save();
                return true;
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::categoryDelete()', $e->getMessage());
                return false;
            }
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
        if ( $this->_isEnabled ) {
            try {
                if ( Mage::getSingleton('core/session')->getJirafeProcessCustomer() ) {
                    $data = Mage::getModel('jirafe_analytics/data');
                    $data->setTypeId( Jirafe_Analytics_Model_Data_Type::CUSTOMER );
                    $data->setJson( Mage::getModel('jirafe_analytics/customer')->getJson( $observer->getCustomer() ) );
                    $data->setStoreId( $observer->getCustomer()->getStoreId() );
                    $data->setCapturedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                    $data->save();
                    Mage::getSingleton('core/session')->setJirafeProcessCustomer( false );
                    return true;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::customerSave()', $e->getMessage());
                return false;
            }
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
        if ( $this->_isEnabled ) {
            try {
                Mage::getSingleton('core/session')->setJirafeProcessCustomer( true );
                $this->customerSave( $observer );
                return true;
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::customerSave()', $e->getMessage());
                return false;
            }
        }
    }
    
    
    /**
     * Capture customer load event
     *
     * @return boolean
     */
    
    public function customerLoad()
    {
        if ( $this->_isEnabled ) {
            try {
                Mage::getSingleton('core/session')->setJirafeProcessCustomer( true );
                return true;
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::customerLoad()', $e->getMessage());
                return false;
            }
        }
    }
    
    /**
     * Capture customer register event
     *
     * @return boolean
     */
    
    public function customerRegister()
    {
        if ( $this->_isEnabled ) {
            try {
                Mage::getSingleton('core/session')->setJirafeProcessCustomer( true );
                return true;
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::customerRegister()', $e->getMessage());
                return false;
            }
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
        if ( $this->_isEnabled ) {
            try {
                $order = $observer->getOrder()->getData();
                if ($order['status'] == 'pending' || $order['status'] == 'cancelled') {
                    $data = Mage::getModel('jirafe_analytics/data');
                    $data->setTypeId( Jirafe_Analytics_Model_Data_Type::ORDER );
                    $data->setJson( Mage::getModel('jirafe_analytics/order')->getJson( $order ) );
                    $data->setStoreId( $order['store_id'] );
                    $data->setCapturedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                    $data->save();
                    return true;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::orderPlaceAfter()', $e->getMessage());
                return false;
            }
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
        if ( $this->_isEnabled ) {
            try {
                $order = $observer->getOrder()->getData();
                $data = Mage::getModel('jirafe_analytics/data');
                $data->setTypeId( Jirafe_Analytics_Model_Data_Type::ORDER );
                $data->setJson( Mage::getModel('jirafe_analytics/order')->getJson( $order ) );
                $data->setStoreId( $order['store_id'] );
                $data->setCapturedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                $data->save();
                return true;
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::orderPlaceAfter()', $e->getMessage());
                return false;
            }
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
        if ( $this->_isEnabled ) {
            try {
                $product = $observer->getProduct();
                $data = Mage::getModel('jirafe_analytics/data');
                $data->setTypeId( Jirafe_Analytics_Model_Data_Type::PRODUCT );
                $data->setJson( Mage::getModel('jirafe_analytics/product')->getJson( $product->getEntityId() ) );
                $data->setStoreId( $product->getStoreId() );
                $data->setCapturedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                $data->save();
                return true;
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::productSave()', $e->getMessage());
                return false;
            }
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
        if ( $this->_isEnabled ) {
            try {
                $userId = $observer->getObject()->getUserId();
                $data = Mage::getModel('jirafe_analytics/data');
                $data->setTypeId( Jirafe_Analytics_Model_Data_Type::EMPLOYEE );
                $data->setJson( Mage::getModel('jirafe_analytics/employee')->getJson( null, $userId ) );
                $data->setCapturedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                $data->save();
                return true;
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::employeeSave()', $e->getMessage());
                return false;
            }
        }
    }
}