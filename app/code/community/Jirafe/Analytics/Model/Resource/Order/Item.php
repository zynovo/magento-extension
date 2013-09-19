<?php

/**
 * Order Item Resource Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Resource_Order_Item extends Mage_Core_Model_Resource_Db_Abstract
{    
    public function _construct()
    {
        $this->_init('jirafe_analytics/order_item','id');
    }
}
