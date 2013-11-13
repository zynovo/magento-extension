<?php

/**
 * Employee Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Employee extends Jirafe_Analytics_Model_Abstract
{

    /**
     * Create user admin array of data required by Jirafe API
     *
     * @param Mage_Admin_Model_User  $user
     * @param string                 $userId
     * @return mixed
     */
    
    public function getArray( $user = null, $userId = null )
    {
        try {
            
            if ( $userId && !$user ) {
                $user = Mage::getSingleton('admin/user')->load( $userId );
            }
            
            if ( $user ) {
                /**
                 * Get field map array
                 */
                
                $fieldMap = $this->_getFieldMap( 'employee', $user->getData() );
                
                return array(
                    $fieldMap['id']['api'] => $fieldMap['id']['magento'],
                    $fieldMap['active_flag']['api'] => $fieldMap['active_flag']['magento'] ,
                    $fieldMap['change_date']['api'] => $fieldMap['change_date']['magento'],
                    $fieldMap['create_date']['api'] => $fieldMap['create_date']['magento'],
                    $fieldMap['email']['api'] => $fieldMap['email']['magento'],
                    $fieldMap['first_name']['api'] => $fieldMap['first_name']['magento'],
                    $fieldMap['last_name']['api'] => $fieldMap['last_name']['magento']
                );
            } else {
               return array();
            }
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log( 'ERROR', 'Jirafe_Analytics_Model_Employee::getArray()', $e->getMessage(), $e);
            return false;
        }
    }
    
     /**
     * Convert employee array into JSON object
     * 
     * @param string $user
     * @param string $userId
     * @return mixed
     */
    
    public function getJson( $user = null, $userId = null )
    {
        if ($userId || $user) {
            return json_encode( $this->getArray( $user, $userId ) );
        } else {
            return false;
        }
        
    }
    
    /**
     * Create array of employee historical data
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    
    public function getHistoricalData( $startDate = null, $endDate = null )
    {
        try {
            $data = array();
            $employees = Mage::getSingleton('admin/user')->getCollection();
            
            if ( $startDate && $endDate) {
                $employees->getSelect()->where("created BETWEEN '$startDate' AND '$endDate'");
            } else if ( $startDate ) {
                $employees->getSelect()->where("created >= '$startDate'");
            } else if ( $endDate ) {
                $employees->getSelect()->where("created <= '$endDate'");
            }
            
            foreach($employees as $employee) {
                 $data[] = array(
                    'type_id' => Jirafe_Analytics_Model_Data_Type::EMPLOYEE,
                    'store_id' => Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID,
                    'json' => $this->getJson( $employee, null )
                );
            }
            
            return $data;
        } catch (Exception $e) {
            Mage::helper('jirafe_analytics')->log('ERROR', 'Jirafe_Analytics_Model_Employee::getHistoricalData()', $e->getMessage(), $e);
            return false;
        }
    }
}