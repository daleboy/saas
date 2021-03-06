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
class Mage_Catalog_Block_Post_View_Options_Type_File
    extends Mage_Catalog_Block_Post_View_Options_Abstract
{
    /**
     * Returns info of file
     *
     * @return string
     */
    public function getFileInfo()
    {
        $info = $this->getPost()->getPreconfiguredValues()->getData('options/' . $this->getOption()->getId());
        if (empty($info)) {
            $info = new Varien_Object();
        } else if (is_array($info)) {
            $info = new Varien_Object($info);
        }
        return $info;
    }
}
