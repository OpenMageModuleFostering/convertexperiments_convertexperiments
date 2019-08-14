<?php
/**
 * Methods that retrieve saved data in the module's system configuration
 *
 * @package ConvertExperiments
 * @subpackage ConvertExperiments
 * @version 1.0.7
 * @author Robert Henderson
 */
class ConvertExperiments_ConvertExperiments_Helper_Data extends Mage_Core_Helper_Abstract {
    const CONFIG_PATH = 'convert_experiments/';
    protected $config = null;
    protected $defaultGroup = "settings";
    protected $storedConfigs = array();

    /**
     * Config meta method for getting gts config
     *
     * @param <type> $code
     * @return <type>
     */
    public function getConfig($code = null, $group = null) {
        if ($code !== null) {
            if ($group == null) {
                $group = $this->defaultGroup;
            }
            if (!isset($this->storedConfigs[$code])) {
                $this->config = Mage::getStoreConfig(self::CONFIG_PATH . "$group/$code");
            }else{
                $this->config = $this->storedConfigs[$code];
            }
        }
        return $this->config;
    }

    /**
     * Is Convert Experiments enabled
     *
     * @return boolean
     */
    public function isEnabled() {
        return $this->getConfig('enabled', 'settings');
    }

    /**
     * Sitewide JS from system config
     *
     * @return string
     */
    public function getConvertProjectCode() {
        if($this->isEnabled() == 1) {
            return $this->getConfig('convert_experiments_project_code', 'settings');
        }else{
            return null;
        }
    }

    /**
     * Get first custom product attribute from config
     *
     * @return int
     */
    public function getCustomAttrOne() {
        if($this->isEnabled() == 1) {
            return $this->getConfig('convert_experiments_custom_text_one', 'product_settings');
        }else{
            return null;
        }
    }

    /**
     * Get second custom product attribute from config
     *
     * @return int
     */
    public function getCustomAttrTwo() {
        if($this->isEnabled() == 1) {
            return $this->getConfig('convert_experiments_custom_text_two', 'product_settings');
        }else{
            return null;
        }
    }

    /**
     * Get third custom product attribute from config (number)
     *
     * @return int
     */
    public function getNumberAttrOne() {
        if($this->isEnabled() == 1) {
            return $this->getConfig('convert_experiments_custom_number_one', 'product_settings');
        }else{
            return null;
        }
    }

    /**
     * Get fourth custom product attribute from config (number)
     *
     * @return int
     */
    public function getNumberAttrTwo() {
        if($this->isEnabled() == 1) {
            return $this->getConfig('convert_experiments_custom_number_two', 'product_settings');
        }else{
            return null;
        }
    }
}
