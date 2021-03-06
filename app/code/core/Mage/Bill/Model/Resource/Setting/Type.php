<?php
class Mage_Bill_Model_Resource_Setting_Type extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init("bill/setting_type","type_id");
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getDateCreate()) {
            $object->setDateCreate(Mage::getSingleton("core/date")->gmtDate());
        }
        return parent::_beforeSave($object);
    }

}

