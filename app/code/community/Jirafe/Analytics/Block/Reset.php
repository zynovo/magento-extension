<?php
class Jirafe_Analytics_Block_Reset extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $website_code = $this->getRequest()->getParam('website');
        $this->setElement($element);
        $url = $this->getUrl('jirafe_analytics/adminhtml_reset/reset');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Emergency Reset!')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}
