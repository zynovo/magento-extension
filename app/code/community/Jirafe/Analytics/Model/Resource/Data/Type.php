<?php

/**
 * Data Type Resource Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Resource_Data_Type extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('jirafe_analytics/data_type','id');
    }
}
