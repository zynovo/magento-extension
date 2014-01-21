<?php

/**
 * Customer Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Customer extends Jirafe_Analytics_Model_Abstract implements Jirafe_Analytics_Model_Pagable
{
    protected $_fields = array('id', 'email', 'first_name', 'last_name', 'active_flag', 'change_date', 'create_date', 'marketing_opt_in');

    private function _makeAddress($magentoCustomer)
    {
        $data = array();
        if ($addressId = $magentoCustomer->getDefaultBilling()) {
            $address = Mage::getModel('customer/address')->load($addressId);
            foreach ($address->getData() as $key => $value) {
                if (!array_key_exists ($key, $magentoCustomer)) {
                    $data[$key] = $value;
                }
            }
        }
        return $data;
    }

    private function _makeExtraFields($magentoCustomer, $fieldMap)
    {
        $data = array();
        if ($magentoCustomer->getDefaultBilling()) {
            $data[$fieldMap['company']['api'] ] = $fieldMap['company']['magento'];
            $data[$fieldMap['phone']['api'] ] = $fieldMap['phone']['magento'];
        }
        return $data;
    }

    /**
     * Create user admin array of data required by Jirafe API
     *
     * @param Mage_Customer_Model_Customer $magentoCustomer
     * @return mixed
     */
    public function getArray($magentoCustomer, $includeCookies=false)
    {
        try {
            $data = array_merge(
                $magentoCustomer->getData(),
                $this->_makeAddress($magentoCustomer)
            );

            $fieldMap = $this->_getFieldMap('customer', $data);
            $extraFields = $this->_makeExtraFields($magentoCustomer, $fieldMap);
            $marketingOptIn = Mage::getModel('newsletter/subscriber')
                                  ->load($magentoCustomer->getEmail(), 'subscriber_email')
                                  ->getSubscriberStatus();

            $data = array_merge(
                $extraFields,
                $this->_mapFields($fieldMap, $this->_fields),
                array(
                    'marketing_opt_in' => $marketingOptIn ? true : false,
                    'name' => $fieldMap['first_name']['magento'] . ' ' . $fieldMap['last_name']['magento']
                )
            );

            if ($this->getDefaultBilling()) {
                $data[$fieldMap['phone']['api']] = $fieldMap['phone']['magento'];
                $data[$fieldMap['company']['api']] = $fieldMap['company']['magento'];
            }
            if ($includeCookies) {
                $data['cookies'] = $this->_getCookies();
            }

            return $data;
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', __METHOD__, $e->getMessage(), $e);
            return false;
        }
    }

    /**
     * Convert customer array into JSON object
     *
     * @param array $customer
     * @return mixed
     */
    public function getJson($magentoCustomer=null, $isVisit=false)
    {
        if ($magentoCustomer) {
            return json_encode($this->getArray($magentoCustomer, $isVisit));
        } else {
            return false;
        }
    }

    public function getDataType() {
        return Jirafe_Analytics_Model_Data_Type::CUSTOMER;
    }

    /**
     * Create array of customer historical data
     *
     * @param string $filter
     * @return Zend_Paginator
     */
    public function getPaginator($websiteId, $lastId = null)
    {
        $customers = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            ->addAttributeToFilter('website_id', array('eq' => $websiteId));

        $customers->getSelect()->order('entity_id ASC');
        if ($lastId) {
            $customers->addAttributeToFilter('entity_id', array('gt' => $lastId));
        }

        return Zend_Paginator::factory($customers->getIterator());
    }

    /**
     * Get customer array for beacon api javascript
     *
     * @return array
     **/
    public function getCustomer()
    {
        return $this->_getCustomer();

    }
}
