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
    
    protected function _getCartFields() 
    {
        try {
            $output = array();
            
            if ( $quote = Mage::getModel('sales/quote')->getCollection()->getFirstItem() ) {
                $output['cart'] = $this->_flattenArray( array_keys( $quote->getData() ) );
                
                if ( $quoteItem = $quote->getItemsCollection()->getData() ) {
                    $output['cart_item'] = $this->_flattenArray( array_keys( $quoteItem[0] ) );
                }
            }
            
            return $output;
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_getCartFields(): ' . $e->getMessage());
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
                $output['category'] = $this->_flattenArray( array_keys( $category ) );
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
                $output['customer'] = $this->_flattenArray( array_keys( $customer ) );
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
                $output['employee'] = $this->_flattenArray( array_keys( $employee ) );
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
                $output['order'] = $this->_flattenArray( array_keys( $order ) );
                
                if ( $payment = Mage::getModel('sales/order')->getCollection()->getFirstItem()->getPayment()->getData() ) {
                    $output = array_merge( $map['order'], $this->_flattenArray( array_keys( $payment ), 'payment' ) );
                }
                
                if ( $order_item = Mage::getModel('sales/order_item')->getCollection()->getFirstItem()->getData() ) {
                    $output['order_item'] = $this->_flattenArray( array_keys( $order_item ) );
                }
            }
            
            return $output;
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_getOrderFields(): ' . $e->getMessage());
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
                $output['product'] = $this->_flattenArray( array_keys( $product ) );
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
     * Get all Magento fields available for mapping
     *
     * @return string
     * @throws Exception if unable to return array of fields
     */
    
    public function getAllMagentoFields() 
    {
        try {
            
            $map = array();
            
            if ( $cart = $this->_getCartFields() ) {
                $map = array_merge( $map, $cart );
            }
            
            if ( $category = $this->_getCategoryFields() ) {
                $map = array_merge( $map, $category );
            }
            
            if ( $customer = $this->_getCustomerFields() ) {
                $map = array_merge( $map, $customer );
            }
            
            if ( $employee = $this->_getEmployeeFields() ) {
                $map = array_merge( $map, $employee );
            }
            
            if ( $order = $this->_getOrderFields() ) {
                $map = array_merge( $map, $order );
            }
            
            if ( $product = $this->_getProductFields() ) {
                $map = array_merge( $map, $product );
            }
            
            return json_encode($map);
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::getAllMagentoFields(): ' . $e->getMessage());
        }
       
    }
    
    /**
     * check update json for valid Magento field name
     *
     * @return boolean
     * @throws Exception if unable to validate field name
     */
    
    protected function _validateField( $params = null )
    {
        try {
            if ( $params ) {
                $fields = $this->_getMagentoFieldsByElement( $params->element );
                Zend_Debug::dump( $fields );
            } else {
                return false;
            }
        
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::_validateField(): ' . $e->getMessage());
        }
    }
    
    /**
     * Update field map
     *
     * @return json
     * @throws Exception if unable to update field map
     */
    
    public function update( $json = null)
    {
        try {
            if ( $json ) {
                $params = json_decode( $json );
                Zend_Debug::dump( $params );
                if ( $this->_validateField( $params ) ) {
                    // update data
                } else {
                    // return error message
                }
            } else {
                return false;
            }
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map::update(): ' . $e->getMessage());
        }
    }
}