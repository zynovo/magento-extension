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
}