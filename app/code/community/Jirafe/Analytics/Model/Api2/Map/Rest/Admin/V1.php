<?php

/**
 * Api2 Map Rest Admin V1 Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Api2_Map_Rest_Admin_V1 extends Jirafe_Analytics_Model_Api2_Map_Rest
{
    
    /**
     * Update field mapy
     *
     * @param array $data
     */
    protected function _update(array $data)
    {
        $message = Mage::getModel('jirafe_analytics/map')->updateMap( $data );
        return $message;
        
    }
}
