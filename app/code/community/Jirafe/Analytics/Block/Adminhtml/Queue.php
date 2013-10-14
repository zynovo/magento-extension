<?php

/**
 * Adminhtml Queue Block
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Block_Adminhtml_Queue extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_blockGroup = 'jirafe_analytics';
        $this->_controller = 'adminhtml_queue';
        $this->_headerText = Mage::helper('jirafe_analytics')->__('Jirafe Analytics: Data Captured By Observers');
        parent::__construct();
        $this->removeButton('add');
    }

}
