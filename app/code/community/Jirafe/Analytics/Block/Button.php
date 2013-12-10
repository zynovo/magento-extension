<?php
class Jirafe_Analytics_Block_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        //$url = $this->getUrl('catalog/product'); //
        $url  = $this->getUrl('jirafe_analytics/adminhtml_historical/check');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Run')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}
?>
