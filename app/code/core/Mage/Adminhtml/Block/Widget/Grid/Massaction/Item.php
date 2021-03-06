<?php
/**
 * Grid widget massaction single action item
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Mio Core Team <developer@ioe9.com>
 */
class Mage_Adminhtml_Block_Widget_Grid_Massaction_Item extends Mage_Adminhtml_Block_Widget
{

    protected $_massaction = null;

    /**
     * Set parent massaction block
     *
     * @param  Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract $massaction
     * @return Mage_Adminhtml_Block_Widget_Grid_Massaction_Item
     */
    public function setMassaction($massaction)
    {
        $this->_massaction = $massaction;
        return $this;
    }

    /**
     * Retrive parent massaction block
     *
     * @return Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract
     */
    public function getMassaction()
    {
        return $this->_massaction;
    }

    /**
     * Set additional action block for this item
     *
     * @param string|Mage_Core_Block_Abstract $block
     * @return Mage_Adminhtml_Block_Widget_Grid_Massaction_Item
     */
    public function setAdditionalActionBlock($block)
    {
        if(is_string($block)) {
            $block = $this->getLayout()->createBlock($block);
        } elseif (is_array($block)) {
            $block = $this->_createFromConfig($block);
        } elseif(!($block instanceof Mage_Core_Block_Abstract)) {
            Mage::throwException('Unknown block type');
        }

        $this->setChild('additional_action', $block);
        return $this;
    }

    protected function _createFromConfig(array $config)
    {
        $type = isset($config['type']) ? $config['type'] : 'default';
        switch($type) {
            default:
                $blockClass = 'adminhtml/widget_grid_massaction_item_additional_default';
                break;
        }

        $block = $this->getLayout()->createBlock($blockClass);
        $block->createFromConfiguration(isset($config['type']) ? $config['config'] : $config);
        return $block;
    }

    /**
     * Retrive additional action block for this item
     *
     * @return Mage_Core_Block_Abstract
     */
    public function getAdditionalActionBlock()
    {
        return $this->getChild('additional_action');
    }

    /**
     * Retrive additional action block HTML for this item
     *
     * @return string
     */
    public function getAdditionalActionBlockHtml()
    {
        return $this->getChildHtml('additional_action');
    }

}
