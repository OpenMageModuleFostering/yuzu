<?php

/**
 * Event Model
 *
 * @category    Yuzu
 * @package     Yuzu_Tags
 * @copyright   Copyright (c) 2015 Yuzu (http://www.yuzu.co)
 * @author      Jonathan Martin <jonathan@yuzu.co>
 */
class Yuzu_Tags_Model_Event extends Mage_Core_Model_Abstract
{
    public function resolveEventName($mca)
    {
        switch ($mca) {
            case 'cms-index-index': return 'home';
                break;
            case 'catalog-product-view': return 'product';
                break;
            case 'catalog-category-view': return 'category';
                break;
            case 'catalogsearch-result-index': return 'search';
                break;
            case 'checkout-cart-index': return 'cart';
                break;
            default:
                return false;
                break;
        }
    }
}