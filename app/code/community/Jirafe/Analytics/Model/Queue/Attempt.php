<?php

/**
 * Queue Attempt Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Queue_Attempt extends Jirafe_Analytics_Model_Abstract
{
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/queue_attempt');
    }
    
    /**
     * Write response for each API queue attempt to jirafe_analytics_queue_attempt table
     * If successful, update completed_dt in jirafe_analytics_queue
     */
    public function record( $queue_id = null, $response = null)
    {
        $currentDt = $this->_getCreatedDt();
        $this->setQueueId( $queue_id );
        $this->setHttpCode( $response['http_code'] );
        $this->setTotalTime( $response['total_time'] );
        $this->setCreatedDt( $currentDt );
        $this->save();
        
        if ($response['http_code'] == '200') {
            $queue = Mage::getModel('jirafe_analytics/queue')->load( $queue_id );
            $queue->setCompletedDt( $currentDt );
            $queue->save();
        }
    }
   
}