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

class Jirafe_Analytics_Block_Beaconapi extends Mage_Core_Block_Template
{
    protected $_beaconApiUrl = null;
    protected $_siteId = null;
    protected $_orgId = null;
    
    public function __construct()
    {
        $this->_beaconApiUrl = Mage::getStoreConfig('jirafe_analytics/account/beacon_api') ;
        $this->_siteId = Mage::getStoreConfig('jirafe_analytics/account/site_id') ;
        $this->_orgId = Mage::getStoreConfig('jirafe_analytics/account/org_id');
        
    }
    
    protected function _getPage()
    {
        $request = $this->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        
        switch ($module) {
            case 'cms':
                if ($controller === 'index') {
                    return 'homepage';
                } else {
                    return Mage::getSingleton('cms/page')->getIdentifier();
                }
                break;
            case 'catalog':
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
                return "$module_$controller";
            break;
        }
    }
}