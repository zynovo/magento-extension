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
    
    protected $_product = null;
    
    protected $_storeId =null;
    
    protected $_parent = null;
    
    protected $_parentTypeId = null;
    
    protected $_baseProducts = null;
    
    protected $_attributes = null;
    
    protected $_typeId = null;
    
    protected $_fieldMap = null;
    
    
    /**
     * Get JSON version of product object
     *
     * @param string $productId
     * @param string $storeId
     * @param Mage_Catalog_Model_Product $product
     * @param boolean $itemAttributes
     * @return string
     */
     public function getJson( $productId = null, $storeId = null, $product = null, $itemAttributes = null )
     {
         if ( ( is_numeric($productId) && is_numeric($storeId) ) || $product) {
             return str_replace('\/', '/', json_encode( $this->getArray( $productId, $storeId, $product, $itemAttributes ) ) );
         } else {
             return null;
         }
     }
    
    /**
     * Create product array of data required by Jirafe API
     *
     * @param string $productId
     * @param string $storeId
     * @param Mage_Catalog_Core_Product $product
     * @param boolean $itemAttributes
     * @return array
     */
    
    public function getArray( $productId = null, $storeId = null, $product = null, $itemAttributes = null  )
    {
        try {
           
            if ( $productId ) {
                $this->_product = Mage::getModel('catalog/product')->load( $productId );
            } else {
                $this->_product = $product;
            }
            
            if ( $this->_product ) {
                
                if ( $storeId ) {
                    $this->_storeId = $storeId;
                } else {
                    $this->_storeId = $this->_product->getStoreId();
                }
                
                $this->_typeId =  $this->_product->getTypeId();
                
                if ($this->_typeId === 'simple') {
                 
                    if ($this->_parent = $this->_getParent()) {
                     
                        $this->_parentTypeId = $this->_parent->getTypeId();
                        $this->_baseProducts = $this->_getBaseProducts();
                        
                        if ( $this->_parentTypeId == 'configurable' ) {
                         $this->_attributes = $this->_getAttributes( $itemAttributes ) ;
                        }
                    }
                }
                
                /**
                 * Get field map array
                 */
                $this->_fieldMap = $this->_getFieldMap( 'product', $this->_product->getData() );
                
                $element = array(
                    $this->_fieldMap['id']['api'] => $this->_fieldMap['id']['magento'],
                    $this->_fieldMap['create_date']['api'] => $this->_fieldMap['create_date']['magento'],
                    $this->_fieldMap['change_date']['api'] => $this->_fieldMap['change_date']['magento'],
                    'is_product' => $this->_isProduct(),
                    'is_sku' => $this->_isSku(),
                    'is_bundle' => ($this->_typeId == 'bundle' ) ? true : false,
                    'catalog' => $this->_getCatalog( $this->_storeId ),
                    $this->_fieldMap['name']['api'] => $this->_fieldMap['name']['magento'],
                    $this->_fieldMap['code']['api'] => $this->_fieldMap['code']['magento'],
                 );
                
                
                if ( $brand = $this->_product->getAttributeText('manufacturer') ) {
                    $element['brand'] = $brand;
                }
                
                if ( $this->_attributes ) {
                    $element['attributes'] = $this->_attributes;
                }
                
                if ( $this->_baseProducts ) {
                    $element['base_product'] = $this->_baseProducts;
                }
                
                if ( $categories = $this->_getCategories() ) {
                    $element['categories'] = $categories;
                }
                
                if ( $images = $this->_getImages() ) {
                    $element['images'] = $images;
                }
                
                if ( $urls = $this->_getUrls() ) {
                    $element['url'] = $urls;
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
     * @return boolean
     */
    
    protected function _isProduct ()
    {
        if ( $this->_parent ) {
            return false;
        } else {
            switch ( $this->_typeId ) {
            case 'simple':
                return true;
                break;
            case 'configurable':
                return true;
                break;
            case 'grouped':
                 return false;
                 break;
            case 'virtual':
                return true;
                break;
            case 'bundle':
                return true;
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
    
    protected function _isSku ()
    {
        switch ($this->_typeId) {
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
                return true;
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
     * Create array of categories
     *
     * @return array
     */
    
    protected function _getCategories()
    {
        try {
             $categories = array();
             
             foreach ($this->_product->getCategoryIds() as $catId) {
                 if ( $category = Mage::getModel('catalog/category')->load( $catId ) ) {
                     $categories[] = Mage::getModel('jirafe_analytics/category')->getArray( $category );
                 }
             }
             
             if ($categories) {
                 return json_decode(json_encode($categories), FALSE);
             } else {
                 return null;
             }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Product::_getCategoryValues()', $e->getMessage(), $e);
            return (object) null;
        }
    }
    
    /**
     * Create array product URLs associated with product
     *
     * @return array
     */
    
    protected function _getUrls( )
    {
         try {
             $urls = array(
                  'admin' => Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $this->_product->getId())),
                  'store' => $this->_product->getUrlInStore()
             );
             return json_decode(json_encode($urls), FALSE);
         } catch (Exception $e) {
             Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Product::_getUrls()', $e->getMessage(), $e);
             return (object) null;
         }
    }
    /**
     * Create array of parent products
     *
     * @return mixed
     */
    
    protected function _getBaseProducts()
    {
        try {
            $obj = array();
            
            if ( $this->_parent ) {
                
                $this->_fieldMap = $this->_getFieldMap( 'product', $this->_parent->getData() );
                
                $obj = array(
                    $this->_fieldMap['id']['api'] => $this->_fieldMap['id']['magento'],
                    $this->_fieldMap['name']['api'] => $this->_fieldMap['name']['magento'],
                    $this->_fieldMap['code']['api'] => $this->_fieldMap['code']['magento']
                );
            }
            
            return json_decode(json_encode($obj), FALSE);
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Product::_getBaseProducts()', $e->getMessage(), $e);
            return false;
        }
    }
    
    /**
     * Set product parent
     *
     * @return Mage_Catalog_Model_Product
     */
    
    protected function _getParent()
    {
        try {
            if ( $this->_typeId == "simple" ) {
                $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild( $this->_product->getId() );
                
                if ( !$parentIds ) {
                    $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild( $this->_product->getId() );
                }
                
                if ( is_numeric(@$parentIds[0]) ) {
                    return Mage::getModel('catalog/product')->load( $parentIds[0] );
                }
            } else {
                return null;
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Product::_getParent()', $e->getMessage(), $e);
            return false;
        }
    }
    
    /**
     * Create array of product attributes
     * 
     * @param string $itemAttributes
     * @return mixed
     */
    protected function _getAttributes( $itemAttributes = null )
    {
        try {
            
             $obj = array();
             
             if ($itemAttributes) {
              
                 $attributes = unserialize($itemAttributes);
                 
                 foreach ($attributes as $key => $val) {
                     
                     $options = Mage::getResourceModel('eav/entity_attribute_collection')
                         ->getSelect()
                         ->join(array('o'=>'eav_attribute_option'),'main_table.attribute_id = o.attribute_id')
                         ->join(array('v'=>'eav_attribute_option_value'),'o.option_id = v.option_id')
                         ->reset(Zend_Db_Select::COLUMNS)
                         ->columns( array('main_table.attribute_id','main_table.attribute_code','v.value'))
                         ->where("main_table.attribute_id = $key AND v.option_id = $val")
                         ->limit(1)
                         ->query();
                     
                     foreach($options as $option) {
                         
                         $obj[] = array(
                             'id' => $option['attribute_id'],
                             'name' => $option['attribute_code'],
                             'value' => $option['value']
                         );
                     }
                }
            } else if ( $this->_typeId === 'simple' && $this->_parentTypeId === 'configurable') {
            
               $attributes = $this->_parent->getTypeInstance(true)->getConfigurableAttributesAsArray($this->_parent);
               
               foreach($attributes as $attribute) {
                   if ($value = $this->_product->getAttributeText( $attribute['attribute_code'] ) ) {
                       $obj[] = array(
                           'id' => $attribute['attribute_id'],
                           'name' => $attribute['attribute_code'],
                           'value' => $value
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
     * @return array
     */
    protected function _getImages()
    {
        try {
            return array(
                array( 'url' => $this->_product->getMediaConfig()->getMediaUrl( $this->_product->getData( 'image' ) ) )
            );
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Product::_getImages()', $e->getMessage(), $e);
            return array();
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
            
            $collection = Mage::getModel('catalog/product')
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
                $collection->where( $where );
            }
            
            foreach($collection->query() as $item) {
                $data[] = array(
                    'type_id' => Jirafe_Analytics_Model_Data_Type::PRODUCT,
                    'store_id' => $item['store_id'],
                    'json' => $this->getJson( $item['entity_id'], $item['store_id'], null, null)
                );
            }
            
            return $data;
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Product::getHistoricalData()', $e->getMessage(), $e);
            return false;
        }
    }
    
}