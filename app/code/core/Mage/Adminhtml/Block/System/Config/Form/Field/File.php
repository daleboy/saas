<?php
/**
 * File config field renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Mio Core Team <developer@ioe9.com>
 */
class Mage_Adminhtml_Block_System_Config_Form_Field_File extends Varien_Data_Form_Element_File
{
    /**
     * Get element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = parent::getElementHtml();
        $html .= $this->_getDeleteCheckbox();
        return $html;
    }

    /**
     * Get html for additional delete checkbox field
     *
     * @return string
     */
    protected function _getDeleteCheckbox()
    {
        $html = '';
        if ((string)$this->getValue()) {
            $label = Mage::helper('adminhtml')->__('Delete File');
            $html .= '<div>'.$this->getValue().' ';
            $html .= '<input type="checkbox" name="'.parent::getName().'[delete]" value="1" class="checkbox" id="'.$this->getHtmlId().'_delete"'.($this->getDisabled() ? ' disabled="disabled"': '').'/>';
            $html .= '<label for="'.$this->getHtmlId().'_delete"'.($this->getDisabled() ? ' class="disabled"' : '').'> '.$label.'</label>';
            $html .= '<input type="hidden" name="'.parent::getName().'[value]" value="'.$this->getValue().'" />';
            $html .= '</div>';
        }
        return $html;
    }
}
