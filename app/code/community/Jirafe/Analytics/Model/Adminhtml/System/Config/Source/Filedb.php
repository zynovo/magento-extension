<?php

/**
 * Adminhtml System Config Source Singlemulti Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Adminhtml_System_Config_Source_Filedb
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
             array('value' => 'db', 'label'=>Mage::helper('jirafe_analytics')->__('database')),
             array('value' => 'file', 'label'=>Mage::helper('jirafe_analytics')->__('file system')),
           
        );
    }

}
