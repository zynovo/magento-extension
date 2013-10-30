<?php

/**
 * Adminhtml Data Controller
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Adminhtml_DataController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Jirafe Analytics: Data Captured By Observers'));
        $this->loadLayout();
        $this->_setActiveMenu('reports/jirafe_analytics');
        $this->_addContent($this->getLayout()->createBlock('jirafe_analytics/adminhtml_data'));
        $this->renderLayout();

    }
}