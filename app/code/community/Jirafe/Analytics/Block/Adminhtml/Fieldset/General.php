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
    protected $_supportedVersion = null;
    protected $_supportedMagentoVersion = null;

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
            if (!$this->_isSupportedVersion()) {
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
        if (!$this->_isSupportedVersion()) {
            $_helper = $this->helper('jirafe_analytics');
            $_content = '';
            if (!$this->_supportedPhpVersion()) {
                $_content .= 'PHP 5.3';
            }
            if (!$this->_supportMagentoVersion()) {
                $_content .= ($_content ? ' and ' : '') . 'Magento ';
                $_content .= ($this->_isEnterpriseVersion() ? '1.12' : '1.7');
            }
            $message = '<span class="error">' . $_helper->__('Jirafe Analytics requires at least ') .
                $_helper->__($_content) . $_helper->__(', please update before you start.').'</span>';
        }
        return $element->getComment()
            ? '<div class="comment">' . $element->getComment() . $message . '</div>'
            : '';
    }

    /**
     * Check whether it is enterprise version or not
     *
     * @return bool
     */
    protected function _isEnterpriseVersion()
    {
        $_moduleLocation = Mage::getRoot() . DS . 'etc' . DS .'modules' . DS .'Enterprise_Enterprise.xml';
        return file_exists($_moduleLocation);
    }

    /**
     * Check whether it is php version supported or not
     *
     * @return bool
     */
    protected function _supportedPhpVersion()
    {
        return version_compare(phpversion(), '5.3.0', '>=');
    }

    /**
     * Check whether it is magento version supported or not
     *
     * @return bool
     */
    protected function _supportMagentoVersion()
    {
        if (is_null($this->_supportedMagentoVersion)) {
            $this->_supportedMagentoVersion = true;
            $_magentoVersion = Mage::getVersion();
            if ($this->_isEnterpriseVersion()) {
                if (version_compare($_magentoVersion, 1.12,'<')) {
                    //if EE version is less than 1.12
                    $this->_supportedMagentoVersion = false;
                }
            } elseif (version_compare($_magentoVersion, 1.7,'<')) {
                //if CE version is less than 1.7
                $this->_supportedMagentoVersion = false;
            }

        }
        return $this->_supportedMagentoVersion;
    }


    /**
     * Check whether the php version and Magento version is within support
     *
     * @return bool
     */
    protected function _isSupportedVersion()
    {
        if (is_null($this->_supportedVersion)) {
            $this->_supportedVersion = true;
            if (!$this->_supportedPhpVersion()) {
                $this->_supportedVersion = false;
            }
            if (!$this->_supportMagentoVersion()) {
                $this->_supportedVersion = false;
            }

        }
        return $this->_supportedVersion;
    }
}