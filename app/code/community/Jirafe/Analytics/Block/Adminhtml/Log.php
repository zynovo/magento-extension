<?php

/**
 * Adminhtml Log Block
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_blockGroup = 'jirafe_analytics';
        $this->_controller = 'adminhtml_log';
        $this->_headerText = Mage::helper('jirafe_analytics')->__('Jirafe Analytics: Log');
        parent::__construct();
        
        $this->removeButton('add');
        $this->_addButton('clear', array(
            'label'     => Mage::helper('jirafe_analytics')->__('Delete Log'),
            'onclick' => 'deleteConfirm(\''. $this->jsQuoteEscape(
                    Mage::helper('jirafe_analytics')->__('Are you sure you want to delete the log?')
            ) . '\', \'' . $this->getUrl('jirafe_analytics/adminhtml_log/clear') . '\' )',
        ));
    }

}
