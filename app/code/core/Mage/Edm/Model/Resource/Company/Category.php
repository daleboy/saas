<?php
class Mage_Edm_Model_Resource_Company_Category extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('edm/company_category', 'id');
    }



}
