<?php

/**
 * Cart Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Job extends Jowens_JobQueue_Model_Job_Abstract
{
    protected function _construct()
    {
        $this->_init('jirafe_analytics/job');
    }

    public function perform()
    {
        try
        {
            // Historial
            $data = array(
                'max_execution_time' => '1800',
                'memory_limit' => '2048M',
                'proc_nice' => '16'
            );
                //'element' => 'order'

            // Convert
            if ( Mage::getModel('jirafe_analytics/data')->convertHistoricalData( $data ) ) {
                Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Job::perform()', 'Finished converting the historical data. Now batching the events.', null );

                // Batch
                if (Mage::getModel('jirafe_analytics/data')->convertEventDataToBatchData( $data, true ) ) {
                    Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Job::perform()', 'Finished batching the historical data. Now preparing to send the events.', null );

                    // Export to Jirafe event-api
                    if ( Mage::getModel('jirafe_analytics/batch')->process( $data, true ) ) {
                        Mage::helper('jirafe_analytics')->log( 'DEBUG', 'Jirafe_Analytics_Model_Job::perform()', 'Finished sending the events.  Process Complete.', null );

                    }
                }
            }
        }
        catch (Exception $e)
        {
             Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Observer::retrieveHistoricalEvents()', $e->getMessage(), $e);
        }
    }
}

