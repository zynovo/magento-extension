<?php

/**
 * Map Controller
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_MapController extends Mage_Core_Controller_Front_Action
{
    /**
    * index action
    */
    public function indexAction() {

        $server = new Jirafe_Analytics_Model_Webservice_Server_Json();
        $server->setClass('Jirafe_Analytics_Model_Webservice_Handler_Rest');
        $server->handle();
        exit;
    }
}
