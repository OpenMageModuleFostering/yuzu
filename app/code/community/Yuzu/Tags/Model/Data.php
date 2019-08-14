<?php

/**
 * Data Model
 *
 * @category    Yuzu
 * @package     Yuzu_Tags
 * @copyright   Copyright (c) 2015 Yuzu (http://www.yuzu.co)
 * @author      Jonathan Martin <jonathan@yuzu.co>
 */
class Yuzu_Tags_Model_Data extends Mage_Core_Model_Abstract
{
    private $data;
    private $layout;

    public function getDatas($mca, $layout)
    {
        $this->layout = $layout;
	try {

        switch ($mca) {
            case 'cms-index-index': $this->pageHome();
                                    break;
            case 'catalog-product-view': $this->pageProduct();
                                         break;
            case 'catalog-category-view': $this->pageCategory();
                                         break;
            case 'catalogsearch-result-index': $this->pageSearch();
                                         break;
            case 'checkout-cart-index': $this->pageCart();
                                         break;
            default: return false;
            break;
        }
	} catch (Exception $e) {
            return false;
        }

        return $this->data;
    }

    private function pageHome()
    {
    }

    private function pageProduct()
    {
        $product = Mage::registry('product');

        if ($product) {
            $this->data['id'] = $product->getId();
        }
    }

    private function pageCategory()
    {
        $category = Mage::registry('current_category');

        if ($category) {
            $this->data['id'] = $category->getId();
        }
    }

    private function pageSearch()
    {
        $this->data['query'] = Mage::helper('catalogsearch')->getEscapedQueryText() ? Mage::helper('catalogsearch')->getEscapedQueryText() : "";
        $this->data['count'] = count(Mage::helper('catalogsearch')->getSuggestCollection()) ? count(Mage::helper('catalogsearch')->getSuggestCollection()) : "";
    }

    private function pageCart()
    {
        //cart data
        $cart = Mage::getModel('checkout/cart')->getQuote();
        $yuCart = array(
            'id'          => $cart->getEntityId(),
            'total'       => number_format($cart->getGrandTotal(), 2, ".", ""),
            'coupon'    => $cart->getCouponCode()
        );
        $this->data = $yuCart;

        //Products data
        $yuItems = array();
        foreach ($cart->getAllItems() as $item) {

            if ($item->getParentItemId()) {
                continue;
            }

            $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            $itemOptions = array();
            if(isset($options['attributes_info']))
            {
                foreach ($options['attributes_info'] as $option)
                {
                    $itemOptions[strtolower($option['label'])] = $option['value'];
                }
            }

            $yuItems[] = array(
                'productId'    => $item->getProductId(),
                'quantity' => number_format($item->getQty(), 0, ".", ""),
                'price'    => number_format($item->getRowTotal(), 2, ".", ""),
                'discount'     => number_format($item->getDiscountAmount(), 2, ".", ""),
                'options'     => $itemOptions
            );
        }
        $this->data['lines'] = $yuItems;
    }
}