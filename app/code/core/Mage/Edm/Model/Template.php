<?php
class Mage_Edm_Model_Template extends Mage_Core_Model_Abstract
{
	const MEDIA_IMAGE_PREFIX = 'template';
    
    protected function _construct()
    {
        $this->_init('edm/template');
    }
    
    public function getImageUrl() {
    	$urlSuffix = $this->getImage();
    	return $this->getImageUrlPrefix().$urlSuffix;
    }
	
	public function getImageUrlPrefix() {
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).self::MEDIA_IMAGE_PREFIX.'/';
	}
}
