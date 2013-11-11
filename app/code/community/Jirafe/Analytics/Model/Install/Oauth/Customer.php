<?php

/**
 * Install Oauth Customer Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 * 
 * 
 */

require_once 'Mage/Oauth/controllers/Adminhtml/Oauth/ConsumerController.php';

class Jirafe_Analytics_Model_Install_Oauth_Customer extends Mage_Oauth_Adminhtml_Oauth_ConsumerController
{
    /**
     * Override parent constructor to mimic controller behavior
     */
    public function __construct()
    {
        $request = Mage::getSingleton('jirafe_analytics/install_request');
        $response = Mage::getSingleton('jirafe_analytics/install_response');
        parent::__construct ($request, $response, array());
    }
    
    /**
     * Create admin permissions role
     * 
     * @param string $name
     */
    public function getSecret()
    {
        try {
            
            $name = Mage::getStoreConfig('jirafe_analytics/installer/oauth_customer');
            
            $customer = Mage::getModel('oauth/consumer')
                ->getCollection()
                ->addFieldToFilter('name',array('eq',$name))
                ->getFirstItem();
            
            if (!$customer->getId()) {
                $customer = Mage::getModel('oauth/consumer');
                $customer->setName($name);
                $customer->setKey(Mage::helper('oauth')->generateConsumerKey());
                $customer->setSecret(Mage::helper('oauth')->generateConsumerSecret());
                $customer->save();
            }
            
            return $customer->getSecret();
        } catch (Exception $e) {
            Mage::throwException('ADMIN ROLE ERROR: Jirafe_Analytics_Model_Install_Oauth_Customer::create(): ' . $e->getMessage());
        }

    }
    
    
}
