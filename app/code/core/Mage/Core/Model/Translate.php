<?php
class Mage_Core_Model_Translate
{
    const CSV_SEPARATOR     = ',';
    const SCOPE_SEPARATOR   = '::';
    const CACHE_TAG         = 'translate';

    const CONFIG_KEY_AREA   = 'area';
    const CONFIG_KEY_LOCALE = 'locale';
    const CONFIG_KEY_STORE  = 'store';
    const CONFIG_KEY_DESIGN_PACKAGE = 'package';
    const CONFIG_KEY_DESIGN_THEME   = 'theme';

    /**
     * Default translation string
     */
    const DEFAULT_STRING = 'Translate String';

    /**
     * Locale name
     *
     * @var string
     */
    protected $_locale;

    /**
     * Translation object
     *
     * @var Zend_Translate_Adapter
     */
    protected $_translate;

    /**
     * Translator configuration array
     *
     * @var array
     */
    protected $_config;

    protected $_useCache = true;

    /**
     * Cache identifier
     *
     * @var string
     */
    protected $_cacheId;

    /**
     * Translation data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Translation data for data scope (per module)
     *
     * @var array
     */
    protected $_dataScope;

    /**
     * Configuration flag to enable inline translations
     *
     * @var boolean
     */
    protected $_translateInline;

    /**
     * Configuration flag to local enable inline translations
     *
     * @var boolean
     */
    protected $_canUseInline = true;

    public function __construct()
    {
    }

    /**
     * Initialization translation data
     *
     * @param   string $area
     * @return  Mage_Core_Model_Translate
     */
    public function init($area, $forceReload = false)
    {
        $this->setConfig(array(self::CONFIG_KEY_AREA=>$area));

        $this->_translateInline = Mage::getSingleton('core/translate_inline')
            ->isAllowed($area=='adminhtml' ? 'admin' : null);

        if (!$forceReload) {
            if ($this->_canUseCache()) {
                $this->_data = $this->_loadCache();
                if ($this->_data !== false) {
                    return $this;
                }
            }
            Mage::app()->removeCache($this->getCacheId());
        }

        $this->_data = array();



        if (!$forceReload && $this->_canUseCache()) {
            $this->_saveCache();
        }

        return $this;
    }

    /**
     * Retrieve modules configuration by translation
     *
     * @return Mage_Core_Model_Config_Element
     */
    public function getModulesConfig()
    {
        if (!Mage::getConfig()->getNode($this->getConfig(self::CONFIG_KEY_AREA).'/translate/modules')) {
            return array();
        }

        $config = Mage::getConfig()->getNode($this->getConfig(self::CONFIG_KEY_AREA).'/translate/modules')->children();
        if (!$config) {
            return array();
        }
        return $config;
    }

    /**
     * Initialize configuration
     *
     * @param   array $config
     * @return  Mage_Core_Model_Translate
     */
    public function setConfig($config)
    {
        $this->_config = $config;
        if (!isset($this->_config[self::CONFIG_KEY_LOCALE])) {
            $this->_config[self::CONFIG_KEY_LOCALE] = $this->getLocale();
        }
        if (!isset($this->_config[self::CONFIG_KEY_STORE])) {
            $this->_config[self::CONFIG_KEY_STORE] = Mage::app()->getStore()->getId();
        }
        if (!isset($this->_config[self::CONFIG_KEY_DESIGN_PACKAGE])) {
            $this->_config[self::CONFIG_KEY_DESIGN_PACKAGE] = Mage::getDesign()->getPackageName();
        }
        if (!isset($this->_config[self::CONFIG_KEY_DESIGN_THEME])) {
            $this->_config[self::CONFIG_KEY_DESIGN_THEME] = Mage::getDesign()->getTheme('locale');
        }
        return $this;
    }

    /**
     * Retrieve config value by key
     *
     * @param   string $key
     * @return  mixed
     */
    public function getConfig($key)
    {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }
        return null;
    }


    /**
     * Adding translation data
     *
     * @param array $data
     * @param string $scope
     * @return Mage_Core_Model_Translate
     */
    protected function _addData($data, $scope, $forceReload=false)
    {
        foreach ($data as $key => $value) {
            if ($key === $value) {
                continue;
            }
            $key    = $this->_prepareDataString($key);
            $value  = $this->_prepareDataString($value);
            if ($scope && isset($this->_dataScope[$key]) && !$forceReload ) {
                /**
                 * Checking previos value
                 */
                $scopeKey = $this->_dataScope[$key] . self::SCOPE_SEPARATOR . $key;
                if (!isset($this->_data[$scopeKey])) {
                    if (isset($this->_data[$key])) {
                        $this->_data[$scopeKey] = $this->_data[$key];
                        /**
                         * Not allow use translation not related to module
                         */
                        if (Mage::getIsDeveloperMode()) {
                            unset($this->_data[$key]);
                        }
                    }
                }
                $scopeKey = $scope . self::SCOPE_SEPARATOR . $key;
                $this->_data[$scopeKey] = $value;
            }
            else {
                $this->_data[$key]     = $value;
                $this->_dataScope[$key]= $scope;
            }
        }
        return $this;
    }

    protected function _prepareDataString($string)
    {
        return str_replace('""', '"', $string);
    }



    /**
     * Loading current store translation from DB
     *
     * @return Mage_Core_Model_Translate
     */
    protected function _loadDbTranslation($forceReload = false)
    {
        $arr = $this->getResource()->getTranslationArray(null, $this->getLocale());
        $this->_addData($arr, $this->getConfig(self::CONFIG_KEY_STORE), $forceReload);
        return $this;
    }

    /**
     * Retrieve data from file
     *
     * @param   string $file
     * @return  array
     */
    protected function _getFileData($file)
    {
        $data = array();
        if (file_exists($file)) {
            $parser = new Varien_File_Csv();
            $parser->setDelimiter(self::CSV_SEPARATOR);
            $data = $parser->getDataPairs($file);
        }
        return $data;
    }

    /**
     * Retrieve translation data
     *
     * @return array
     */
    public function getData()
    {
        if (is_null($this->_data)) {
            return array();
            //Mage::throwException('Translation data is not initialized. Please contact developers.');
        }
        return $this->_data;
    }

    /**
     * Retrieve locale
     *
     * @return string
     */
    public function getLocale()
    {
        if (is_null($this->_locale)) {
            $this->_locale ="zh_CN";
        }
        return $this->_locale;
    }

    public function setLocale( $locale )
    {
        $this->_locale = $locale;
        return $this;
    }

    /**
     * Retrieve DB resource model
     *
     * @return unknown
     */
    public function getResource()
    {
        return Mage::getResourceSingleton('core/translate');
    }

    /**
     * Retrieve translation object
     *
     * @return Zend_Translate_Adapter
     */
    public function getTranslate()
    {
        if (is_null($this->_translate)) {
            $this->_translate = new Zend_Translate('array', $this->getData(), $this->getLocale());
        }
        return $this->_translate;
    }

    /**
     * Translate
     *
     * @param   array $args
     * @return  string
     */
    public function translate($args)
    {
        $text = array_shift($args);

        if (is_string($text) && ''==$text
            || is_null($text)
            || is_bool($text) && false===$text
            || is_object($text) && ''==$text->getText()) {
            return '';
        }
        if ($text instanceof Mage_Core_Model_Translate_Expr) {
            $code = $text->getCode(self::SCOPE_SEPARATOR);
            $module = $text->getModule();
            $text = $text->getText();
            $translated = $this->_getTranslatedString($text, $code);
        }
        else {
            if (!empty($_REQUEST['theme'])) {
                $module = 'frontend/default/'.$_REQUEST['theme'];
            } else {
                $module = 'frontend/default/default';
            }
            $code = $module.self::SCOPE_SEPARATOR.$text;
            $translated = $this->_getTranslatedString($text, $code);
        }

        //array_unshift($args, $translated);
        //$result = @call_user_func_array('sprintf', $args);

        $result = @vsprintf($translated, $args);
        if ($result === false) {
            $result = $translated;
        }

        if ($this->_translateInline && $this->getTranslateInline()) {
            if (strpos($result, '{{{')===false || strpos($result, '}}}')===false || strpos($result, '}}{{')===false) {
                $result = '{{{'.$result.'}}{{'.$translated.'}}{{'.$text.'}}{{'.$module.'}}}';
            }
        }

        return $result;
    }

    /**
     * Set Translate inline mode
     *
     * @param bool $flag
     * @return Mage_Core_Model_Translate
     */
    public function setTranslateInline($flag=null)
    {
        $this->_canUseInline = (bool) $flag;
        return $this;
    }

    /**
     * Retrieve active translate mode
     *
     * @return bool
     */
    public function getTranslateInline()
    {
        return $this->_canUseInline;
    }

    /**
     * Retrive translated template file
     *
     * @param string $file
     * @param string $type
     * @param string $localeCode
     * @return string
     */
    public function getTemplateFile($file, $type)
    {
        $filePath = Mage::getBaseDir('design')  . DS . DS . 'template' . DS . $type . DS . $file;
        if (!file_exists($filePath)) { // If no template specified for this locale, use store default
            $filePath = Mage::getBaseDir('design') . DS . 'template' . DS . $type . DS . $file;
        }

        if (!file_exists($filePath)) {  // If no template specified as  store default locale, use en_US
            $filePath = Mage::getBaseDir('design') . DS . 'template' . DS . $type . DS . $file;
        }

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->open(array('path' => Mage::getBaseDir('design')));

        return (string) $ioAdapter->read($filePath);
    }

    /**
     * Retrieve cache identifier
     *
     * @return string
     */
    public function getCacheId()
    {
        if (is_null($this->_cacheId)) {
            $this->_cacheId = 'translate';
            if (isset($this->_config[self::CONFIG_KEY_LOCALE])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_LOCALE];
            }
            if (isset($this->_config[self::CONFIG_KEY_AREA])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_AREA];
            }
            if (isset($this->_config[self::CONFIG_KEY_STORE])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_STORE];
            }
            if (isset($this->_config[self::CONFIG_KEY_DESIGN_PACKAGE])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_DESIGN_PACKAGE];
            }
            if (isset($this->_config[self::CONFIG_KEY_DESIGN_THEME])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_DESIGN_THEME];
            }
        }
        return $this->_cacheId;
    }

    /**
     * Loading data cache
     *
     * @param   string $area
     * @return  array | false
     */
    protected function _loadCache()
    {
        if (!$this->_canUseCache()) {
            return false;
        }
        $data = Mage::app()->loadCache($this->getCacheId());
        $data = unserialize($data);
        return $data;
    }

    /**
     * Saving data cache
     *
     * @param   string $area
     * @return  Mage_Core_Model_Translate
     */
    protected function _saveCache()
    {
        if (!$this->_canUseCache()) {
            return $this;
        }
        Mage::app()->saveCache(serialize($this->getData()), $this->getCacheId(), array(self::CACHE_TAG), null);
        return $this;
    }

    /**
     * Check cache usage availability
     *
     * @return bool
     */
    protected function _canUseCache()
    {
        return Mage::app()->useCache('translate');
    }

    /**
     * Return translated string from text.
     *
     * @param string $text
     * @param string $code
     * @return string
     */
    protected function _getTranslatedString($text, $code)
    {
        $translated = '';
        if (array_key_exists($code, $this->getData())) {
            $translated = $this->_data[$code];
        }
        elseif (array_key_exists($text, $this->getData())) {
            $translated = $this->_data[$text];
        }
        else {
            $translated = $text;
        }
        return $translated;
    }
}
