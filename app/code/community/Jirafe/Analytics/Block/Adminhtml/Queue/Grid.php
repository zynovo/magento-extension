<?php

/**
 * Adminhtml Queue Block Grid
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Block_Adminhtml_Queue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('queueId');
        $this->setDefaultSort('created_dt');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return Jirafe_Analytics_Block_Adminhtml_Queue_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('jirafe_analytics/queue')->getCollection();

        $collection->getSelect()->join( array('t'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/queue_type')), 'main_table.type_id = t.id', array('t.description'), array());
        $this->setCollection($collection);
        $collection->addFilterToMap('id', 'main_table.id');
        
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepare columns
     *
     * @return Jirafe_Analytics_Block_Adminhtml_Queue_Grid
     */
    protected function _prepareColumns()
    {
        
        $this->addColumn(
            'id',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('ID'),
                'align'     =>'left',
                'index'     => 'id',
            )
        );
        
        $this->addColumn(
            'description',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('TYPE'),
                'align'     =>'left',
                'index'     => 'description',
            )
        );
        
        $this->addColumn(
            'content',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('JSON'),
                'align'     => 'left',
                'width'     => '200px',
                'index'     => 'content',
            )
        );
        
        $this->addColumn(
            'created_dt',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('CAPTURED'),
                'align'     => 'left',
                'type'      => 'datetime',
                'index'     => 'created_dt',
                'format'    => Mage::app()->getLocale()->getDateFormat()
            )
        );
        
        $this->addColumn(
            'attempt_count',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('ATTEMPTS'),
                'align'     =>'left',
                'index'     => 'attempt_count',
            )
        );
        
        $this->addColumn(
            'success',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('TRANSFERED'),
                'align'     =>'left',
                'index'     => 'success',
                'type' => 'options',
                'options' => array('0'=>'N','1'=>'Y'),
            )
        );
        
        $this->addColumn(
            'completed_dt',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('COMPLETE'),
                'align'     => 'left',
                'type'      => 'datetime',
                'default'   => '--',
                'index'     => 'completed_dt',
                'format'    => Mage::app()->getLocale()->getDateFormat()
            )
        );
        
        return parent::_prepareColumns();
    }

}
