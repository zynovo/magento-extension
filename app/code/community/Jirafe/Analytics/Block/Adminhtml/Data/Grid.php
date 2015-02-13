<?php

/**
 * Adminhtml Data Block Grid
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Block_Adminhtml_Data_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('dataId');
        $this->setDefaultSort('created_dt');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return Jirafe_Analytics_Block_Adminhtml_Data_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('jirafe_analytics/data')->getCollection();
        $collection->getSelect()->join( array('t'=>Mage::getSingleton('core/resource')->getTableName('jirafe_analytics/data_type')), 'main_table.type_id = t.id', array('t.type'), array());
        
        $this->setCollection($collection);
        $collection->addFilterToMap('id', 'main_table.id');

        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepare columns
     *
     * @return Jirafe_Analytics_Block_Adminhtml_Data_Grid
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
            'type',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('TYPE'),
                'align'     =>'left',
                'index'     => 'type',
            )
        );
        
        $this->addColumn(
            'json',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('JSON'),
                'align'     => 'left',
                'width'     => '200px',
                'index'     => 'json',
            )
        );
        
        $this->addColumn(
            'captured_dt',
            array(
                'header'        => Mage::helper('jirafe_analytics')->__('CAPTURED (UTC)'),
                'align'         => 'left',
                'width'         => '150px',
                'type'          => 'datetime',
                'filter_index'  => 'main_table.captured_dt',
                'index'         => 'captured_dt'
            )
        );
        
        $this->addColumn(
            'attempt_count',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('ATTEMPTS'),
                'align'     =>'left',
                'width'     => '100px',
                'index'     => 'attempt_count',
            )
        );
        
        $this->addColumn(
            'success',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('SUCCESS'),
                'align'     =>'left',
                'index'     => 'success',
                'type' => 'options',
                'options' => array('0'=>'N','1'=>'Y'),
            )
        );
        
        $this->addColumn(
            'completed_dt',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('COMPLETED (UTC)'),
                'align'     => 'left',
                'width'     => '150px',
                'type'      => 'datetime',
                'default'   => '--',
                'index'     => 'completed_dt'
            )
        );
        
        return parent::_prepareColumns();
    }

}
