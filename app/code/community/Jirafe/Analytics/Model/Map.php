<?php

/**
 * Map Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Map extends Jirafe_Analytics_Model_Abstract
{
    
    /**
     * Return array of field mappings
     *
     * @return array
     */
    
    public function getArray() 
    {
        try {
           
        } catch (Exception $e) {
            $this->_log( 'ERROR', 'Jirafe_Analytics_Model_Map::getArray()', $e->getMessage());
            return false;
        }
    }
   
}