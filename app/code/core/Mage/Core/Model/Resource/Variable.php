<?php
/**
 * Custom variable resource model
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Mio Core Team <developer@ioe9.com>
 */
class Mage_Core_Model_Resource_Variable extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_init('core/variable', 'variable_id');
    }

    /**
     * Load variable by code
     *
     * @param Mage_Core_Model_Variable $object
     * @param string $code
     * @return Mage_Core_Model_Resource_Variable
     */
    public function loadByCode(Mage_Core_Model_Variable $object, $code)
    {
        if ($result = $this->getVariableByCode($code, true, $object->getStoreId())) {
            $object->setData($result);
        }
        return $this;
    }

    /**
     * Retrieve variable data by code
     *
     * @param string $code
     * @param boolean $withValue
     * @param integer $storeId
     * @return array
     */
    public function getVariableByCode($code, $withValue = false, $storeId = 0)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where($this->getMainTable() . '.code = ?', $code);
        if ($withValue) {
            $this->_addValueToSelect($select, $storeId);
        }
        return $this->_getReadAdapter()->fetchRow($select);
    }

    /**
     * Perform actions after object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Variable
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);
        if ($object->getUseDefaultValue()) {
            /*
             * remove store value
             */
            $this->_getWriteAdapter()->delete(
                $this->getTable('core/variable_value'), array(
                    'variable_id = ?' => $object->getId()
            ));
        } else {
            $data =  array(
                'variable_id' => $object->getId(),
                'plain_value' => $object->getPlainValue(),
                'html_value'  => $object->getHtmlValue()
            );
            $data = $this->_prepareDataForTable(new Varien_Object($data), $this->getTable('core/variable_value'));
            $this->_getWriteAdapter()->insertOnDuplicate(
                $this->getTable('core/variable_value'),
                $data,
                array('plain_value', 'html_value')
            );
        }
        return $this;
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $object
     * @return Varien_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $this->_addValueToSelect($select, $object->getStoreId());
        return $select;
    }

    /**
     * Add variable store and default value to select
     *
     * @param Varien_Db_Select $select
     * @param integer $storeId
     * @return Mage_Core_Model_Resource_Variable
     */
    protected function _addValueToSelect(Varien_Db_Select $select, $storeId = Mage_Core_Model_App::ADMIN_STORE_ID)
    {
        $adapter = $this->_getReadAdapter();
        $ifNullPlainValue = $adapter->getCheckSql('store.plain_value IS NULL', 'def.plain_value', 'store.plain_value');
        $ifNullHtmlValue  = $adapter->getCheckSql('store.html_value IS NULL', 'def.html_value', 'store.html_value');

        $select->joinLeft(
                array('def' => $this->getTable('core/variable_value')),
                'def.variable_id = '.$this->getMainTable().'.variable_id ',
                array())
            ->joinLeft(
                array('store' => $this->getTable('core/variable_value')),
                'store.variable_id = def.variable_id ',
                array())
            ->columns(array(
                'plain_value'       => $ifNullPlainValue,
                'html_value'        => $ifNullHtmlValue,
                'store_plain_value' => 'store.plain_value',
                'store_html_value'  => 'store.html_value'
            ));

        return $this;
    }
}
