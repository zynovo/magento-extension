<?php

/**
 * Abstract Class Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

abstract class Jirafe_Analytics_Model_Abstract extends Mage_Core_Model_Abstract
{
    
    protected function _formatDate( $date )
    {
        /**
         * Jirafe API requires all dates be UTC in the ISO 8601:2004 format
         */
        return date( DATE_ISO8601, strtotime( $date) );
    }
    
    protected function _getCreatedDt() {
        
        /**
         * Use PHP generated UTC date to avoid MySQL possible timezone configuration issues
         */
        return gmdate('Y-m-d H:i:s');
    }
    
    

}