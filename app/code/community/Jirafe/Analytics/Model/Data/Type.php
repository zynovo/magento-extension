<?php

/**
 * Data Type Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Data_Type extends Mage_Core_Model_Abstract
{
    const CART = 1;
    
    const CATEGORY = 2;
    
    const CUSTOMER = 3;
    
    const ORDER = 4;
    
    const PRODUCT = 5;
    
    const EMPLOYEE = 6;
    
    /**
     * Class construction & resource initialization
     */
    protected function _construct()
    {
        $this->_init('jirafe_analytics/data_type');
    }
    
    /**
     * Capture the last id for each data type
     */
    public function captureLastIds()
    {
        try {
            $db = Mage::getSingleton('core/resource')->getConnection('core_read');
            
            foreach ( $this->getCollection() as $type ) {
                $last = $db->query('SELECT MAX(' . $type->getIdField() . ') as last_id FROM ' . $type->getTableName())->fetch();
                if ( !$type->getCapturedDt() ) {
                    $type->setLastId( $last['last_id'] );
                    $type->setCapturedDt( Mage::helper('jirafe_analytics')->getCurrentDt() );
                    $type->save();
                }
            }
        } catch (Exception $e) {
        Mage::helper('jirafe_analytics')->log( 'ERROR', 'Jirafe_Analytics_Model_Data_Historical::captureLastIds()', $e->getMessage(), $e );
        return false;
        }
    }
}