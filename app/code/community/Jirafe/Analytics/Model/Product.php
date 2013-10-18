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
    
    public function getArray( $productId = null, $isRoot = true )
    {
        try {
            if ($productId) {
                
                $product = Mage::getModel('catalog/product')->load( $productId );
                $parentIds = $this->_getParentIds( $product );
                
                /**
                 * Get field map array
                 */
                $fieldMap = $this->_getFieldMap( 'product', $product->getData() );
                
                $element = array(
                    $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                    $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento'],
                    $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                    'is_product' => $this->_isProduct( $product->getTypeId(), $parentIds ) ,
                    'is_sku' => $this->_isSku( $product->getTypeId() ),
                    'catalog' => $this->_getCatalog( $product->getStoreId() ),
                    $fieldMap['name']['api'] => $fieldMap['name']['magento'],
                    $fieldMap['code']['api'] => $fieldMap['code']['magento'],
                    'categories' => $this->_getCategories( $product ),
                    'images' => $this->_getImages( $product )
                    );
                
                if ($isRoot) {
                    $element['base_product'] = $this->_getBaseProducts( $parentIds );
                    $element['attributes'] = $this->_getAttributes( $product, $fieldMap );
                    //$element['ancestors'] = array();
                    //$element['vendors'] = (object) null;
                    //$element['urls'] = (object) null;
                }
                
                return $element;
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
     * @param string $baseProduct
     * @return boolean
     */
    
    protected function _isProduct ( $type_id = null, $isChild = null )
    {
        if ( $isChild ) {
            return false;
        } else {
            switch ( $type_id ) {
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
    
    protected function _getCategories( $product = null )
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
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Product::_getCategories()', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create array of parent products
     *
     * @param array $parentIds
     * @return mixed
     */
    
    protected function _getBaseProducts( $parentIds = null )
    {
        try {
            
            $obj = (object) null;
            
            if ( $parentIds ) {
                $obj = array();
                foreach( $parentIds as $parentId ) {
                    $parent = Mage::getModel('catalog/product')->load( $parentId );
                    $fieldMap = $this->_getFieldMap( 'product', $parent->getData() );
                    $obj[] = array( $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                                    $fieldMap['name']['api'] => $fieldMap['name']['magento'],
                                    $fieldMap['code']['api'] => $fieldMap['code']['magento']);
                }
            }
            
            return $obj;
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Product::_getBaseProducts()', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get product parent ids
     *
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    
    protected function _getParentIds( $product = null )
    {
        try {
            if ( $product ) {
                $parentIds = null;
                if ( $product->getTypeId() == "simple" ){
                    $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild( $product->getId() );
                    if ( !$parentIds ) {
                        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild( $product->getId() );
                    }
                }
                if ($parentIds) {
                    return $parentIds;
                }
            }
            
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Product::_getParentIds()', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create array of product attributes
     *
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    
    protected function _getAttributes( $product = null )
    {
        try {
            $obj = (object) null;
            if ( $product ) {
                $attributes = $product->getAttributes();
                $magnetoFields = $this->_getMagentoFieldsByElement( 'product' );
                Mage::log($magnetoFields,null,'attributes.log');
                $obj = array();
                foreach ( $attributes as $attribute ) {
                    if ( $attribute->getAttributeId() && $value = $attribute->getFrontend()->getValue( $product ) && !array_search($attribute->getAttributeCode(), $magnetoFields)) {
                        $obj[] = array( 'id' => $attribute->getAttributeId(), 
                                        'name' => $attribute->getAttributeCode(),
                                        'value' => strval( $value ) 
                                      );
                   }
                }
            }
            
            return $obj;
        } catch (Exception $e) {
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Product::_getAttributes()', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create array of images associated with product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    
    protected function _getImages( $product = null )
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
            $this->_log('ERROR', 'Jirafe_Analytics_Model_Product::_getImages()', $e->getMessage());
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
    
    public function getJson( $productId = null, $isRoot = true )
    {
        if ( $productId ) {
            return str_replace('\/', '/', json_encode( $this->getArray( $productId, $isRoot ) ) );
        } else {
            return false;
        }
    }
    
}