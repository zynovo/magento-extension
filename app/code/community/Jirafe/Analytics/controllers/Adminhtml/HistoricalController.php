<?php

/**
 * Adminhtml Attempt Controller
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Naqeeb Memon (naqeeb.memon@jirafe.com)
 */

class Jirafe_Analytics_Adminhtml_HistoricalController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Jirafe Analytics: Data Transfer Attempts'));
        $this->loadLayout()->_setActiveMenu('jirafe_analytics/attempt');
        $this->_addContent($this->getLayout()->createBlock('jirafe_analytics/adminhtml_attempt'));

        $this->renderLayout();
    }

    /**
     * Clear action
     * Truncate jirafe_analytics_log table
     *
     * @return void
     */
    public function checkAction()
    {
        // Check if the batch
        $website_code = $this->getRequest()->getParam('website_code');

        if ( $server = Mage::getModel('jirafe_analytics/curl')->getHistoricalPushStatus($website_code) ) {
            // Dispatch an event for the historical process if the site is ready
            $response = $server['response'];
            $json = json_decode($response);

            // No errors
            if ($json)
            {
                $status = $json->{'historical_status'};

                if($status == 'ready')
                {
                    $websiteId = Mage::app()->getWebsite($website_code)->getId();
                    Mage::helper('jirafe_analytics')->log('DEBUG', __METHOD__, 'Get Historical Job');
                    // Enable historical pull config setting.
                    Mage::helper('jirafe_analytics')->setHistoricalFlagOn($websiteId);
                    Mage::getModel('jirafe_analytics/curl')->updateHistoricalPushStatus($website_code, 'in-process');
                    Mage::helper('jirafe_analytics')->log(
                        'DEBUG',
                        __METHOD__,
                        'Historical Fetch Event dispatched');
                    Mage::getSingleton('core/session')->addSuccess('Jirafe is syncing your historical data.');
                }
                else if ($status == 'in-process')
                {
                    Mage::helper('jirafe_analytics')->log(
                        'ERROR',
                        __METHOD__,
                        'Historical Fetch is in process or not enabled for this site');
                    Mage::getSingleton('core/session')->addError('Historical Fetch is in process for this site.');
                }
                else if ($status == 'complete')
                {
                    Mage::helper('jirafe_analytics')->log(
                        'ERROR',
                        __METHOD__,
                        'Historical Fetch has already completed for this site');
                    Mage::getSingleton('core/session')->addError('Historical Fetch has already completed for this site.');
                }
                else
                {
                    Mage::helper('jirafe_analytics')->log(
                        'ERROR',
                        __METHOD__,
                        'Historical Fetch is not enabled for this site');
                    Mage::getSingleton('core/session')->addError('Historical Fetch is not enabled for this site. Please contact Jirafe support.');
                }
            }
        }
        // Redirect to the original website configuration screen
        $this->_redirectUrl(($this->_getRefererUrl()));
    }
}

