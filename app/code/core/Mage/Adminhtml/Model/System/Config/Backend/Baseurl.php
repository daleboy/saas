<?php



class Mage_Adminhtml_Model_System_Config_Backend_Baseurl extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        $value = $this->getValue();

        if (!preg_match('#^{{((un)?secure_)?base_url}}#', $value)) {
            $value = Mage::helper('core/url')->encodePunycode($value);
            $parsedUrl = parse_url($value);
            if (!isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
                Mage::throwException(Mage::helper('core')->__('The %s you entered is invalid. Please make sure that it follows "http://domain.com/" format.', $this->getFieldConfig()->label));
            }
        }

        $value = rtrim($value,  '/');
        /**
         * If value is special ({{}}) we don't need add slash
         */
        if (!preg_match('#}}$#', $value)) {
            $value.= '/';
        }


        $this->setValue($value);
        return $this;
    }

    /**
     * Clean compiled JS/CSS when updating url configuration settings
     */
    protected function _afterSave()
    {
        if ($this->isValueChanged()) {
            Mage::getModel('core/design_package')->cleanMergedJsCss();
        }
    }

    /**
     * Processing object after load data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        if (!preg_match('#^{{((un)?secure_)?base_url}}#', $value)) {
            $value = Mage::helper('core/url')->decodePunycode($value);
        }
        $this->setValue($value);
        return parent::_afterLoad();
    }
}
