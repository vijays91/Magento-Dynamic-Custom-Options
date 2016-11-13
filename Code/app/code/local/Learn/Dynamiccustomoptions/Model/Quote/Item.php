<?php
    /*
	 * Ref : http://ramkumar.tomrain.com/the-best-way-to-add-dynamic-custom-options-to-magentos-cart-items/
	 * Ref : http://stackoverflow.com/questions/9412074/magento-quote-order-product-item-attribute-based-on-user-input/9496266#9496266/
     */


class Learn_Dynamiccustomoptions_Model_Quote_Item extends Mage_Sales_Model_Quote_Item
{
    protected $customRowTotalPrice = null;
	
	const DYNAMIC_ATTRIBUTE = "dynamic_attribute";

    public function setCustomRowTotalPrice($price) {
        $this->customRowTotalPrice = $price;
    }

    public function calcRowTotal()
    {
		if ($this->customRowTotalPrice !== null) {
			$this->setRowTotal($this->getStore()->roundPrice($this->customRowTotalPrice));
			$this->setBaseRowTotal($this->getStore()->roundPrice($this->customRowTotalPrice));
			return $this;
		}
		$qty = $this->getTotalQty();
		$total = $this->getStore()->roundPrice($this->getCalculationPriceOriginal()) * $qty;
		
		/*- Dynamic Attribute -*/
		echo $label = $this->getCustomAttributeLabel(self::DYNAMIC_ATTRIBUTE); // Label
		$_dynamicAttributePrice = $this->getDynamicAttributeValue($this->getProductId());
		if($_dynamicAttributePrice > 0) {
			$total = $total + $_dynamicAttributePrice;
		}
		
		$baseTotal = $this->getStore()->roundPrice($this->getBaseCalculationPriceOriginal()) * $qty;
		$this->setRowTotal($this->getStore()->roundPrice($total));
		$this->setBaseRowTotal($this->getStore()->roundPrice($baseTotal));
		return $this;
    }

    /*
	 * Get the "dynamic_attribute" value.
     */
	protected function getDynamicAttributeValue($product_id) {
    	$product = Mage::getModel('catalog/product')->load($product_id);
    	return $product->getData(self::DYNAMIC_ATTRIBUTE);
    }
	
	/*
	 * Get the "one time setup fee" lable name.
     */
    protected function getCustomAttributeLabel($attribute_code) {
		$attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);
		return $attribute->getFrontend()->getLabel();
    }
}