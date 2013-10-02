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

    public $debug = false;
    
    public $batchSize = null;
    
    public $maxExecutionTime = null;
    
    public $memoryLimit = null;
    
    public $procNice = null;
    
    public $pos = null;
    
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/queue');
    }
    
    public function process() 
    {
        $data = $this->getCollection()
            ->addFieldToSelect(array('content'))
            ->addFieldToFilter('`main_table`.`completed_dt`', array('is' => new Zend_Db_Expr('null')))
            ->addFieldToFilter('`main_table`.`content`', array('neq' => ''))
            ->getSelect()
            ->join( array('qt'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/queue_type')), '`main_table`.`type_id` = `qt`.`id`', array('qt.description as type'))
            ->order(array('main_table.created_dt ASC'))
            ->query();
          
        $response = Mage::getModel('jirafe_analytics/api')->send( $data);
    
    }
    
}