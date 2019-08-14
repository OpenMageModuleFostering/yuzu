<?php

/**
 * Abstract block
 *
 * @category    Yuzu
 * @package     Yuzu_Tags
 * @copyright   Copyright (c) 2015 Yuzu (http://www.yuzu.co)
 * @author      Jonathan Martin <jonathan@yuzu.co>
 */
abstract class Yuzu_Tags_Block_Abstract extends Mage_Core_Block_Template
{
    public function isEnabled()
    {
        return Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/enable');
    }

    public function getMerchantKey()
    {
        return Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/merchant_key');
    }

    public function isMonetize()
    {
        return Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/monetize');
    }

    public function isInEmail()
    {
        return Mage::helper('yuzu_tags')->getConfig('yuzu_tags/advanced/in_email');
    }

    public function getApiUrl()
    {
        return Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/tag_url_collect');
    }

    public function readyInEmail()
    {
        if ($this->isEnabled() && $this->getMerchantKey() && $this->isMonetize() && $this->isInEmail()) {
            return true;
        }

        return false;
    }
}