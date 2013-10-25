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
     * Update field map
     *
     * @param array $data
     */
    protected function _update(array $data)
    {
        
        $result = Mage::getModel('jirafe_analytics/map')->updateMap( $data );
        
        if ($result['success']) {
            $this->_successMessage( $result['message'], Mage_Api2_Model_Server::HTTP_OK );
        } else {
            $this->_critical( $result['message'] );
        }
       
    }
    
    
    
}
