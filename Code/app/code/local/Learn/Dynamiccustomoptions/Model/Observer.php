<?php

class Learn_Dynamiccustomoptions_Model_Observer
{

	const DYNAMIC_ATTRIBUTE = "dynamic_attribute";
	
	/*
	 * Add the custom option.
     */
	public function addCustomOptions(Varien_Event_Observer $observer) {
		$product = $observer->getProduct();
		$product_id = $product->getId();
		$oneTimeSetupFee = $observer->getProduct()->getData(self::DYNAMIC_ATTRIBUTE);
		$customFeeFormat = Mage::helper('core')->currency($oneTimeSetupFee, true, false);
		$label = $this->getCustomAttributeLabel(self::DYNAMIC_ATTRIBUTE); // Label
		if($oneTimeSetupFee) {
			$action = Mage::app()->getFrontController()->getAction();
			if($action->getFullActionName() == 'checkout_cart_add' || $action->getFullActionName() == 'arajaxcart_index_add') {
				$value = $label." - <b>". $customFeeFormat ."</b>"; // Value
				$options = array($label => $value);
				if ($options) {
		        	$additionalOptions = array();
		        	if ($additionalOption = $product->getCustomOption('additional_options')) {
		            	$additionalOptions = (array) unserialize($additionalOption->getValue());
		        	}
		        	foreach ($options as $key => $value) {
		            	$additionalOptions[] = array(
		                	'label' => $key,
		                	'value' => $value,
		            	);
		        	}
		        	$observer->getProduct()->addCustomOption('additional_options', serialize($additionalOptions));
		    	}
			}
		}
	}
	
	/*
	 * Convert Quote Item To Order Item.
     */
	public function convertQuoteItemToOrderItem(Varien_Event_Observer $observer) {
		$quoteItem = $observer->getItem();
		if ($additionalOptions = $quoteItem->getOptionByCode('additional_options')) {
			$orderItem = $observer->getOrderItem();
			$options = $orderItem->getProductOptions();
			$options['additional_options'] = unserialize($additionalOptions->getValue());		
			$orderItem->setProductOptions($options);
		}
	}

	/*
	 * Get the "one time setup fee" value.
     */
	public function getProductOneTimeSetupFee($product_id) {
    	$product = Mage::getSingleton('catalog/product')->load($product_id);
    	return $product->getData('one_time_setup_fee');

    }

	/*
	 * Get the "one time setup fee" lable name.
     */
    protected function getCustomAttributeLabel($attribute_code) {
		$attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);
		return $attribute->getFrontend()->getLabel();
    }
}