<?php

/**
 * Adminhtml Log Block Grid
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('created_dt');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return Jirafe_Analytics_Block_Adminhtml_Log_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('jirafe_analytics/log')->getCollection();
        $this->setCollection($collection);
        
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepare columns
     *
     * @return Jirafe_Analytics_Block_Adminhtml_Log_Grid
     */
    protected function _prepareColumns()
    {
        
        $this->addColumn(
            'created_dt',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('CREATED'),
                'align'     => 'left',
                'type'      => 'datetime',
                'index'     => 'created_dt',
                'width'     => '125px',
            )
        );
        
        $this->addColumn(
            'type',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('TYPE'),
                'align'     =>'left',
                'index'     => 'type',
                'width'     => '65px',
            )
        );
        
        $this->addColumn(
            'location',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('LOCATION'),
                'align'     =>'left',
                'index'     => 'location',
                'width'     => '200px',
            )
        );
        $this->addColumn(
            'message',
            array(
                'header'    => Mage::helper('jirafe_analytics')->__('MESSAGE'),
                'align'     =>'left',
                'index'     => 'message',
            )
        );
        
       
       
        return parent::_prepareColumns();
    }

}
