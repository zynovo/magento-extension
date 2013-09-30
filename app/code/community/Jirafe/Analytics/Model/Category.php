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
     * Create JSON object for add category events
     * 
     * @param Mage_Catalog_Model_Category $category
     * @return mixed
     */
    
    public function getAddJson( $category ) 
    {
        Mage::log($category);
        try {
            $data = array(
                'id' => $category->getEntityId(),
                'name' => $category->getName(),
                'change_date' => $this->_formatDate(($category->getUpdatedAt())),
                'create_date' => $this->_formatDate(($category->getCreatedAt()))
            );
            
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Category::getAddJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Create JSON object for modify category events
     *
     * @param Mage_Catalog_Model_Category $category
     * @return mixed
     */
    
    public function getModifyJson( $category )
    {
        try {
            $data = array(
                'id' => $category->getEntityId(),
                'name' => $category->getName(),
                'change_date' => $this->_formatDate(($category->getUpdatedAt())),
                'create_date' => $this->_formatDate(($category->getCreatedAt()))
            );
            
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Category::getModifyJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Create JSON object for delete category events
     *
     * @param Mage_Catalog_Model_Category $category
     * @return mixed
     */
    
    public function getDeleteJson( $category )
    {
        try {
            $data = array(
                'id' => $category->getEntityId(),
                'name' => $category->getName(),
                'change_date' => $this->_formatDate(($category->getUpdatedAt())),
                'create_date' => $this->_formatDate(($category->getCreatedAt()))
            );
            
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Category::getDeleteJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
}