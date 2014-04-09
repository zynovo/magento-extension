<?php

/**
 * Adminhtml Log Controller
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Adminhtml_LogController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Jirafe Analytics: Log'));
        $this->loadLayout();
        $this->_setActiveMenu('reports/jirafe_analytics');
        $this->_addContent($this->getLayout()->createBlock('jirafe_analytics/adminhtml_log'));
        $this->renderLayout();

    }
    
    /**
     * Clear action
     * Truncate jirafe_analytics_log table
     * 
     * @return void
     */
    public function clearAction()
    {
        
        try {
            $_resourceModel = Mage::getResourceModel('jirafe_analytics/log');
            $_resourceModel->truncate();
            $this->_getSession()->addSuccess('Debug logs successfully deleted.');
        } catch (Exception $e) {
            $this->_getSession()->addError('Unable to delete debug logs.');
            Mage::log('ERROR Jirafe_Analytics_Adminhtml_LogController. Unable to delete debug logs.: ' . $e->getMessage(),null,'jirafe_analytics.log');
        }
        
        $this->_redirect('*/*/index');
    
    }
}