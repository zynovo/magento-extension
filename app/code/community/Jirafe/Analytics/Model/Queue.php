<?php

/**
 * Queue Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Queue extends Jirafe_Analytics_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('jirafe_analytics/queue');
    }
    
    public function process() 
    {
        $queue = $this->_getQueue();
        foreach($queue->query() as $item) {
            $response = Mage::getModel('jirafe_analytics/api')->send( $item['type'], $item['content'] );
            $attempt = Mage::getModel('jirafe_analytics/queue_attempt');
            $attempt->setQueueId($item['id']);
            $attempt->setResponse($response);
            $obj = json_decode($response);
            $success = $obj->success ? 1 : 0;
            $attempt->setStatusId($success);
            $attempt->setCreatedDt($this->_getCreatedDt());
            $attempt->save();
            
            if ($success) {
                $current = $this->load($item['id']);
                $current->setCompletedDt($this->_getCreatedDt());
                $current->save();
            }

        }
       // $attempt = Mage::getModel('jirafe_analytics/queue_attempt');
       // $api = Mage::getModel('jirafe_analytics/api');
    }
    
    protected function _getQueue() 
    {
        return $this->getCollection()
            ->addFieldToSelect(array('content'))
            ->addFieldToFilter('`main_table`.`completed_dt`', array('is' => new Zend_Db_Expr('null')))
            ->addFieldToFilter('`main_table`.`content`', array('neq' => ''))
            ->getSelect()
            ->join( array('qt'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/queue_type')), '`main_table`.`type_id` = `qt`.`id`', array('qt.description as type'))
            ->order(array('main_table.created_dt ASC'));
    }
}