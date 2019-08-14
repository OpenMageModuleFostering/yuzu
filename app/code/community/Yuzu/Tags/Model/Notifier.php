<?php

/**
 * Notifier Model
 *
 * @category    Yuzu
 * @package     Yuzu_Tags
 * @copyright   Copyright (c) 2015 Yuzu (http://www.yuzu.co)
 * @author      Jonathan Martin <jonathan@yuzu.co>
 */
class Yuzu_Tags_Model_Notifier
{
    public function preDispatch(Varien_Event_Observer $observer)
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn())
        {
            $feedModel  = Mage::getModel('yuzu_tags/feed');
            $feedModel->checkUpdate();
        }
    }
}