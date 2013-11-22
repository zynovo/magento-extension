<?php

/**
 * Map Field Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Map_Field extends Jirafe_Analytics_Model_Abstract
{
   
    /**
     * Return array of Magento fields
     *
     * @return array
     * @throws Exception if unable to return array of Magento fields
     */
    
    public function getArray() 
    {
        try {
            $map = array();
            $quote = Mage::getModel('sales/quote')->getCollection()->getFirstItem();
            
            if ( $cart = $this->_getCartFields( $quote ) ) {
                $map['cart'] = implode( ',', $cart );
            }
            
            if ( $cartItem = $this->_getCartItemFields( $quote ) ) {
                $map['cart_item'] = implode( ',', $cartItem );
            }
            
            if ( $category = $this->_getCategoryFields() ) {
                $map['category'] = implode( ',', $category );
            }
           
            if ( $customer = $this->_getCustomerFields() ) {
                $map['customer'] = implode( ',', $customer );
            }
            
            if ( $employee = $this->_getEmployeeFields() ) {
                $map['employee'] = implode( ',', $employee );
            }
            
            if ( $order = $this->_getOrderFields() ) {
                $map['order'] = implode( ',', $order );
            }
            
            if ( $orderItem = $this->_getOrderItemFields() ) {
                $map['order_item'] = implode( ',', $orderItem );
            }
            
            if ( $product = $this->_getProductFields() ) {
                $map['product'] = implode( ',', $product );
            }
            
            return $map;
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map_Field::getArray(): ' . $e->getMessage());
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
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map_Field::_getCartFields(): ' . $e->getMessage());
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
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map_Field::_getCartItemFields(): ' . $e->getMessage());
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
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map_Field::_getCategoryFields(): ' . $e->getMessage());
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
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map_Field::_getCustomerFields(): ' . $e->getMessage());
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
            
            if ( $employee = Mage::getSingleton('admin/user')->getCollection()->getFirstItem()->getData() ) {
                $output = $this->_flattenArray( array_keys( $employee ) );
            }
            
            return $output;
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map_Field::_getEmployeeFields(): ' . $e->getMessage());
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
                
            }
            
            return $output;
            
        } catch (Exception $e) {
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map_Field::_getOrderFields(): ' . $e->getMessage());
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
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map_Field::_getOrderItemFields(): ' . $e->getMessage());
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
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map_Field::_getProductFields(): ' . $e->getMessage());
        }
    }
    
    /**
     * Get Magento fields by element name available for mapping
     *
     * @return mixed
     * @throws Exception if unable to return array of fields
     */
    
    public function getMagentoFieldsByElement( $element = null)
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
            Mage::throwException('FIELD MAPPING ERROR: Jirafe_Analytics_Model_Map_Field::_getMagentoFieldsByElement(): ' . $e->getMessage());
        }
         
    }
    
}