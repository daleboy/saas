<?php


class Mage_Adminhtml_Block_Cache_Notifications extends Mage_Adminhtml_Block_Template
{
    /**
     * Get array of cache types which require data refresh
     *
     * @return array
     */
    public function getCacheTypesForRefresh()
    {
        $invalidatedTypes = Mage::app()->getCacheInstance()->getInvalidatedTypes();
        $res = array();
        foreach ($invalidatedTypes as $type) {
            $res[] = $type->getCacheType();
        }
        return $res;
    }

    /**
     * Get index management url
     *
     * @return string
     */
    public function getManageUrl()
    {
        return $this->getUrl('adminhtml/cache');
    }

    /**
     * ACL validation before html generation
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::getSingleton('admin/session')->isAllowed('system/cache')) {
            return parent::_toHtml();
        }
        return '';
    }
}
