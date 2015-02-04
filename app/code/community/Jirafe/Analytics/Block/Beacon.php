<?php

/**
 * Beacon Api Block
 *
 * Passes account and user data into the Beacon API javascript in the page head
 * 
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Block_Beacon extends Mage_Core_Block_Template
{
    protected $_beaconApiUrl = null;
    
    protected $_siteId = null;
    
    protected $_page = null;
    
    protected $_orderNumber = null;

    protected $_customer = null;
    
    protected $_enabled = false;

    /**
     * Class construction & variable initialization
     */
    
    public function __construct()
    {
        $this->_beaconApiUrl = Mage::getStoreConfig('jirafe_analytics/general/beacon_api');
        $this->_siteId = Mage::getStoreConfig('jirafe_analytics/general/site_id');
        $this->_enabled = Mage::getStoreConfig('jirafe_analytics/general/enabled');
        $this->_page = $this->_getPage();
        
        if ($this->_page == 'order_success') {
            $this->_orderNumber = Mage::getSingleton('core/session')->getJirafeOrderNumber();
            Mage::getSingleton('core/session')->setJirafeOrderNumber( null );
        }

        $this->_customer = $this->_getCustomer();
    }
    
    /**
     * Map Magneto page name to Beacon API value
     */
    
    protected function _getPage()
    {
        $request = $this->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        Mage::log("$module $controller $action",null,'page.log');
        switch ($module) {
            case 'cms':
                if ($controller === 'index') {
                    return 'homepage';
                } else {
                    return Mage::getSingleton('cms/page')->getIdentifier();
                }
                break;
            case 'catalog':
                /**
                 * returns 'product' or 'category' in default Magneto
                 */
                return $controller;
                break;
            case 'checkout':
                if ( $action == 'success' ) {
                    return 'order_success';
                } else if ( $controller == 'cart' ) {
                    return 'cart';
                } else {
                    return 'checkout';
                }
                break;
            case 'catalogsearch':
                return "search";
                break;
            default:
                return $module . '_' . $controller;
            break;
        }
    }

    protected function _getCustomer()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if (!$customer) return array();
        return array(
            "id" => $customer->getId(),
            "firstname" => $customer->getFirstname(),
            "lastname" => $customer->getLastname(),
            "email" => $customer->getEmail()
        );
    }

}
