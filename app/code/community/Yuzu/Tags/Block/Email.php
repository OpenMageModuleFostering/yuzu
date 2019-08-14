<?php

/**
 * Abstract block
 *
 * @category    Yuzu
 * @package     Yuzu_Tags
 * @copyright   Copyright (c) 2015 Yuzu (http://www.yuzu.co)
 * @author      Jonathan Martin <jonathan@yuzu.co>
 */
class Yuzu_Tags_Block_Email extends Yuzu_Tags_Block_Abstract
{
    public function getMaxOffers()
    {
        return Mage::helper('yuzu_tags')->getConfig('yuzu_tags/offers/nb');
    }

    public function getBaseUrl()
    {
        $baseUrl = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/tag_url_collect');
        if (preg_match('#^\/\/#', $baseUrl)) {
            $baseUrl = 'http:'.$baseUrl;
        }

        return $baseUrl;
    }
}