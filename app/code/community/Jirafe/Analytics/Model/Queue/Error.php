<?php

/**
 * Queue Error Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Queue_Error extends Jirafe_Analytics_Model_Abstract
{
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/queue_error');
    }
    
    /**
     * Write response for each API queue error
     */
    public function add( $attempt = null, $queueAttemptId = null )
    {
        $this->setQueueId( $attempt['queue_id'] );
        $this->setQueueAttemptId( $queueAttemptId );
        $this->setResponse( $attempt['response'] );
        $this->setCreatedDt( $attempt['created_dt'] );
        $this->save();
    }
   
}