<?php
/**
 *
 * @category    Mage
 * @package     Mage_Cms
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Cms Adminhtml Template Filter Model
 *
 * @category    Mage
 * @package     Mage_Cms
 * @author      Mio Core Team <developer@ioe9.com>
 */
class Mage_Cms_Model_Adminhtml_Template_Filter extends Mage_Cms_Model_Template_Filter
{
    /**
     * Retrieve media file local path directive
     *
     * @internal to avoid usage of urls at functions sensitive to "allow_url_fopen" php setting at GD2 adapter
     *
     * @param array $construction
     *
     * @return string
     *
     * @throws Mage_Core_Exception
     */
    public function mediaDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        if (!isset($params['url'])) {
            Mage::throwException('Undefined url parameter for media directive.');
        }

        return Mage::getBaseDir('media') . DS . $params['url'];
    }
}
