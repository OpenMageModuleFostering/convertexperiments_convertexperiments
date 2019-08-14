<?php
/**
 * Creates the select values in the configuration by pulling all catalog product
 * attributes from the Magento DB.
 *
 * @package ConvertExperiments
 * @subpackage ConvertExperiments
 * @version 1.0.7
 * @author Robert Henderson
 */
class ConvertExperiments_ConvertExperiments_Model_Entity_Attribute extends Mage_Eav_Model_Entity_Attribute {
    /**
     * Options select for different types of custom text attributes
     *
     * @return array
     */
    public function toOptionArray() {
        $eavEntityType = Mage::getModel('eav/entity_type')->loadByCode('catalog_product');
        $attributes = $this->getCollection()->setEntityTypeFilter($eavEntityType->getId());
        $excluded = array('sku','cost','price','name','weight');
        $attributeOptions = array(
            array('value' => 0, 'label' => Mage::helper('convert_experiments')->__('')),
        );
        $i = 1;
        foreach($attributes as $attribute) {
            $code = $attribute['attribute_code'];
            $label = $attribute['frontend_label'];
            if(!in_array($code,$excluded)){
                if($label !== null && $label !== ""){
                    array_push($attributeOptions, array('value' => $code, 'label' => Mage::helper('convert_experiments')->__($label)));
                    $i++;
                }
            }
        }
        uasort($attributeOptions, 'blueAcornConvertExperimentsModelEntityAttributeCompareFunction');
        return $attributeOptions;
    }
}

function blueAcornConvertExperimentsModelEntityAttributeCompareFunction($a, $b)
{
    if ($a['label'] == $b['label']) {
        return 0;
    }
    return ($a['label'] < $b['label']) ? -1 : 1;
}
