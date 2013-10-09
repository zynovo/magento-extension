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
     * Create product array of data required by Jirafe API
     *
     * @param int $productId
     * @param int $storeId
     * @return mixed
     */
    
    public function getArray( $productId = null, $storeId = null )
    {
        try {
            if ($productId) {
                $product = Mage::getModel('catalog/product')->load( $productId );
                
                if (!$storeId) {
                    $storeId = $product->getStoreId();
                }
                return array(
                    'id' => $product->getData('entity_id'),
                    'create_date' => $this->_formatDate( $product->getData( 'created_at' ) ),
                    'change_date' => $this->_formatDate( $product->getData( 'updated_at' ) ),
                    'is_product' => true,
                    'is_sku' => true,
                    'catalog' => $this->_getCatalog( $storeId ),
                    'name' => $product->getData('name'),
                    'code' => $product->getData('sku'),
                    'brand' => '',
                    'categories' => $this->getCategories( $product ),
                    'images' => $this->getImages( $product ));
            } else {
                return array();
            }
            
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Product::getArray(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Create array of categories associated with product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    
    public function getCategories( $product = null )
    {
        try {
            if ($product) {
                $data = array();
                $categories = $product->getCategoryIds();
                foreach ($categories as $catId) {
                    $category = Mage::getModel('catalog/category')->load( $catId ) ;
                    $data[] = array(
                        'id' => $catId,
                        'name' => $category->getName());
                }
                return $data;
            } else {
                return array();
            }
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Product::getCategories(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Create array of images associated with product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    
    public function getImages( $product = null )
    {
        try {
            if ( $product ) {
                return array(
                    array( 'url' => $product->getMediaConfig()->getMediaUrl( $product->getData( 'image' ) ) )
                );
            } else {
                return array( 'url' => '' );
            }
        } catch (Exception $e) {
            Mage::log('ERROR Jirafe_Analytics_Model_Product::getImages(): ' . $e->getMessage(),null,'jirafe_analytics.log');
            return false;
        }
    }
    
    /**
     * Convert product array into JSON object
     *
     * @param  array $product
     * @param  int   $storeId
     * @return mixed
     */
    
    public function getJson( $productId = null, $storeId = null )
    {
        if ( $productId ) {
            return json_encode( $this->getArray( $productId, $storeId ) );
        } else {
            return false;
        }
    }
    
}