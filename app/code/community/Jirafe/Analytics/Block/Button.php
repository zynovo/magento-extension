<?php
class Jirafe_Analytics_Block_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $website_code = $this->getRequest()->getParam('website');
        $disableButton = true;
        if ($website_code) {
            $website = Mage::app()->getWebsite($website_code);
            $enabled = $website->getConfig('jirafe_analytics/general/enabled');
            if ($enabled) {
                $disableButton = !Mage::getModel('jirafe_analytics/curl')->checkCredentials($website->getId());
                //if cron is disabled, disabled the historical sync button
                if(!$disableButton && !$this->helper('jirafe_analytics')->hasCron())
                {
                    $disableButton = true;
                }
            }
        }
        $this->setElement($element);
        $url  = $this->getUrl('jirafe_analytics/adminhtml_historical/check', array('website_code' => $website_code));

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Sync')
                    ->setDisabled($disableButton)
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}