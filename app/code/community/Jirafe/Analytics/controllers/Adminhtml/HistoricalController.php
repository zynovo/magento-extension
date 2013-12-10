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

        if ( $server = Mage::getModel('jirafe_analytics/curl')->checkHistoricalPush() ) {
            // Dispatch an event for the historical process if the site is ready
            $response = $server['response'];
            $json = json_decode($response);

            // No errors
            if ($json)
            {
                $status = $json->{'historical_status'};

                if($status == 'ready'){
                {
                    $result = Mage::dispatchEvent('jirafe_historical_retrieval');
                    Mage::helper('jirafe_analytics')->log('DEBUG', 'Jirafe_Analytics_Adminhtml_HistoricalController::checkAction()', 'Historical Fetch Event dispatched', null);
                }
                else
                {
                    $format = 'The historical push cannot  %d monkeys in the %s';
                    $message = sprintf($format, $status);
                    Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Adminhtml_HistoricalController::checkAction()', $message, null);
                }
            }
        }

    }


}
