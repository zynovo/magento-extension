<?php

/**
 * Map Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Map extends Jirafe_Analytics_Model_Abstract
{
    protected $_magentoFields = null;
    
    /**
     * Class construction & resource initialization
     */
    
    protected function _construct()
    {
        $this->_init('jirafe_analytics/map');
    }
    
    /**
     * Return array of field mappings
     *
     * @return array
     * @throws Exception if unable to return array of field mappings from db
     */
    
    public function getArray() 
    {
        try {
           $map = array();
           $collection = $this->getCollection()->setOrder( 'element', 'ASC' );
           $last = null;
           $group = array();
           
           foreach ( $collection as $field ) {
               $current = $field->getElement();
               
               if (!$last) {
                   $last = $current;
               } else if ( $last != $current ) {
                   $map[ $last ] = $group;
                   $last = $current;
                   $group = array();
               }
               
               $group[ $field->getKey() ] =  array(
                   'element' => $field->getElement(),
                   'key' => $field->getKey(),
                   'api' => $field->getApi(), 
                   'magento' => $field->getMagento(), 
                   'type' => $field->getType(),
                   'default' => $field->getDefault() ) ;
              
               $last = $current;
           }
           
           $map[ $last ] = $group;
           
           return $map;
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::getArray(): ' . $e->getMessage());
        }
    }
    
    /**
     * Get Magento cart fields available for mapping
     *
     * @return array
     * @throws Exception if unable to return array of fields
     */
    
    protected function _getCartFields(  $quote = null ) 
    {
        try {
            $output = array();
            
            if (!$quote) {
                $quote = Mage::getModel('sales/quote')->getCollection()->getFirstItem();
            }
            
            $output= $this->_flattenArray( array_keys( $quote->getData() ) );
            
            return $output;
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_getCartFields(): ' . $e->getMessage());
        }
    }
    
    /**
     * Get Magento cart item fields available for mapping
     *
     * @return array
     * @throws Exception if unable to return array of fields
     */
    
    protected function _getCartItemFields( $quote = null )
    {
        try {
            $output = array();
            
            if (!$quote) {
                $quote = Mage::getModel('sales/quote')->getCollection()->getFirstItem();
            }
            
            if ( $quoteItem = $quote->getItemsCollection()->getData() ) {
                $output = $this->_flattenArray( array_keys( $quoteItem[0] ) );
            }
            
            return $output;
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_getCartItemFields(): ' . $e->getMessage());
        }
    }
    
    /**
     * Get Magento category fields available for mapping
     *
     * @return array
     * @throws Exception if unable to return array of fields
     */
    
    protected function _getCategoryFields()
    {
        try {
            $output = array();
            
            if ( $category = Mage::getModel('catalog/category')->getCollection()->getFirstItem()->getData() ) {
                $output = $this->_flattenArray( array_keys( $category ) );
            }
            
            return $output;
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_getCategoryFields(): ' . $e->getMessage());
        }
    }
    
    /**
     * Get Magento customer fields available for mapping
     *
     * @return array
     * @throws Exception if unable to return array of fields
     */
    
    protected function _getCustomerFields()
    {
        try {
            $output = array();
            
            if ( $customer = Mage::getModel('customer/customer')->getCollection()->getFirstItem()->getData() ) {
                $output = $this->_flattenArray( array_keys( $customer ) );
            }
            
            return $output;
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_getCustomerFields(): ' . $e->getMessage());
        }
    }
    
    /**
     * Get Magento employee fields available for mapping
     *
     * @return array
     * @throws Exception if unable to return array of fields
     */
    
    protected function _getEmployeeFields()
    {
        try {
            $output = array();
            
            if ( $employee = Mage::getModel('admin/user')->getCollection()->getFirstItem()->getData() ) {
                $output = $this->_flattenArray( array_keys( $employee ) );
            }
            
            return $output;
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_getEmployeeFields(): ' . $e->getMessage());
        }
    }
    
    /**
     * Get Magento order fields available for mapping
     *
     * @return array
     * @throws Exception if unable to return array of fields
     */
    
    protected function _getOrderFields()
    {
        try {
            $output = array();
            
            if ( $order = Mage::getModel('sales/order')->getCollection()->getFirstItem()->getData() ) {
                $output = $this->_flattenArray( array_keys( $order ) );
                
                if ( $payment = Mage::getModel('sales/order')->getCollection()->getFirstItem()->getPayment()->getData() ) {
                    $output = array_merge( $output, $this->_flattenArray( array_keys( $payment ), 'payment' ) );
                }
                
            }
            
            return $output;
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_getOrderFields(): ' . $e->getMessage());
        }
    }
    
    /**
     * Get Magento order item fields available for mapping
     *
     * @return array
     * @throws Exception if unable to return array of fields
     */
    
    protected function _getOrderItemFields()
    {
        try {
            $output = array();
            
                if ( $order_item = Mage::getModel('sales/order_item')->getCollection()->getFirstItem()->getData() ) {
                    $output = $this->_flattenArray( array_keys( $order_item ) );
                }
            
            return $output;
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_getOrderItemFields(): ' . $e->getMessage());
        }
    }
    
    /**
     * Get Magento product fields available for mapping
     *
     * @return array
     * @throws Exception if unable to return array of fields
     */
    
    protected function _getProductFields()
    {
        try {
            $output = array();
            
            
            if ( $product = Mage::getModel('catalog/product')->getCollection()->getFirstItem()->getData() ) {
                $output = $this->_flattenArray( array_keys( $product ) );
            }
            
            $collection = Mage::getResourceModel('catalog/product_attribute_collection');
            $attributes = array();
            
            foreach ($collection as $attribute) {
                $code = $attribute->getAttributeCode();
                if ( !array_search($code,$output) ) {
                    $attributes[] = $code;
                }
            }
            
            if ($attributes) {
                $output = array_merge( $output, $this->_flattenArray($attributes) );
            }
            return $output;
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_getProductFields(): ' . $e->getMessage());
        }
    }
    
    /**
     * Get Magento fields by element name available for mapping
     *
     * @return mixed
     * @throws Exception if unable to return array of fields
     */
    
    protected function _getMagentoFieldsByElement( $element = null)
    {
        try {
            
            if ( $element ) {
                switch ( $element ) {
                    case 'cart':
                        return $this->_getCartFields();
                        break;
                    case 'cart_item':
                        return $this->_getCartItemFields();
                        break;
                    case 'category':
                        return $this->_getCategoryFields();
                        break;
                    case 'customer':
                        return $this->_getCustomerFields();
                        break;
                    case 'employee':
                        return $this->_getEmployeeFields();
                        break;
                    case 'order':
                        return $this->_getOrderFields();
                        break;
                    case 'order_item':
                        return $this->_getOrderItemFields();
                        break;
                    case 'product':
                        return $this->_getProductFields();
                        break;
                    default:
                        return null;
                        break;
                }
            }
            return null;
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_getMagentoFieldsByElement(): ' . $e->getMessage());
        }
         
    }
    
    /**
     * validate Magento element identifer
     *
     * @return boolean
     * @throws Exception if unable to validate element identifier
     */
    
    protected function _validateElement( $params = null )
    {
        try {
        if ( $params ) {
                $count = $this->getCollection()
                        ->addFieldToFilter( '`element`', $params->element )
                        ->getSize();
                
                return $count ? true : false;
            } else {
                return false;
            }
    
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_validateElement(): ' . $e->getMessage());
        }
    }
    
    /**
     * validate key
     *
     * @return boolean
     * @throws Exception if unable to validate key
     */
    
    protected function _validateKey( $params = null )
    {
        try {
            if ( $params ) {
                $count = $this->getCollection()
                ->addFieldToFilter( '`element`', $params->element )
                ->addFieldToFilter( '`key`', $params->key )
                ->getSize();
                
                return $count ? true : false;
            } else {
                return false;
            }
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_validateKey(): ' . $e->getMessage());
        }
    }
    
    /**
     * validate Magento field name
     *
     * @return boolean
     * @throws Exception if unable to validate field name
     */
    
    protected function _validateField( $params = null )
    {
        try {
            if ( $params ) {
                $fields = $this->_getMagentoFieldsByElement( $params->element );
                return array_search( $params->magento, $fields[ $params->element ] ) ? true : false;
            } else {
                return false;
            }
        
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_validateField(): ' . $e->getMessage());
        }
    }
    
    /**
     * Get field object to update
     *
     * @return Jirafe_Analytics_Model_Map
     * @throws Exception if unable to query db for field object
     */
    
    protected function _getField( $params = null )
    {
        try {
            if ( $params ) {
                return $this->getCollection()
                        ->addFieldToFilter( '`element`', $params->element )
                        ->addFieldToFilter( '`key`', $params->key )
                        ->getFirstItem();
            } else {
                return null;
            }
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_getField(): ' . $e->getMessage());
        }
    }
    
    /**
     * Update field map
     *
     * @return json
     * @throws Exception if unable to update field map
     */
    
    public function updateMap( $json = null)
    {
        try {
            if ( $json ) {
                
                $params = json_decode( $json );
                
                if ( !$this->_validateElement( $params ) ) {
                    
                    return 'invalid_element';
                    
                } else if ( !$this->_validateKey( $params ) ) {
                    
                    return 'invalid_key';
                
                } else if ( !$this->_validateField( $params ) ) {
                    
                    return 'invalid_field';
                    
                } else {
                    
                    if ( $field = $this->_getField( $params ) ) {
                        
                        if ( $api = trim($params->api) ) {
                            $field->setApi($api);
                        }
                        
                        if ( $magento = trim($params->magento) ) {
                            $field->setMagento($magento);
                        }
                        
                        if ( $type = trim($params->type) ) {
                            $field->setType($type);
                        }
                        
                        if ( $default = trim($params->default) ) {
                            $field->setDefault($default);
                        }
                        $field->setUpdatedDt( $this->_getCreatedDt() );
                        $field->save();
                        
                        Mage::register('jirafe_analytics_regenerate_map', true);
                        
                        return 'success';
                        
                    } else {
                        
                        $this->_log('ERROR', 'Jirafe_Analytics_Model_Map::_getField()', 'Unable to update field map. Request JSON=' . $json);
                        
                        return 'not_updated';
                    }
                        
                }
            } else {
                
                 return 'invalid_json';
                 
            }
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::update(): ' . $e->getMessage());
        }
    }
    
    /**
     * Generate JSON response message
     * 
     * @param string $success
     * @param string $errorType
     * @return json
     * @throws Exception if unable generate JSON response message
     */
    
    protected function _getJsonResponse( $success = false, $errorType = null ) 
    {
        try {
            $response = array( 'success' => $success );
            
            if ( $errorType ) {
                $response['error_type'] = $errorType;
            }
            
            return json_encode( $response );
            
         } catch (Exception $e) {
             
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_getJsonResponse(): ' . $e->getMessage());
            
        }
    }
    
    
    /**
     * Get all Magento field mapping options
     *
     * @return string
     * @throws Exception if unable to return array of fields
     */
    
    public function getOptions()
    {
        try {
            
            $map = array();
            $quote = Mage::getModel('sales/quote')->getCollection()->getFirstItem();
            
            if ( $cart = $this->_getCartFields( $quote ) ) {
                $map['cart'] = $cart;
            }
            
            if ( $cartItem = $this->_getCartItemFields( $quote ) ) {
                $map['cart_item'] = $cartItem;
            }
            
            if ( $category = $this->_getCategoryFields() ) {
                $map['category'] = $category;
            }
            
            if ( $customer = $this->_getCustomerFields() ) {
                $map['customer'] = $customer;
            }
            
            if ( $employee = $this->_getEmployeeFields() ) {
                $map['employee'] = $employee;
            }
            
            if ( $order = $this->_getOrderFields() ) {
                $map['order'] = $order;
            }
            
            if ( $orderItem = $this->_getOrderItemFields() ) {
                $map['order_item'] = $orderItem;
            }
            
            if ( $product = $this->_getProductFields() ) {
                $map['product'] = $product;
            }
            
            return json_encode($map);
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::options(): ' . $e->getMessage());
        }
         
    }
}