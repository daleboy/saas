<?php
class Mage_Edm_Model_Resource_Company_Keyword_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('edm/company_keyword');
    }
}
