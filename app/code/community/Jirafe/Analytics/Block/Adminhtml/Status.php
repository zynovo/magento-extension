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

    public function hasCron()
    {
        $_resource = Mage::getResourceModel('cron/schedule');
        $_readAdapter = $_resource->getReadConnection();
        $_select = $_readAdapter->select()
            ->from($_resource->getMainTable(), 'created_at')
            ->order('schedule_id ' . Zend_Db_Select::SQL_DESC)
            ->limit(1);
        $_createdAt = $_readAdapter->fetchOne($_select);
        if ($_createdAt) {
            $_twoDays = 2 * 24 * 60 * 60;//in seconds
            $_now = time();
            $_time = strtotime($_createdAt);
            /**
             * if latest cron time is within 2 days
             */
            if ($_time > $_now - $_twoDays) {
                return true;
            }
        }
        return false;
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
