<?php

/**
 * Adminhtml Attempt Controller
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Naqeeb Memon (naqeeb.memon@jirafe.com)
 */

class Jirafe_Analytics_Adminhtml_ResetController extends Mage_Adminhtml_Controller_Action
{
    private function deleteConfig()
    {
        Mage::helper('jirafe_analytics')->log('INFO', __METHOD__, 'Deleting Configs...');
        $p = Mage::getModel("core/config")->getTablePrefix();
        $t = $p."core_config_data";
        $w = Mage::getSingleton('core/resource')->getConnection('core_write');
        $w->query("DELETE FROM $t WHERE path LIKE '%jirafe_analytics/last_id%';");
        $w->query("DELETE FROM $t WHERE path LIKE '%jirafe_analytics/historicalpull/active%';");
        Mage::helper('jirafe_analytics')->log('INFO', __METHOD__, 'Done Deleting Configs.');
    }

    private function truncateModels()
    {
        try {
            Mage::helper('jirafe_analytics')->log('INFO', __METHOD__, 'Truncating Models...');
            Mage::getConfig()->saveConfig('jirafe_analytics/general/enabled', 0);
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();

            $truncatable = array('batch', 'batch_data', 'data', 'data_attempt', 'data_error');
            foreach ($truncatable as $name) {
                Mage::helper('jirafe_analytics')->log('INFO', __METHOD__, "'Truncating '$name'...'");
                $model = Mage::getResourceModel("jirafe_analytics/$name");
                $w = Mage::getSingleton('core/resource')->getConnection('core_write');
                $w->query('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE '.$model->getMainTable().'; SET FOREIGN_KEY_CHECKS = 1;');
                Mage::helper('jirafe_analytics')->log('INFO', __METHOD__, "Done Truncating '$name'.");
            }
        } catch (Exception $e) {
            $this->log('ERROR', __METHOD__, $e->getMessage(), $e);
        }

        Mage::getConfig()->saveConfig('jirafe_analytics/general/enabled', 1);
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
        Mage::helper('jirafe_analytics')->log('INFO', __METHOD__, 'Done Truncating Models.');
    }

    /**
     * Action of the emergency reset button.
     * Truncate all Jirafe tables, with the exception of the log table.
     * Delete all jirafe config records from the magento key value store.
     *
     * This allows historical pushs to be re-started if something should go "horriably wrong."
     *
     * @return void
     */
    public function resetAction()
    {
        Mage::helper('jirafe_analytics')->log('INFO', __METHOD__, 'Emergency Reset Button Pressed...');
        $this->deleteConfig();
        $this->truncateModels();
        Mage::helper('jirafe_analytics')->log('INFO', __METHOD__, 'Done Reseting.');
        $this->_redirectUrl(($this->_getRefererUrl()));
    }
}
