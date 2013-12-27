<?php

/**
 * Category Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Category extends Jirafe_Analytics_Model_Abstract
{

    /**
     * Create category array of data required by Jirafe API
     *
     * @param Mage_Catalog_Model_Category $category
     * @return mixed
     */

    public function getArray( $category = null )
    {
        try {
            if ($category) {

             /**
              * Get field map array
              */
             $fieldMap = $this->_getFieldMap( 'category', $category );

             $data = array(
                 $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                 $fieldMap['name']['api'] => $fieldMap['name']['magento'],
                 $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                 $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento']
             );

             if ( $parent = Mage::getModel('catalog/category')->load($category->getParentId()) ) {
                 $fieldMap = $this->_getFieldMap( 'category', $parent );
                 $parent = array();
                 $parent[] = array(
                     $fieldMap['id']['api'] => $fieldMap['id']['magento']
                 );

                 $data['parent_categories'] = $parent;
             }

              return $data;
            } else {
                return array();
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Category::getArray()', $e->getMessage(), $e);
            return false;
        }
    }

    /**
     * Convert category array into JSON object
     *
     * @param array $category
     * @return mixed
     */

    public function getJson( $category = null )
    {
        if ($category) {
            return json_encode( $this->getArray( $category ) );
        } else {
            return false;
        }

    }

    /**
     * Create array of category historical data
     *
     * @param string $filter
     * @return array
     */

    public function getHistoricalData( $filter = null )
    {
        try {
            $lastId = isset($filter['last_id']) ? (is_numeric($filter['last_id']) ?  $filter['last_id'] : null): null;
            $startDate = isset($filter['start_date']) ? $filter['start_date'] : null;
            $endDate = isset($filter['end_date']) ? $filter['end_date'] : null;
            $storeIds = isset($filter['store_ids']) ? $filter['store_ids'] : null;
            $storeId = 0;

            // To do: Fix hard coding of store id for all categories
            if($storeIds) {
                $storeId = reset($storeIds);
            }

            $data = array();

            $categories = Mage::getModel('catalog/category')
                ->getCollection()
                ->addAttributeToSelect('name');

            if ( $lastId ) {
                $categories->addAttributeToFilter('entity_id', array('lteq' => $lastId));
            }

            if ( $startDate ) {
                $categories->addAttributeToFilter('created_at', array('gteq' => $startDate));
            }

            if ( $endDate ) {
                $categories->addAttributeToFilter('created_at', array('lteq' => $endDate));
            }

            foreach($categories as $category) {
                $data[] = array(
                    'type_id' => Jirafe_Analytics_Model_Data_Type::CATEGORY,
                    'store_id' => $storeId,
                    'json' => $this->getJson( $category )
                );
            }
            return $data;
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Category::getHistoricalData()', $e->getMessage(), $e);
            return false;
        }
    }
}
