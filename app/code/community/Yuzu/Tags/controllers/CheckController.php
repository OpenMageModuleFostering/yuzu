<?php

/**
 * Check controller
 *
 * @category    Yuzu
 * @package     Yuzu_Tags
 * @version     1.0.0
 * @copyright   Copyright (c) 2015 Yuzu (http://www.yuzu.co)
 * @author      Jonathan Martin <jonathan@yuzu.co>
 */
class Yuzu_Tags_CheckController extends Mage_Core_Controller_Front_Action
{
    public function statusAction()
    {
        $merchantKey = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/merchant_key');
        $secretKey = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/secret_key');
        $enabled = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/enable');

        $response = array(
            'mage_version' => Mage::getVersion(),
            'php_version' => phpversion(),
            'merchant_key' => ($merchantKey) ? true : false,
            'secret_key' => ($secretKey) ? true : false,
            'enabled' => ($enabled) ? true : false
        );

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }
}