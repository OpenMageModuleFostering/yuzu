<?php

/**
 * Tags block
 *
 * @category    Yuzu
 * @package     Yuzu_Tags
 * @version     1.0.0
 * @copyright   Copyright (c) 2015 Yuzu (http://www.yuzu.co)
 * @author      Jonathan Martin <jonathan@yuzu.co>
 */
class Yuzu_Tags_Block_Tags extends Yuzu_Tags_Block_Abstract
{
    private $mca;

    public function __construct()
    {
        $this->mca = $this->getRequest()->getModuleName()."-".$this->getRequest()->getControllerName()."-".$this->getRequest()->getActionName();
    }

    public function getuser()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::getSingleton('customer/session')->getCustomer();
        }

        return false;
    }

    public function getEvent()
    {
        return Mage::getModel('yuzu_tags/event')->resolveEventName($this->mca);
    }

    public function hasEventsAction()
    {
        switch ($this->getEvent()) {
            case 'product' :
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    public function getContext()
    {
        $locale = Mage::app()->getLocale()->getLocaleCode() ? Mage::app()->getLocale()->getLocaleCode() : 'en_US';
        $locale = explode('_', $locale);

        $context = array(
            'country'    => $locale[1],
            'language'    => $locale[0],
            'currency'  => Mage::app()->getStore()->getCurrentCurrencyCode() ? Mage::app()->getStore()->getCurrentCurrencyCode() : "",
        );

        return json_encode($context);
    }

    public function getDatas()
    {
        $datas = Mage::getModel('yuzu_tags/data')->getDatas($this->mca, $this->getLayout());

        return json_encode($datas);
    }
}