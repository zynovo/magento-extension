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
    
    
    public function test()
    {
        $product = Mage::getModel('catalog/product')->load( 144 );
        $ary = $this->_getFieldMap( 'product', $product->getData() );
        return $ary;
    }
   
}