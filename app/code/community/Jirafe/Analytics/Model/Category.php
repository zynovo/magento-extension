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
                return array(
                    'id' => $category->getData('entity_id'),
                    'name' => $category->getData('name'),
                    'change_date' => $this->_formatDate( $category->getData('updated_at') ),
                    'create_date' => $this->_formatDate( $category->getData('created_at') )
                );
            } else {
                return array();
            }
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Category::getArray(): ' . $e->getMessage(),null,'jirafe_analytics.log');
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