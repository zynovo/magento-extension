<?php

/**
 * Jirafe Config Controller
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 */

class Jirafe_Analytics_Adminhtml_System_ConfigController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $_groups = $this->getRequest()->getPost('groups');
        $result = '';
        if (isset($_groups['general']) && isset($_groups['general']['fields'])) {
            $_fields = $_groups['general']['fields'];
            if ($_fields['enabled']['value']) {
                $website    = Mage::app()->getWebsite($this->getRequest()->getParam('website'));
                //if enable checked credentials
                $response = Mage::getModel('jirafe_analytics/curl')->checkCredentials(
                    $website->getId(), $_fields['site_id']['value'], $_fields['access_token']['value']);
                if (!$response) {
                    $result = array(
                        'error_message' => $this->__('The credentials you have supplied are not valid. Please check and try again.')
                    );
                }
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}