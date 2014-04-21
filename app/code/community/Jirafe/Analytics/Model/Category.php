<?php

/**
 * Category Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */
class Jirafe_Analytics_Model_Category extends Jirafe_Analytics_Model_Abstract implements Jirafe_Analytics_Model_Pagable
{
    protected $_fields = array('id', 'name', 'change_date', 'create_date');

    /**
     * Create category array of data required by Jirafe API
     *
     * @param Mage_Catalog_Model_Category $category
     * @return mixed
     */
    public function getArray($magentoCategory=null)
    {
        try {
            if ($magentoCategory) {

             /**
              * Get field map array
              */
             $fieldMap = $this->_getFieldMap('category', $magentoCategory);
             $data = $this->_mapFields($fieldMap, $this->_fields);

             if ($parent = Mage::getModel('catalog/category')->load($magentoCategory->getParentId())) {
                 $fieldMap = $this->_getFieldMap('category', $parent);
                 $parent = array(
                    array($fieldMap['id']['api'] => $fieldMap['id']['magento'])
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
    public function getJson($category = null)
    {
        if ($category) {
            return json_encode($this->getArray($category));
        } else {
            return false;
        }
    }

    public function getDataType() {
        return Jirafe_Analytics_Model_Data_Type::CATEGORY;
    }

   /**
     * Create array of category historical data
     *
     * @param string $filter
     * @return Zend_Paginator
     */
    public function getPaginator($websiteId, $lastId = null)
    {
        if ($websiteId) {
            $_rootCatIds = array();
            $_storeGroups = Mage::getModel('core/store_group')->getCollection()
                    ->addWebsiteFilter($websiteId);
            foreach ($_storeGroups as $_storeGroup) {
                $_rootCatIds[] = $_storeGroup->getRootCategoryId();
            }
        }
        $categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->setOrder('entity_id');

        if ($lastId) {
            $categories->addAttributeToFilter('entity_id', array('gt' => $lastId));
        }
        if ($_rootCatIds) {
            $pathFilter = array();
            foreach($_rootCatIds as $_rootCatId) {
                $pathFilter[] = Mage_Catalog_Model_Category::TREE_ROOT_ID.'/'.$_rootCatId.'/';
            }

            $categories->addPathsFilter($pathFilter);
        }
        return Zend_Paginator::factory($categories->getIterator());
    }
}

