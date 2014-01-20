<?php

/**
 * Purge Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 *
 *
 */

class Jirafe_Analytics_Model_Purge
{
    public function _construct()
    {
        $this->_init('jirafe_analytics/purge');
    }

    /**
     * Purge Old Jirafe Logs
     * Trigger by cron
     *
     * @return boolean
     * @throws Exception
     */
    public function purge()
    {
        Mage::helper('jirafe_analytics')->log('DEBUG', __METHOD__, 'Starting Purge Job.', null);
        // Purge processed data older than Mage::getStoreConfig('jirafe_analytics/general/purge_time') minutes
        try {
            Mage::getModel('jirafe_analytics/log')->purgeData();
            Mage::getModel('jirafe_analytics/data')->purgeData();
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, $e->getMessage(), $e);
            return false;
        }
        return true;
    }
}

