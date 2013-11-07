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

class Request extends Zend_Controller_Request_Abstract {}

class Response extends Zend_Controller_Response_Abstract {}

require_once 'Mage/Oauth/controllers/Adminhtml/Oauth/ConsumerController.php';

class Jirafe_Analytics_Model_Install_Oauth_Customer extends Mage_Oauth_Adminhtml_Oauth_ConsumerController
{
    /**
     * Override parent constructor to mimic controller behavior
     */
    public function __construct()
    {
        $request = new Request;
        $response = new Response;
        parent::__construct ($request, $response, array());
    }
    
    /**
     * Create admin permissions role
     * 
     * @param string $name
     */
    public function create( $name = null )
    {
        try {
            $customer = Mage::getModel('oauth/consumer')
                ->getCollection()
                ->addFieldToFilter('name',array('eq',$name))
                ->getFirstItem();
            Zend_Debug::dump($customer);
            if (!$customer->getId()) {
                $customer = Mage::getModel('oauth/consumer');
                $customer->setName($name);
                $customer->setKey(Mage::helper('oauth')->generateConsumerKey());
                $customer->setSecret(Mage::helper('oauth')->generateConsumerSecret());
                $customer->save();
            }
            
            return $customer->getId();
        } catch (Exception $e) {
            Mage::throwException('ADMIN ROLE ERROR: Jirafe_Analytics_Model_Install_Oauth_Customer::create(): ' . $e->getMessage());
        }

    }
    
    
}
