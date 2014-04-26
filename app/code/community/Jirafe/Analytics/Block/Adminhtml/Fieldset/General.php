<?php

/**
 * Fieldset Renderer for General setting
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 */

class Jirafe_Analytics_Block_Adminhtml_Fieldset_General
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->_getHeaderHtml($element);

        foreach ($element->getSortedElements() as $field) {
            if (!$this->helper('jirafe_analytics')->isSupportedVersion()) {
                $field->setDisabled(true);
                $field->addClass(' disabled ');
            }
            $html.= $field->toHtml();
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * Return header comment part of html for fieldset
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        $message = '';
        if (!$this->helper('jirafe_analytics')->isSupportedVersion()) {
            $message = '<span class="error">' . $this->helper('jirafe_analytics')->getErrorMessageForVersion() .'</span>';
        }
        return $element->getComment()
            ? '<div class="comment">' . $element->getComment() . $message . '</div>'
            : '';
    }

    /**
     * Add conflicts resolution js code to the fieldset
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param bool $tooltipsExist Init tooltips observer or not
     * @return string
     */
    protected function _getExtraJs($element, $tooltipsExist = false)
    {
        $curWebsite = $this->getRequest()->getParam('website');
        $js = '
            var cred_check_url="'. $this->getUrl('jirafe_analytics/adminhtml_system_config', array('website'=>$curWebsite)) .'"
        ';
        $js .= '

            document.observe("dom:loaded", function() {
              configForm.submit= function (url) {
                    if (this.validator && this.validator.validate()) {
                        var general_fields = $("config_edit_form").getElementsBySelector("input", "select");
                        var general_config_fields = [];
                        for(var i=0;i<general_fields.length;i++){
                            if (!general_fields[i].name) {
                                continue;
                            }
                            if (general_fields[i].name.match(/^groups\[general\]\[fields\]\[.+$/i)) {
                                general_config_fields[general_fields[i].name] = general_fields[i].value;
                            }
                        }//end for loop
                        if (general_config_fields) {
                            if (general_config_fields["groups[general][fields][enabled][value]"]!==undefined) {
                                if (general_config_fields["groups[general][fields][enabled][value]"]>0) {
                                    //ajax request to test credentials
                                    new Ajax.Request(
                                        cred_check_url,
                                        {
                                            method:     "POST",
                                            parameters: Form.serialize($("config_edit_form"), true),
                                            onSuccess : function(transport) {
                                                try{
                                                    response = eval("(" + transport.responseText + ")");
                                                } catch (e) {
                                                    response = {};
                                                }
                                                if (response.error_message) {
                                                    alert(response.error_message);
                                                    return false;
                                                }
                                            }
                                        }
                                        );
                                    //end ajax request
                                }
                            }
                        }//end general config fields
                        if (this.isSubmitted) {
                            return false;
                        }
                        this.isSubmitted = true;
                        this._submit();
                    }
                    return false;
                };
            });
        ';
        return parent::_getExtraJs($element, $tooltipsExist) . $this->helper('adminhtml/js')->getScript($js);
    }
}