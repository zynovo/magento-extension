<?php
class Jirafe_Analytics_Block_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $website_code = $this->getRequest()->getParam('website');
        $this->setElement($element);
        $url  = $this->getUrl('jirafe_analytics/adminhtml_historical/check', array('website_code' => $website_code));

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Sync')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}
?>
