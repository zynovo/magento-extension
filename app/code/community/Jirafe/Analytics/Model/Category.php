<?php

/**
 * Category Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Category extends Jirafe_Analytics_Model_Abstract
{

    /**
     * Create category array of data required by Jirafe API
     * 
     * @param Mage_Catalog_Model_Category $category
     * @return mixed
     */
    
    public function getArray( $category = null ) 
    {
        try {
            if ($category) {
                
                /**
                 * Get field map array
                 */
                
                $fieldMap = $this->_getFieldMap( 'category', $category );
                
                return array(
                    $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                    $fieldMap['name']['api'] => $fieldMap['name']['magento'],
                    $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                    $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento']
                );
            } else {
                return array();
            }
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Category::getArray()', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convert category array into JSON object
     *
     * @param array $category
     * @return mixed
     */
    
    public function getJson( $category = null )
    {
        if ($category) {
            return json_encode( $this->getArray( $category ) );
        } else {
            return false;
        }
        
    }
}