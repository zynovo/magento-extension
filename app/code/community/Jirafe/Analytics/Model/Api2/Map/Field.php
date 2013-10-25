<?php

/**
 * Api2 Field Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Api2_Map_Field extends Jirafe_Analytics_Model_Api2_Map
{
    
   /**
     * Get array of optional Magneto fields
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        return Mage::getModel('jirafe_analytics/map_field')->getArray();
    }
    
}
