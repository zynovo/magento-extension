<?php

/**
 * Batch Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 *
 * @property int $maxRecords    maximum number of records to process
 * @property int $maxAttempts   maximum attempts before failure
 * @property int $maxSize       maximum size of json object in bytes
 *
 */

class Jirafe_Analytics_Model_Batch extends Jirafe_Analytics_Model_Abstract
{
    protected $_maxRecords = null;
    protected $_maxAttempts = null;

    protected $rawData = array();
    protected $dataIds = array();
    protected $json_size = 0;
    protected $extra_chars_size = 10;

    /**
     * Class construction & resource initialization
     *
     * Load user configurable variables from Mage::getStoreConfig() into object property scope
     */
    protected function _construct()
    {
        $this->_init('jirafe_analytics/batch');
        $this->maxRecords  = intval(Mage::getStoreConfig('jirafe_analytics/curl/max_records'));
        $this->maxAttempts = intval(Mage::getStoreConfig('jirafe_analytics/curl/max_attempts'));

        $this->dataIds = array();
        $this->rawData = array();
        $this->json_size = 0;
        $this->extra_chars_size = 10; // account for whitespace, commas and quotes in json that will add to the length
    }

    /**
     * Update batch with information from attempt
     *
     * @param array $attempt    cURL attempt data
     * @return boolean
     * @throws Exception if unable to update jirafe_analytics_batch
     */
    public function updateBatchAttemptStatus($attempt)
    {
        try {
            if ($attempt) {
                $this->setHttpCode($attempt['http_code']);
                $this->setTotalTime($attempt['total_time']);
                $this->setCompletedDt($attempt['created_dt']);
                $this->save();
            } else {
                Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, 'attempt object null');
                return false;
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, $e->getMessage(), $e);
            return false;
        }
    }

    public function addItem($item, $type, $max_size)
    {
        $item_size = strlen($item['json']);

        if ($item_size > $max_size) {
            // TODO: mark this as unbatchable
            throw new Exception('individual json item_size exceeds max_size, unbatchable');
        }

        if ($this->json_size == 0) {
            // if it's empty we can add it
            $this->addToRaw($item, $type);
        } elseif (array_key_exists($type, $this->rawData) && (($this->json_size + $item_size + $this->extra_chars_size) < $max_size)) {
            // if the type is already in here, just check against the item_size
            $this->addToRaw($item, $type);
        } elseif (($this->json_size + $item_size + $this->extra_chars_size + strlen($type)) < $max_size) {
            // if the type isn't in here, check the type string length and the item_size
            $this->addToRaw($item, $type);
        } else {
            // if not, we're too big already
            return false;
        }
        return true;
    }

    protected function addToRaw($item, $type)
    {
        if (!array_key_exists($type, $this->rawData)) {
            $this->rawData[$type] = array();
        }

        $this->rawData[$type][] = json_decode($item['json']);;
        $this->dataIds[] = $item['id'];
        $this->json_size = strlen(json_encode($this->rawData));
    }

    public function hasRawData()
    {
        return !empty($this->rawData);
    }

    /**
     * Overrise save to do the associations w/ data and to convert to json
     */
    public function save()
    {
        if ($this->hasRawData()) {
            $this->setJson(json_encode($this->rawData));
        }
        // generate an id
        if(parent::save()) {
            $batchIndex = 0;
            // generate all of the associations
            foreach ($this->dataIds as $dataId) {
                $batchData = Mage::getModel('jirafe_analytics/batch_data');
                $batchData->setBatchId($this->getId());
                $batchData->setDataId($dataId);
                $batchData->setBatchOrder($batchIndex);
                $batchData->save();
                $batchIndex++;
            }
        }
        return $this;
    }

    public function getWebsiteId()
    {
        return $this->getStoreId();
    }

    public function setWebsiteId($websiteId)
    {
        return $this->setStoreId($websiteId);
    }
}

