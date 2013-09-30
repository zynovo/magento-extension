<?php

/**
 * Product Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */


class Jirafe_Analytics_Model_Product extends Jirafe_Analytics_Model_Abstract
{
    
    
    /**
     * Create JSON object for admin add product events
     *
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    
    public function getAddJson( $product )
    {
        try {
            $data = array(
                'id' => $product->getEntityId(),
                'name' => $product->getName(),
                'change_date' => $this->_formatDate( $product->getUpdatedAt() ),
                'create_date' => $this->_formatDate( $product->getCreatedAt() )
            );
            
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Product::getAddJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Create JSON object for admin modify product events
     *
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    
    public function getModifyJson( $product )
    {
        try {
            $data = array(
                'id' => $product->getEntityId(),
                'name' => $product->getName(),
                'change_date' => $this->_formatDate( $product->getUpdatedAt() ),
                'create_date' => $this->_formatDate( $product->getCreatedAt() )
            );
            
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Product::getModifyJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Create JSON object for admin product status change events
     *
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    
    public function getStatusChangeJson( $product )
    {
        try {
            $data = array(
                'id' => $product->getEntityId(),
                'name' => $product->getName(),
                'change_date' => $this->_formatDate( $product->getUpdatedAt() ),
                'create_date' => $this->_formatDate( $product->getCreatedAt() )
            );
            
            return json_encode($data);
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Product::getStatusChangeJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Create JSON object for admin product delete events
     *
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    
    public function getDeleteJson( $product )
    {
        try {
            $data = array(
                    'id' => $product->getEntityId(),
                    'name' => $product->getName(),
                    'change_date' => $this->_formatDate( $product->getUpdatedAt() ),
                    'create_date' => $this->_formatDate( $product->getCreatedAt() )
            );
            
            return json_encode($data);
          } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Product::getDeleteJson(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /*
    protected function _getImages( $product ) 
    {    
        $thumbnail = Mage::helper('catalog/image')->init($product, 'thumbnail');
        $smallImage = Mage::helper('catalog/image')->init($product, 'small_image');
        $image = Mage::helper('catalog/image')->init($product, 'image');
        return '[{"url":"' . $thumbnail .'"},{"url":"' . $smallImage .'"},{"url":"' . $image .'"}]';
    }
    
    protected function _getJson( $product )
    {
      
         $images = $this->_getImages( $product );
       
        $json = '{
            "brand":"X",
            "catalog":"",
            "categories":"",
            "change_date":"' . date(DATE_ISO8601, strtotime($product->getUpdatedAt())) . '",
            "code":"6666",
            "create_date":"' . date(DATE_ISO8601, strtotime($product->getCreatedAt())) . '",
            "id":"' . $product->getEntityId() . '",
            "images":' . $images . ',
            "is_product":true,
            "is_sku":true,
            "name":"' . $product->getName() . ',
            "rating":"5.0",
            "vendors":""}';
     
        
        return $json;
    }
    

    */

}