<?php
class Mage_Material_Block_Adminhtml_Template extends Mage_Adminhtml_Block_Template
{
	protected $_pageSize = 20;
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('material/template.phtml');
    }
    
    protected function _prepareLayout(){
        parent::_prepareLayout();
        $tag= trim($this->getRequest()->getParam('tag',null));
        $categoryId = trim($this->getRequest()->getParam('cid',null));
        $collection = Mage::getModel('material/template')->getCollection();
        if ($tag) {
        	$tagModel = Mage::getModel('material/template_tag')->loadByName($tag);
        	if ($tagModel->getId()) {
        		$collection->getSelect()
        			->join(array('r'=>'material_template_tag_relate'),
							'main_table.template_id=r.template_id and tag_id='.$tagModel->getId(),array());
        	}
        }
        if ($categoryId) {

    		$collection->getSelect()
        			->join(array('r'=>'material_template_category_relate'),
							'main_table.template_id=r.template_id and category_id='.$categoryId,array());
        	
        }
        if (Mage::registry('use_filter_my')) {
        	$collection->addFieldToFilter('company_id',Mage::registry('current_company')->getId());
        } else {
        	$collection->addFieldToFilter('company_id',0);
        }
        $this->setCollection($collection);
        $pager = $this->getLayout()->createBlock('page/html_pager')
                ->setTemplate('material/html/pager.phtml')
                ->setLimit($this->_pageSize)
            ->setCollection($collection);
        $this->setChild('pager', $pager);

        return $this;
         
    }
}