<?php

/**
 * Queue Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Queue extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('jirafe_analytics/queue');
    }
    
    public function process() 
    {
        $this->_processQueue(Mage::getModel('jirafe_analytics/cart'),'cart');
        $this->_processQueue(Mage::getModel('jirafe_analytics/category'),'category');
        $this->_processQueue(Mage::getModel('jirafe_analytics/customer'),'customer');
        $this->_processQueue(Mage::getModel('jirafe_analytics/order'),'order');
        $this->_processQueue(Mage::getModel('jirafe_analytics/product'),'product');
    }
    
    protected function _processQueue( $queue, $type ) 
    {
        $result = $queue->processQueue();
        $queue = Mage::getModel('jirafe_analytics/queue');
        $queue->setType($type);
        $queue->setSuccess($result->getSuccess());
        $queue->setResponse($result->getResponse());
        
    }
    
   

}