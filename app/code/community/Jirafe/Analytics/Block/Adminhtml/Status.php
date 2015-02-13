<?php

/**
 * Jirafe Analytic Status
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2014 Jirafe, Inc. (http://jirafe.com/)
 */

class Jirafe_Analytics_Block_Adminhtml_Status extends Mage_Adminhtml_Block_Template
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_headerText = $this->__('Jirafe Analytics Status');
        parent::__construct();
        $this->setTemplate('jirafe_analytics/status.phtml');
    }

    public function checkCredentials()
    {
        $results = array();
        foreach (Mage::app()->getWebsites() as $website) {
            $enabled = $website->getConfig('jirafe_analytics/general/enabled');
            if ($enabled) {
                $results[] = array(
                    'success' => Mage::getModel('jirafe_analytics/curl')->checkCredentials($website->getId()),
                    'website' => $website
                );
            }
        }
        return $results;
    }
}
