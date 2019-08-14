<?php
/**
 * Sending a warning after save to tell the user they have not actually enabled
 * the module so no settings will appear on the frontend.
 *
 * @package ConvertExperiments
 * @subpackage ConvertExperiments
 * @version 1.0.7
 * @author Robert Henderson
 */
class ConvertExperiments_ConvertExperiments_Model_Enabledcheck extends Mage_Core_Model_Config_Data {
    /**
     * Check to see if user has enabled the configuration, if not, warn them they have not
     *
     * @return Mage_Core_Model_Abstract|void
     */
    protected function _afterSave() {
        $enabled = $this->getValue();
        if($enabled == null || $enabled == 0) {
            return Mage::getSingleton('core/session')->addNotice(Mage::helper('adminhtml')->__('You have not enabled the Blue Acorn Convert Experiments configuration. No data will appear on the frontend. Please enable to activate frontend Javascript output.'));
        }
        return parent::_afterSave();
    }
}
