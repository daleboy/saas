<?php
class Mage_Cms_Model_Wysiwyg_Config extends Varien_Object
{
    /**
     * Wysiwyg behaviour: enabled
     */
    const WYSIWYG_ENABLED = 'enabled';

    /**
     * Wysiwyg behaviour: hidden
     */
    const WYSIWYG_HIDDEN = 'hidden';

    /**
     * Wysiwyg behaviour: disabled
     */
    const WYSIWYG_DISABLED = 'disabled';

    /**
     * constant for image directory
     */
    const IMAGE_DIRECTORY = 'wysiwyg';

    /**
     * Path to skin image placeholder file
     */
    const WYSIWYG_SKIN_IMAGE_PLACEHOLDER_FILE = 'images/wysiwyg/skin_image.png';

    /**
     * Return Wysiwyg config as Varien_Object
     *
     * Config options description:
     *
     * enabled:                 Enabled Visual Editor or not
     * hidden:                  Show Visual Editor on page load or not
     * use_container:           Wrap Editor contents into div or not
     * no_display:              Hide Editor container or not (related to use_container)
     * translator:              Helper to translate phrases in lib
     * files_browser_*:         Files Browser (media, images) settings
     * encode_directives:       Encode template directives with JS or not
     *
     * @param $data array       constructor params to override default config values
     * @return Varien_Object
     */
    public function getConfig($data = array())
    {
        $config = new Varien_Object();

        $config->setData(array(
            'enabled'                       => $this->isEnabled(),
            'hidden'                        => $this->isHidden(),
            'use_container'                 => false,
            'add_variables'                 => true,
            'add_widgets'                   => true,
            'no_display'                    => false,
            'translator'                    => Mage::helper('cms'),
            'encode_directives'             => true,
            'directives_url'                => Mage::getSingleton('adminhtml/url')->getUrl('*/cms_wysiwyg/directive'),
            'popup_css'                     =>
                Mage::getBaseUrl('js').'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/dialog.css',
            'content_css'                   =>
                Mage::getBaseUrl('js').'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css',
            'width'                         => '100%',
            'plugins'                       => array()
        ));

        $config->setData('directives_url_quoted', preg_quote($config->getData('directives_url')));

        if (Mage::getSingleton('admin/session')->isAllowed('cms/media_gallery')) {
            $config->addData(array(
                'add_images'               => true,
                'files_browser_window_url' => Mage::getSingleton('adminhtml/url')->getUrl('*/cms_wysiwyg_images/index'),
                'files_browser_window_width'
                    => (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_width'),
                'files_browser_window_height'
                    => (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_height'),
            ));
        }

        if (is_array($data)) {
            $config->addData($data);
        }

        Mage::dispatchEvent('cms_wysiwyg_config_prepare', array('config' => $config));

        return $config;
    }

    /**
     * Return the URL for skin image placeholder
     *
     * @return string
     */
    public function getSkinImagePlaceholderUrl()
    {
        return Mage::getDesign()->getSkinUrl(self::WYSIWYG_SKIN_IMAGE_PLACEHOLDER_FILE);
    }

    /**
     * Return the path to the skin image placeholder
     *
     * @return string
     */
    public function getSkinImagePlaceholderPath()
    {
        return Mage::getModel('core/design_package')->getSkinBaseDir(array('_area' => 'adminhtml')) . DS .
            self::WYSIWYG_SKIN_IMAGE_PLACEHOLDER_FILE;
    }

    /**
     * Check whether Wysiwyg is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return self::WYSIWYG_ENABLED;
    }

    /**
     * Check whether Wysiwyg is loaded on demand or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
