<?php

/**
 * Adminhtml Attempt Block Grid
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Block_Adminhtml_Attempt_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('attemptId');
        $this->setDefaultSort('created_dt');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * Prepare collection
     *
     * @return Jirafe_Analytics_Block_Adminhtml_Attempt_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('jirafe_analytics/data_attempt')->getCollection();
        $collection->getSelect()->join( array('d'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/data')), 'main_table.data_id = d.id', array('d.json'), array());
        $collection->getSelect()->joinLeft( array('e'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/data_error')), 'main_table.id = e.data_attempt_id', array('e.error_type','e.errors'), array());
        $this->setCollection($collection);
        $collection->addFilterToMap('id', 'main_table.id');
        $collection->addFilterToMap('created_dt', 'main_table.created_dt');
        parent::_prepareCollection();
        return $this;
    }
    
    /**
     * Prepare columns
     *
     * @return Jirafe_Analytics_Block_Adminhtml_Attempt_Grid
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
            'json',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('JSON'),
                'index'     => 'json',
            )
        );
        $this->addColumn(
            'error_type',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('ERROR TYPE'),
                'index'     => 'error_type',
            )
        );
        $this->addColumn(
          'errors',
          array(
            'header'    => Mage::helper('jirafe_analytics')->__('ERRORS'),
            'index'     => 'errors',
          )
        );
        $this->addColumn(
            'created_dt',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('ATTEMPTED (UTC)'),
                'align'     => 'left',
                'width'     => '150px',
                'type'      => 'datetime',
                'index'     => 'created_dt'
            )
        );
        
        return parent::_prepareColumns();
    }
}