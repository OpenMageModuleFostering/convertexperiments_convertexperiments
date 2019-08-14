<?php
/**
 * Statically adds integer specific values to number array.
 *
 * @package ConvertExperiments
 * @subpackage
 * @version 1.0.7
 * @author Robert Henderson
 */
class ConvertExperiments_ConvertExperiments_Model_Entity_Attribute_Integer {
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 0, 'label' => Mage::helper('convert_experiments')->__('')),
            array('value' => 'news_from_date', 'label' => Mage::helper('convert_experiments')->__('Age (days)')),
            array('value' => 'cost', 'label' => Mage::helper('convert_experiments')->__('Cost')),
            array('value' => 'inventory', 'label' => Mage::helper('convert_experiments')->__('Stock Remaining')),
            array('value' => 'weight', 'label' => Mage::helper('convert_experiments')->__('Weight')),
        );
    }
}
