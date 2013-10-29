<?php

/**
 * Batch Data Model
 *
 * If API attempt fails, store error message
 * 
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Batch_Data extends Jirafe_Analytics_Model_Abstract
{
    /**
     * Class construction & resource initialization
     */
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/batch_data');
    }
   
}