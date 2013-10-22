<?php

/**
 * Api Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    
    /**
     * Update field map
     *
     * @return json
     * @throws Exception if unable to update field map
     */
    
    public function updateMap( $json = null)
    {
        try {
            $result = Mage::getModel('jirafe_analytics/map')->updateMap( $json );
            $output = null;
            switch ($result) {
                case 'success':
                    $output = 'success';
                    break;
                case 'invalid_element':
                    $this->_fault('invalid_element', 'Invalid element.');
                    break;
                case 'invalid_key':
                    return $this->_fault('invalid_key', 'Invalid key.');
                    break;
                case 'invalid_field':
                    $this->_fault('invalid_field', 'Invalid Magento field.');
                    break;
                case 'not_updated':
                    $this->_fault('not_updated', 'Server error. Mapping not updated.');
                    break;
                default:
                    $this->_fault('invalid_request', 'Invalid request.');
                    break;
            }
            
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_updated', $e->getMessage());
        }
        return $output;
        
       
    }
   
    
    /**
     * Get all Magento field mapping options
     *
     * @return string
     * @throws Exception if unable to return array of fields
     */
    
    public function getOptions()
    {
        $result = Mage::getModel('jirafe_analytics/map')->getOptions();
        
    }
}