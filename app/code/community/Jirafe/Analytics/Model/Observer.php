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
                    $json = Mage::getModel('jirafe_analytics/cart')->getJson( $quote, true );
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
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::cartProductAdd()', $e->getMessage(), $e);
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
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::cartAddItem()', $e->getMessage(), $e);
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
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::cartRemoveItem()', $e->getMessage(), $e);
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
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::categorySave()', $e->getMessage(), $e);
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
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::categoryDelete()', $e->getMessage(), $e);
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
                    $isVisit = Mage::getSingleton('core/session')->getJirafeIsVisit();
                    $data = Mage::getModel('jirafe_analytics/data');
                    $data->setTypeId( Jirafe_Analytics_Model_Data_Type::CUSTOMER );
                    $data->setJson( Mage::getModel('jirafe_analytics/customer')->getJson( $observer->getCustomer(), $isVisit ) );
                    $data->setStoreId( $observer->getCustomer()->getStoreId() );
                    $data->setCapturedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                    $data->save();
                    Mage::getSingleton('core/session')->setJirafeProcessCustomer( false );
                    Mage::getSingleton('core/session')->setJirafeIsVisit( false);
                    return true;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::customerSave()', $e->getMessage(), $e);
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
                Mage::getSingleton('core/session')->setJirafeIsVisit( false );
                $this->customerSave( $observer );
                return true;
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::customerSave()', $e->getMessage(), $e);
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
                Mage::getSingleton('core/session')->setJirafeIsVisit( true );
                return true;
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::customerLoad()', $e->getMessage(), $e);
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
                Mage::getSingleton('core/session')->setJirafeIsVisit( true );
                return true;
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::customerRegister()', $e->getMessage(), $e);
                return false;
            }
        }
    }
    
    /**
     * Capture order accepted event
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function orderAccepted( Varien_Event_Observer $observer )
    {
        if ( $this->_isEnabled ) {
            try {
                $mem1 = memory_get_usage();
                $order = $observer->getOrder();
                $mem2 = memory_get_usage() - $mem1;
                
                $start = memory_get_usage();
                $data = $order->getData();
                $mem3 = memory_get_usage() - $start;
                
                $start = memory_get_usage();
                $payment = $order->getPayment();
                $mem4 = memory_get_usage() - $start;
                
                $start = memory_get_usage();
                unset($order);
                gc_collect_cycles();
                $data['amount_paid'] = $payment->getAmountPaid();
                $data['amount_authorized'] = $payment->getAmountAuthorized();
                $mem5 = memory_get_usage() - $start;
                
                $start = memory_get_usage();
                unset($payment);
                gc_collect_cycles();
                $mem6 = memory_get_usage() - $start;
                
                $start = memory_get_usage();
                $data['jirafe_status'] = 'accepted';
                //Mage::log($data,null,'order_date.log');
                $this->_orderSave( $data );
                unset($data);
                gc_collect_cycles();
                $mem7 = memory_get_usage() - $start;
                
                
                $total = memory_get_usage() - $mem1;
                Mage::log("$mem1 > $mem2 > $mem3 > $mem4 > $mem5 > $mem6 > $mem7 = $total",null,'memory.log');
                return true;
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::orderAccepted()', $e->getMessage(), $e);
                return false;
            }
        }
    }
    
    /**
     * Capture order cancelled event
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    
    public function orderCancelled( Varien_Event_Observer $observer )
    {
        if ( $this->_isEnabled ) {
            try {
                $order = $observer->getOrder()->getData();
                $order['jirafe_status'] = 'cancelled';
                return $this->_orderSave( $order );
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::orderAccepted()', $e->getMessage(), $e);
                return false;
            }
        }
    }
    
    /**
     * Convert order to JSON and save
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $status
     * @return boolean
     */
    
    protected function _orderSave( $order = null )
    {
        if ( $this->_isEnabled && $order ) {
            try {
             Mage::log('BEGIN JIRAFE _orderSave',null,'events.log');
                $data = Mage::getModel('jirafe_analytics/data');
                $data->setTypeId( Jirafe_Analytics_Model_Data_Type::ORDER );
                $data->setJson( Mage::getModel('jirafe_analytics/order')->getJson( $order ) );
                $data->setStoreId( $order['store_id'] );
                $data->setCapturedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                $data->save();
                Mage::log('END JIRAFE _orderSave',null,'events.log');
                return true;
            } catch (Exception $e) {
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::_orderSave()', $e->getMessage(), $e);
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
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::orderPlaceAfter()', $e->getMessage(), $e);
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
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::productSave()', $e->getMessage(), $e);
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
                Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::employeeSave()', $e->getMessage(), $e);
                return false;
            }
        }
    }
}