<?php

/**
 * Adminhtml Dashboard Block
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2014 Jirafe, Inc. (http://jirafe.com/)
 */

class Jirafe_Analytics_Block_Adminhtml_Dashboard extends Mage_Core_Block_Template
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_blockGroup = 'jirafe_analytics';
        $this->_controller = 'adminhtml_dashboard';
        $this->_headerText = Mage::helper('jirafe_analytics')->__('Jirafe Analytics: Dashboard');
        parent::__construct();
    }

}
