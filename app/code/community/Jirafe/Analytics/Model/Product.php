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
    
    public function getArray( $productId = null )
    {
        try {
            if ($productId) {
                
                $product = Mage::getModel('catalog/product')->load( $productId );
                
                /**
                 * Get field map array
                 */
                $fieldMap = $this->_getFieldMap( 'product', $product->getData() );
                
                return array(
                    $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                    $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento'],
                    $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                    'is_product' => $this->_isProduct( $product->getTypeId() ) ,
                    'is_sku' => $this->_isSku( $product->getTypeId() ),
                    'catalog' => $this->_getCatalog( $product->getStoreId() ),
                    $fieldMap['name']['api'] => $fieldMap['name']['magento'],
                    $fieldMap['code']['api'] => $fieldMap['code']['magento'],
                    'brand' => '',
                    'categories' => $this->getCategories( $product ),
                    'images' => $this->getImages( $product ));
            } else {
                return array();
            }
            
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Product::getArray()', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Use Magento product type to determine whether this is an API product
     * 
     * @param string $type_id
     * @return boolean
     */
    
    protected function _isProduct ( $type_id = null )
    {
        switch ($type_id) {
            case 'simple':
                return true;
                break;
            case 'grouped':
                return false;
                break;
            case 'configurable':
                return false;
                break;
            case 'virtual':
                return true;
                break;
            case 'bundle':
                return false;
                break;
            case 'downloadable':
                return true;
                break;
            case 'giftcard':
                return true;
                break;
            default:
                return false;
                break;
        }
    }
    
    /**
     * Use Magento product type to determine whether this is an API SKU
     *
     * @param string $type_id
     * @return boolean
     */
    
    protected function _isSku ( $type_id = null )
    {
        switch ($type_id) {
            case 'simple':
                return true;
                break;
            case 'grouped':
                return false;
                break;
            case 'configurable':
                return false;
                break;
            case 'virtual':
                return true;
                break;
            case 'bundle':
                return false;
                break;
            case 'downloadable':
                return true;
                break;
            case 'giftcard':
                return true;
                break;
            default:
                return false;
                break;
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
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Product::getCategories()', $e->getMessage());
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
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Product::getImages()', $e->getMessage());
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
    
    public function getJson( $productId = null )
    {
        if ( $productId ) {
            return json_encode( $this->getArray( $productId ) );
        } else {
            return false;
        }
    }
    
}