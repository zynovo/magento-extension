<?php

class Jirafe_Analytics_Model_Base extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->start = time();
        $duration = $this->getDuration();
        $this->resetTimer($this->start + $duration);
    }

    protected function getDuration()
    {
        $duration = Mage::getStoreConfig('jirafe_analytics/historicalpull/duration');
        return $duration;
    }

    protected function resetTimer($stop)
    {
        $this->stop = $stop;
    }

    public function isTimeUp()
    {
        $now = time();
        $timeup = $this->stop <= $now;
        return $timeup;
    }

    public function sendToJirafe($data)
    {
        return Mage::getModel('jirafe_analytics/curl')->sendJson($data);
    }

    public function updateBatchAttemptStatuses($response)
    {
        foreach ($response as $batch) {
            foreach ($batch as $attempt) {
                $model = Mage::getModel('jirafe_analytics/batch');
                $batch = $model->load($attempt['batch_id']);
                $batch->updateBatchAttemptStatus($attempt);
                Mage::getModel('jirafe_analytics/data_attempt')->add($attempt);
            }
        }
    }

    protected function activeWebsites()
    {
        $active = array();
        $websites = Mage::app()->getWebsites();
        foreach ($websites as $websiteId => $_) {
            if(Mage::helper('jirafe_analytics')->getHistoricalFlag($websiteId)) {
                $active[] = $websiteId;
            }
        }
        return $active;
    }
}

