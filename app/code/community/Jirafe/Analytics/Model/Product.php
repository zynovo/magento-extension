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
    
    public function getArray( $productId = null, $storeId = null, $isRoot = true, $product = null )
    {
        try {
            if ( $productId && $storeId ) {
                $product = Mage::getModel('catalog/product')->setStoreId( $storeId )->load( $productId );
            }
            
            if ( $product ) {
                
                $parentIds = $this->_getParentIds( $product );
                
                /**
                 * Get field map array
                 */
                $fieldMap = $this->_getFieldMap( 'product', $product->getData() );
                $categoryValues = $this->_getCategoryValues( $product, $storeId);
                $images = $this->_getImages( $product );
                $element = array(
                    $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                    $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento'],
                    $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                    'is_product' => $this->_isProduct( $product->getTypeId(), $parentIds ) ,
                    'is_sku' => $this->_isSku( $product->getTypeId() ),
                    'catalog' => $this->_getCatalog( $storeId ),
                    $fieldMap['name']['api'] => $fieldMap['name']['magento'],
                    $fieldMap['code']['api'] => $fieldMap['code']['magento'],
                    'categories' => $categoryValues['category'] ? $categoryValues['category'] : (object) null,
                    'images' => $images ? $images : (object) null,
                    'url' => $categoryValues['url'] ? $categoryValues['url'] : (object) null
                    );
                
                if ($isRoot) {
                    $element['base_product'] = $this->_getBaseProducts( $parentIds );
                    $element['attributes'] = $this->_getAttributes( $product, $fieldMap );
                }
                return $element;
            } else {
                return array();
            }
            
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Product::getArray()', $e->getMessage(), $e);
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
     * Create array of categories and product URLs associated with product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    
    protected function _getCategoryValues( $product = null, $storeId = null)
    {
        try {
            if ($product) {
                $data = array();
                
                foreach ($product->getCategoryIds() as $catId) {
                    $category = Mage::getModel('catalog/category')->load( $catId );
                    
                    /**
                      * Get field map array
                      */
                     $fieldMap = $this->_getFieldMap( 'category', $category );
                     
                     $data['category'] = array(
                         $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                         $fieldMap['name']['api'] => $fieldMap['name']['magento'],
                         $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                         $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento']
                     );
                     
                     if( $category->getLevel() > 2 ){
                         if ( $parent = Mage::getModel('catalog/category')->load($category->getParentId()) ) {
                             $fieldMap = $this->_getFieldMap( 'category', $parent );
                             $data['category']['parent_categories'] = array(
                                 $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                                 $fieldMap['name']['api'] => $fieldMap['name']['magento'],
                                 $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                                 $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento']
                             );
                         }
                      }
                    
                    $data['url'] = array(
                        'admin' => Mage::getUrl() . 'index.php/admin/catalog_product/edit/id/' . $product->getId(),
                        'store' => $product->getUrlInStore()
                    );
                }
                
                return $data;
            } else {
                return array();
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Product::_getCategoryValues()', $e->getMessage(), $e);
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
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Product::_getBaseProducts()', $e->getMessage(), $e);
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
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Product::_getParentIds()', $e->getMessage(), $e);
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
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Product::_getAttributes()', $e->getMessage(), $e);
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
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Product::_getImages()', $e->getMessage(), $e);
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
    
    public function getJson( $productId = null, $isRoot = true, $product = null )
    {
        if ( $productId || $product) {
            return str_replace('\/', '/', json_encode( $this->getArray( $productId, $isRoot, $product ) ) );
        } else {
            return false;
        }
    }
    
    /**
     * Create array of product historical data
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    
    public function getHistoricalData( $startDate = null, $endDate = null )
    {
        try {
            $data = array();
            
            $products = Mage::getModel('catalog/product')
                ->getCollection()
                ->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns( array('e.entity_id') )
                ->join( array('pw'=>Mage::getSingleton('core/resource')->getTableName('catalog/product_website')), 'e.entity_id = pw.product_id', array('pw.website_id as store_id'))
                ->distinct(true)
                ->order('pw.website_id ASC');
            
            if ( $startDate && $endDate ){
                $where = "created_at BETWEEN '$startDate' AND '$endDate'";
            } else if ( $startDate && !$endDate ){
                $where = "created_at >= '$startDate'";
            } else if ( !$startDate && $endDate ){
                $where = "created_at <= 'endDate'";
            } else {
                $where = null;
            }
            
            if ($where) {
                $products->where( $where );
            }
            
            foreach($products->query() as $product) {
                $data[] = array(
                    'type_id' => Jirafe_Analytics_Model_Data_Type::PRODUCT,
                    'store_id' => $product['store_id'],
                    'json' => $this->getJson( $product['entity_id'], $product['store_id'], null, null )
                );
            }
            
            return $data;
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Product::getHistoricalData()', $e->getMessage(), $e);
            return false;
        }
    }
    
}