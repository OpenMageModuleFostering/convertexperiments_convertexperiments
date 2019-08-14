<?php
/**
 * TODO: Fix undefined index: value on line 23 when changing the value of an attribute on the store scope after default values are set.
 * Makes sure the user doesn't select the same attribute twice.
 *
 * @package ConvertExperiments
 * @subpackage ConvertExperiments
 * @version 1.0.7
 * @author Robert Henderson
 */
class ConvertExperiments_ConvertExperiments_Model_DisallowDuplicates extends Mage_Core_Model_Config_Data {
    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave() {
        $groups = $this->getData('groups');
        if(isset($groups['product_settings']['fields'])) {
            $fields = $groups['product_settings']['fields'];
            unset($fields['explanation']);

            foreach($fields as $searchKey => $searchField) {
                $searchField = $searchField['value'];
                foreach ($fields as $key => $field) {
                    $field = $field['value'];
                    if($field && $field !== "" && $searchField && $searchField !== "") {
                        if ($searchKey != $key && $field == $searchField) {
                            Mage::throwException("Settings have not been saved. An attribute has been chosen twice. Please select only unique attributes.");
                        }
                    }
                }
            }
        }

        return parent::_beforeSave();
    }
}
