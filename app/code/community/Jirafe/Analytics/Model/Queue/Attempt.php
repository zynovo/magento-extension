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
     * Store data for each API queue attempt 
     * If error, add data to queue error table
     */
    public function add( $attempt = null )
    {
        $this->setQueueId( $attempt['queue_id'] );
        $this->setHttpCode( $attempt['http_code'] );
        $this->setTotalTime( $attempt['total_time'] );
        $this->setCreatedDt( $attempt['created_dt'] );
        $this->save();
        
        if ($attempt['http_code'] != '200') {
            Mage::getModel('jirafe_analytics/queue_error')->add( $attempt, $this->getId() );
        }
        
        
    }
   
}