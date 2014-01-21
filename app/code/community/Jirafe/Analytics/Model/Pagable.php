<?php

/**
 * Pagable Interface
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Andy Stanberry (andy@jirafe.com)
 */

interface Jirafe_Analytics_Model_Pagable
{
    /**
     * Create array of cart historical data
     *
     * @param int $websiteId
     * @param int $lastId
     * @return Zend_Paginator
     */
    function getPaginator($websiteId, $lastId = null);

    function getDataType();
}
