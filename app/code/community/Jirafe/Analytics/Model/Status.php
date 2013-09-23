<?php

/**
 * Status Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Status extends Mage_Core_Model_Abstract
{

    const ADD = 1;
    
    const MODIFY = 2;
    
    const DELETE  = 3;
    
    const CANCEL  = 4;
    
    const ATTRIBUTE_ADD  = 5;
    
    const ATTRIBUTE_MODIFY  = 6;
    
    const ATTRIBUTE_DELETE  = 6;
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/status');
    }

}