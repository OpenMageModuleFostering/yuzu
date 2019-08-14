<?php

/**
 * Check controller
 *
 * @category    Yuzu
 * @package     Yuzu_Tags
 * @copyright   Copyright (c) 2015 Yuzu (http://www.yuzu.co)
 * @author      Jonathan Martin <jonathan@yuzu.co>
 */
class Yuzu_Tags_CheckController extends Mage_Core_Controller_Front_Action
{
    public function statusAction()
    {
        $config = (array) Mage::getConfig()->getModuleConfig("Yuzu_Tags");
        $merchantKey = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/merchant_key');
        $secretKey = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/secret_key');
        $enabled = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/enable');
        $inCheckout = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/offers/in_checkout');
        $inOrderDetail = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/offers/in_order_detail');
        $emailOrder = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/offers/in_email_confirmation_order');
        $emailInvoice = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/offers/in_email_invoice');
        $emailShipment = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/offers/in_email_shipment');

        $response = array(
            'version' => $config['version'],
            'date' => time(),
            'timezone' => date_default_timezone_get(),
            'mage_version' => Mage::getVersion(),
            'php_version' => phpversion(),
            'merchant_key' => ($merchantKey) ? true : false,
            'secret_key' => ($secretKey) ? true : false,
            'enabled' => ($enabled) ? true : false,
            'in_checkout' => ($inCheckout) ? true : false,
			'in_order_detail' => ($inOrderDetail) ? true : false,
			'email_order' => ($emailOrder) ? true : false,
			'email_invoice' => ($emailInvoice) ? true : false,
			'email_shipment' => ($emailShipment) ? true : false,
        );

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }
}