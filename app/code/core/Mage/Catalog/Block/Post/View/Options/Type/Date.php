<?php
/**
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Post options text type block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Mio Core Team <developer@ioe9.com>
 */
class Mage_Catalog_Block_Post_View_Options_Type_Date extends Mage_Catalog_Block_Post_View_Options_Abstract
{

    /**
     * Fill date and time options with leading zeros or not
     *
     * @var boolean
     */
    protected $_fillLeadingZeros = true;

    protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setCanLoadCalendarJs(true);
        }
        return parent::_prepareLayout();
    }

    /**
     * Use JS calendar settings
     *
     * @return boolean
     */
    public function useCalendar()
    {
        return Mage::getSingleton('catalog/post_option_type_date')->useCalendar();
    }

    /**
     * Date input
     *
     * @return string Formatted Html
     */
    public function getDateHtml()
    {
        if ($this->useCalendar()) {
            return $this->getCalendarDateHtml();
        } else {
            return $this->getDropDownsDateHtml();
        }
    }

    /**
     * JS Calendar html
     *
     * @return string Formatted Html
     */
    public function getCalendarDateHtml()
    {
        $option = $this->getOption();
        $value = $this->getPost()->getPreconfiguredValues()->getData('options/' . $option->getId() . '/date');

        //$require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';

        $yearStart = Mage::getSingleton('catalog/post_option_type_date')->getYearStart();
        $yearEnd = Mage::getSingleton('catalog/post_option_type_date')->getYearEnd();

        $calendar = $this->getLayout()
            ->createBlock('core/html_date')
            ->setId('options_'.$this->getOption()->getId().'_date')
            ->setName('options['.$this->getOption()->getId().'][date]')
            ->setClass('post-custom-option datetime-picker input-text' . $require)
            ->setImage($this->getSkinUrl('images/calendar.gif'))
            ->setFormat('Y-m-d H:i:s')
            ->setValue($value)
            ->setYearsRange('[' . $yearStart . ', ' . $yearEnd . ']');
        if (!$this->getSkipJsReloadPrice()) {
            $calendar->setExtraParams('onchange="opConfig.reloadPrice()"');
        }

        return $calendar->getHtml();
    }

    /**
     * Date (dd/mm/yyyy) html drop-downs
     *
     * @return string Formatted Html
     */
    public function getDropDownsDateHtml()
    {
        $fieldsSeparator = '&nbsp;';
        $fieldsOrder = Mage::getSingleton('catalog/post_option_type_date')->getConfigData('date_fields_order');
        $fieldsOrder = str_replace(',', $fieldsSeparator, $fieldsOrder);

        $monthsHtml = $this->_getSelectFromToHtml('month', 1, 12);
        $daysHtml = $this->_getSelectFromToHtml('day', 1, 31);

        $yearStart = Mage::getSingleton('catalog/post_option_type_date')->getYearStart();
        $yearEnd = Mage::getSingleton('catalog/post_option_type_date')->getYearEnd();
        $yearsHtml = $this->_getSelectFromToHtml('year', $yearStart, $yearEnd);

        $translations = array(
            'd' => $daysHtml,
            'm' => $monthsHtml,
            'y' => $yearsHtml
        );
        return strtr($fieldsOrder, $translations);
    }

    /**
     * Time (hh:mm am/pm) html drop-downs
     *
     * @return string Formatted Html
     */
    public function getTimeHtml()
    {
        if (Mage::getSingleton('catalog/post_option_type_date')->is24hTimeFormat()) {
            $hourStart = 0;
            $hourEnd = 23;
            $dayPartHtml = '';
        } else {
            $hourStart = 1;
            $hourEnd = 12;
            $dayPartHtml = $this->_getHtmlSelect('day_part')
                ->setOptions(array(
                    'am' => Mage::helper('catalog')->__('AM'),
                    'pm' => Mage::helper('catalog')->__('PM')
                ))
                ->getHtml();
        }
        $hoursHtml = $this->_getSelectFromToHtml('hour', $hourStart, $hourEnd);
        $minutesHtml = $this->_getSelectFromToHtml('minute', 0, 59);

        return $hoursHtml . '&nbsp;<b>:</b>&nbsp;' . $minutesHtml . '&nbsp;' . $dayPartHtml;
    }

    /**
     * Return drop-down html with range of values
     *
     * @param string $name Id/name of html select element
     * @param int $from  Start position
     * @param int $to    End position
     * @param int $value Value selected
     * @return string Formatted Html
     */
    protected function _getSelectFromToHtml($name, $from, $to, $value = null)
    {
        $options = array(
            array('value' => '', 'label' => '-')
        );
        for ($i = $from; $i <= $to; $i++) {
            $options[] = array('value' => $i, 'label' => $this->_getValueWithLeadingZeros($i));
        }
        return $this->_getHtmlSelect($name, $value)
            ->setOptions($options)
            ->getHtml();
    }

    /**
     * HTML select element
     *
     * @param string $name Id/name of html select element
     * @return Mage_Core_Block_Html_Select
     */
    protected function _getHtmlSelect($name, $value = null)
    {
        $option = $this->getOption();

        // $require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setId('options_' . $this->getOption()->getId() . '_' . $name)
            ->setClass('post-custom-option datetime-picker' . $require)
            ->setExtraParams()
            ->setName('options[' . $option->getId() . '][' . $name . ']');

        $extraParams = 'style="width:auto"';
        if (!$this->getSkipJsReloadPrice()) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }
        $select->setExtraParams($extraParams);

        if (is_null($value)) {
            $value = $this->getPost()->getPreconfiguredValues()->getData('options/' . $option->getId() . '/' . $name);
        }
        if (!is_null($value)) {
            $select->setValue($value);
        }

        return $select;
    }

    /**
     * Add Leading Zeros to number less than 10
     *
     * @param int
     * @return string
     */
    protected function _getValueWithLeadingZeros($value)
    {
        if (!$this->_fillLeadingZeros) {
            return $value;
        }
        return $value < 10 ? '0'.$value : $value;
    }
}