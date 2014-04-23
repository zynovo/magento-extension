<?php

/**
 * Jirafe Status Controller
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 */

class Jirafe_Analytics_Adminhtml_StatusController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Jirafe Analytics Status'));
        $this->loadLayout();
        $this->_setActiveMenu('system');
        $this->_addBreadcrumb($this->__('Jirafe Analytics Status'), $this->__('Jirafe Analytics Status'));
        $this->_addContent($this->getLayout()->createBlock('jirafe_analytics/adminhtml_status'));
        $this->renderLayout();
    }
}