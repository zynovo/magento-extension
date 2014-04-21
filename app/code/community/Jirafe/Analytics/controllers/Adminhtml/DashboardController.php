<?php

/**
 * Adminhtml Dashboard Controller
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2014 Jirafe, Inc. (http://jirafe.com/)
 */

class Jirafe_Analytics_Adminhtml_DashboardController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Jirafe Analytics: Dashboard'));
        $this->loadLayout();
        $this->_setActiveMenu('dashboard/jirafe_analytics');
        $this->_addContent($this->getLayout()->createBlock('jirafe_analytics/adminhtml_dashboard'));
        $this->renderLayout();

    }
}
