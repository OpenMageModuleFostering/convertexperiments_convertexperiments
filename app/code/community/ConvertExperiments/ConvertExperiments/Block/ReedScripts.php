<?php
/**
 * Block that outputs the REED variable definitions above the main Convert.com
 * script contained within one script tag.
 *
 * @package ConvertExperiments
 * @subpackage ConvertExperiments
 * @version 1.0.7
 * @author Robert Henderson
 */
class ConvertExperiments_ConvertExperiments_Block_ReedScripts extends Mage_Core_Block_Template {

    const NEW_LINE = "\n";
    const TABBED_SPACE = "\t";

    protected $_helper = null;
    protected $_product = null;
    protected $_customAttrName = null;
    protected $_category = null;
    protected $_cms = null;

    /**
     * Get module helper
     *
     * @return Mage_Core_Helper_Abstract
     */
    protected function _helper() {
        return $this->_helper = Mage::helper('convert_experiments');
    }

    /**
     * Get current product
     *
     * @return mixed
     */
    protected function _getProduct() {
        return $this->_product = Mage::registry('current_product');
    }

    /**
     * Get current category
     *
     * @return mixed
     */
    protected function _getCategory() {
        return $this->_category = Mage::registry('current_category');
    }

    /**
     * Check if current page is CMS
     *
     * @return bool
     */
    protected function _isCms() {
        return $this->_cms = $this->getRequest()->getRequestedRouteName() == 'cms';
    }

    /**
     * Create one script tag for all REED variables to be inserted to at the
     * top of each page
     *
     * @return string
     */
    public function wrapReedVariables($variables) {
        $product = $this->_getProduct();
        $html = '<!-- Convert Experiments Page Script Variables -->' . self::NEW_LINE;
        $html .= '<script type="text/javascript">' . self::NEW_LINE;
        $html .= '//<![CDATA[' . self::NEW_LINE;
        $html .= $variables;
        if($product) {
            $html .= $this->_getProductVariables();
        }
        if($this->_getCategory() && !$product) {
            $html .= $this->_getCategoryVariables();
        }
        if($this->_isCms()) {
            $html .= $this->_getCmsVariables();
        }
        $html .= '//]]>' . self::NEW_LINE;
        $html .= '</script>' . self::NEW_LINE;
        $html .= '<!-- End Convert Experiments Page Script Variables -->' . self::NEW_LINE;
        return $html;
    }

    /**
     * Outputs a script after the opening <body> tag on each product page that
     * defines the product SKU, name, price (lowest number if bundled or grouped),
     * and custom attribute values if current product has data assigned to that
     * attribute.
     *
     * @return string
     */
    protected function _getProductVariables() {
        $productVariables = "";
        if($this->_helper()->isEnabled() == 1) {
            $product = $this->_getProduct();
            if($product) {
                $productSku = $this->_editString($product->getSku());
                $productName = $this->_editString($product->getName());
                $productType = $this->_editString($product->getTypeId());
                $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
                $attributeSetModel->load($product->getAttributeSetId());
                $attributeSetName  = $this->_editString($attributeSetModel->getAttributeSetName());
                $productPrice = round($product->getMinimalPrice(),2);
                if($productType == "grouped") {
                    $aProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
                    $prices = array();
                    foreach ($aProductIds as $ids) {
                        foreach ($ids as $id) {
                            $aProduct = Mage::getSingleton('catalog/product')->load($id);
                            array_push($prices, $aProduct->getData('minimal_price'));
                        }
                    }
                    sort($prices, SORT_NUMERIC);
                    $productPrice = round($prices[0],2);
                }
                elseif($productType == "bundle") {
                    $priceModel  = $product->getPriceModel();
                    $productPrice = $priceModel->getTotalPrices($product, null, null, false);
                    $productPrice = $productPrice[0];
                }
                $this->_editString($productPrice);

                $productVariables = self::TABBED_SPACE . 'var REED_page_type = "Product' . ';' . $productType . ';' . $attributeSetName . '";' . self::NEW_LINE;
                $productVariables .= self::TABBED_SPACE . 'var REED_product_sku = "' . $productSku . '";' . self::NEW_LINE;
                $productVariables .= self::TABBED_SPACE . 'var REED_product_name = "' . $productName . ';' . $productType . ';' . $attributeSetName . '";' . self::NEW_LINE;
                $productVariables .= self::TABBED_SPACE . 'var REED_product_price = "' . $productPrice . '";' . self::NEW_LINE;
                //Get the value from the custom attributes defined if they exist for current
                //product
                $customAttrOne = $this->_helper()->getCustomAttrOne();
                $customAttrTwo = $this->_helper()->getCustomAttrTwo();
                $numberAttrOne = $this->_helper()->getNumberAttrOne();
                $numberAttrTwo = $this->_helper()->getNumberAttrTwo();
                $attrOneOptionId = $product->getData($customAttrOne);
                $attrTwoOptionId = $product->getData($customAttrTwo);

                if($attrOneOptionId) {
                    $productVariables .= self::TABBED_SPACE . 'var REED_custom_v1 = "' . $this->_editString($this->_getCustomAttributeValue($customAttrOne)) . ';' . $this->_customAttrName($customAttrOne) . '";' . self::NEW_LINE;
                }
                if($attrTwoOptionId) {
                    $productVariables .= self::TABBED_SPACE . 'var REED_custom_v2 = "' . $this->_editString($this->_getCustomAttributeValue($customAttrTwo)) . ';' . $this->_customAttrName($customAttrTwo) . '";' . self::NEW_LINE;
                }
                $calcNumAttrOne = $this->_calcNumberAttr($numberAttrOne);
                $calcNumAttrTwo = $this->_calcNumberAttr($numberAttrTwo);
                if($calcNumAttrOne != 0) {
                    $productVariables .= self::TABBED_SPACE . 'var REED_custom_v3 = "' . $this->_calcNumberAttr($numberAttrOne) . self::NEW_LINE;
                }
                if($calcNumAttrTwo != 0) {
                    $productVariables .= self::TABBED_SPACE . 'var REED_custom_v4 = "' . $this->_calcNumberAttr($numberAttrTwo) . self::NEW_LINE;
                }
            }
        }
        return $productVariables;
    }

    /**
     * Returns the integer and string description of the number attributes selected on product pages
     *
     * @return string
     */
    protected function _calcNumberAttr($numberAttr) {
        $product = $this->_getProduct();
        //If Age is the attribute chosen
        if($numberAttr && $numberAttr == 'news_from_date') {
            $numberAttr = null;
            $newFromDate = $product->getData('created_at');
            $newFromDate = str_replace("-", "", substr($newFromDate, 0, strpos($newFromDate, " ")));
            $today = date('Ymd');
            if(is_numeric($newFromDate)) {
                $numberAttr = $this->_editString(($today - $newFromDate)) . '";';
            }
        }
        //If Cost is the attribute chosen
        if($numberAttr && $numberAttr == 'cost') {
            $numberAttr = null;
            $cost = round($product->getData('cost'),2);
            if(is_numeric($cost)) {
                $numberAttr = $this->_editString($cost) . '";';
            }
        }
        //If Stock Remaining is the Attribute chosen
        if($numberAttr && $numberAttr == 'inventory') {
            $numberAttr = null;
            $qty = round(Mage::getSingleton('cataloginventory/stock_item')->loadByProduct($product)->getQty(),0);
            if(is_numeric($qty)) {
                $numberAttr = $this->_editString($qty) . '";';
            }
        }
        //If Weight is the attribute chosen
        if($numberAttr && $numberAttr == 'weight') {
            $numberAttr = null;
            $weight = round($product->getData('weight'),2);
            if(is_numeric($weight)) {
                $numberAttr = $this->_editString($weight) . '";';
            }
        }
        return $numberAttr;
    }

    /**
     * Find the value of the custom product attributes selected in system config
     * if that are stored as int values
     *
     * @param $attribute
     * @return string
     */
    protected function _getCustomAttributeValue($attribute) {
        $product = $this->_getProduct();
        if(is_numeric($product->getData($attribute))) {
            $attributeText = $product->getAttributeText($attribute);
            if($attributeText) {
                return $attributeText;
            }else{
                return round($product->getData($attribute),2);
            }
        }else{
            return $product->getData($attribute);
        }
    }

    /**
     * Trims each string within the variable definitions to 50 characters or less
     * if the string is more than 50 characters. Also gets rid of characters that 
     * may cause JS errors like ", ', and ;
     *
     * @param $string
     * @return string
     */
    protected function _editString($string) {
        if(strlen($string) > 50) {
            $newString = substr($string,0,50);
        }else{
            $newString = $string;
        }

        $badChars = array("\"","'",";","\n");
        foreach($badChars as $char) {
            if(strstr($newString, $char) !== FALSE){
                $newString = str_replace($badChars,'',$newString);
            }
        }
        return $newString;
    }

    /**
     * Gets the frontend name of the custom attribute code being used
     *
     * @return mixed
     */
    protected function _customAttrName($customAttr) {
        return $this->_customAttrName = $this->_getProduct()->getResource()->getAttribute($customAttr)->getStoreLabel();
    }

    /**
     * Gets the current category we're on and finds out category name, ID, and
     * immediate parent if one exists.
     *
     * @return string
     */
    protected function _getCategoryVariables() {
        $category = $this->_getCategory();
        $categoryVariables = "";
        if($this->_helper()->isEnabled() == 1) {
            if($category && !$this->_getProduct()) {
                $categoryName = $category->getName();
                $categoryId = $category->getId();

                //Retrieve Stores where isset category Path, returns comma separated list
                $pathInStore = $category->getPathInStore();
                //Reverse the list so the current category is at the bottom
                $pathIds = array_reverse(explode(',', $pathInStore));

                $categories = $category->getParentCategories();
                $categoryTree = array();
                foreach ($pathIds as $categoryId) {
                    //Push all category names into $categoryTree array
                    if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                        array_push($categoryTree, $categories[$categoryId]->getName());
                    }
                }
                //See if there are any parent categories by seeing how many keys are stored
                //in the $categoryTree array
                $categoryTreeCount = count(array_keys($categoryTree));
                $parentCategory = '';
                //If the $categoryTree array does have more than 1 key, then pass the value
                //of the second to last key into $parentCategory
                if($categoryTreeCount > 1) {
                    end($categoryTree);
                    $parentCategory =  ';' . prev($categoryTree);
                }

                //Generates package and theme name for category template variable
                $designPackage = Mage::getSingleton('core/design_package');
                $package = $designPackage->getPackageName();
                $theme = $designPackage->getTheme('frontend');
                $categoryTemplate = $package . "/" . $theme;
                $customDesign = $category->getData('custom_design');
                if($customDesign && $category->getData('custom_use_parent_settings') == 0) {
                    $categoryTemplate = $customDesign;
                }

                //Create the convert.com category script:
                $categoryVariables = self::TABBED_SPACE . 'var REED_page_type = "Category;' . $categoryTemplate . '";' . self::NEW_LINE;
                $categoryVariables .= self::TABBED_SPACE . 'var REED_category_name = "' . $categoryName . $parentCategory . '";' . self::NEW_LINE;
                $categoryVariables .= self::TABBED_SPACE . 'var REED_category_id = "' . $categoryId . '";' . self::NEW_LINE;
            }
        }
        return $categoryVariables;
    }

    protected function _getCmsVariables() {
        $cmsVariables = "";
        if($this->_helper()->isEnabled() == 1 && $this->_isCms()) {
            $cmsVariables = self::TABBED_SPACE . 'var REED_page_type = "CMS";' . self::NEW_LINE;
        }
        return $cmsVariables;
    }
}
