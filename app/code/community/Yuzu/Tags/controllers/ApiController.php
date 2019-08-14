<?php

/**
 * Check controller
 *
 * @category    Yuzu
 * @package     Yuzu_Tags
 * @copyright   Copyright (c) 2015 Yuzu (http://www.yuzu.co)
 * @author      Olivier Mouren <olivier@yuzu.co>
 */
class Yuzu_Tags_ApiController extends Mage_Core_Controller_Front_Action
{
    /** @var Yuzu_Tags_Helper_Api */
    private $api;

    public function indexAction()
    {
        $request = $this->getRequest();
        $this->api = Mage::helper('yuzu_tags/Api');
        $this->api->setRequest($request);

        $this->checkRequest();

        $res = '';
        $action = json_decode($this->api->getPostData()['query'], true)['action'];
        switch ($action) {
            case 'getOrders':
                $res = $this->getOrders();
                break;
            case 'getOrder':
                $res = $this->getOrder();
                break;
            case 'getCodes':
                $res = $this->getCodes();
                break;
            case 'getCategories':
                $res = $this->getCategories();
                break;
            case 'getProducts':
                $res = $this->getProducts();
                break;
            default:
                break;
        }

        die($this->api->encodeBase64(json_encode(array_merge($res, $this->api->getResponse()))));
    }

    private function checkRequest()
    {
        $postData = $this->api->getPostData();
        if (!count($postData)) {
            $res = array();
            $res['debug'] = 'No POST DATA received';
            $res['return'] = 2;

            die($this->api->encodeBase64(json_encode($res)));
        }

        $isActive = $this->isModuleActive();
        if ($isActive['return'] !== 1) {
            die($this->api->encodeBase64(json_encode($isActive)));
        }

        $checkSign = $this->checkSign();
        if ($checkSign['return'] !== 1) {
            die($this->api->encodeBase64(json_encode($checkSign)));
        }
    }

    private function isModuleActive()
    {
        $res = array();
        $active = false;
        $message = json_decode($this->api->decodeBase64($this->api->getPostData()['message']), true);

        if (!empty($message['id_shop'])) {
            $active = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/enable', $message['id_shop']) ? true : false;
        } else {
            $active = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/enable') ? true : false;
        }

        if (!$active) {
            $res['debug'] = 'Module disabled';
            $res['return'] = 2;
            $res['query'] = 'isActiveModule';

            return $res;
        }

        $res['debug'] = 'Module installed and enabled';
        if (!empty($message['id_shop'])) {
            $res['sign'] = sha1(
                $this->api->getPostData()['query']
                .Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/merchant_key', $message['id_shop'])
                .Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/secret_key', $message['id_shop'])
            );
        } else {
            $res['sign'] = sha1(
                $this->api->getPostData()['query']
                .Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/merchant_key')
                .Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/secret_key')
            );
        }

        $res['return'] = 1;
        $res['query'] = $this->api->getPostData()['query'];

        return $res;
    }

    private function checkSign()
    {
        $res = array();
        $message = json_decode($this->api->decodeBase64($this->api->getPostData()['message']), true);

        if (empty($message)) {
            $res['debug'] = 'Empty message';
            $res['return'] = 2;
            $res['query'] = 'checkSign';

            return $res;
        }

        if (!empty($message['id_shop'])) {
            $merchantKey = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/merchant_key', $message['id_shop']);
            $secretKey = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/secret_key', $message['id_shop']);

            $res['query'] = 'checkSign';
            if (!$merchantKey || !$secretKey) {
                $res['debug'] = 'Identifiants client non renseignés sur le module';
                $res['message'] = 'Identifiants client non renseignés sur le module';
                $res['return'] = 3;

                return $res;
            } elseif ($message['merchantKey'] !== $merchantKey) {
                $res['message'] = 'MerchantKey incorrecte';
                $res['debug'] = 'MerchantKey incorrecte';
                $res['return'] = 4;

                return $res;
            } elseif (sha1($this->api->getPostData()['query'].$merchantKey.$secretKey) !== $message['sign']) {
                $res['message'] = 'La signature est incorrecte';
                $res['debug'] = 'La signature est incorrecte';
                $res['return'] = 5;

                return $res;
            } else {
                $res['message'] = 'Identifiants client Ok';
                $res['debug'] = 'Identifiants client Ok';
                $res['return'] = 1;
                $res['sign'] = sha1($this->api->getPostData()['query'].$merchantKey.$secretKey);

                return $res;
            }
        } else {
            $merchantKey = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/merchant_key');
            $secretKey = Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/secret_key');

            if (!$merchantKey || !$secretKey) {
                $res['debug'] = 'Identifiants client non renseignés sur le module';
                $res['message'] = 'Identifiants client non renseignés sur le module';
                $res['return'] = 3;
                $res['query'] = 'checkSign';

                return $res;
            } elseif ($message['merchantKey'] !== $merchantKey) {
                $res['message'] = 'MerchantKey incorrecte';
                $res['debug'] = 'MerchantKey incorrecte';
                $res['return'] = 4;
                $res['query'] = 'checkSign';

                return $res;
            } elseif (sha1($this->api->getPostData()['query'].$merchantKey.$secretKey) !== $message['sign']) {
                $res['message'] = 'La signature est incorrecte';
                $res['debug'] = 'La signature est incorrecte';
                $res['return'] = 5;
                $res['query'] = 'checkSign';

                return $res;
            }
            $res['message'] = 'Identifiants Client Ok';
            $res['debug'] = 'Identifiants Client Ok';
            $res['return'] = 1;
            $res['sign'] = sha1($this->api->getPostData()['query'].$merchantKey.$secretKey);
            $res['query'] = 'checkSign';

            return $res;
        }
    }

    private function getOrders()
    {
        $res = array();
        $results = $this->api->getOrders();

        $orders = array();
        foreach ($results as $order) {
            $orders[] = $this->api->formatOrder($order);
        }

        $res['message']['orders']  = $orders;

        return $res;
    }

    private function getOrder()
    {
        $res = array();
        $order = $this->api->getOrder();

        if ($order) {
            $res['message'] = $this->api->formatOrder($order);
        }

        return $res;
    }

    private function getCodes()
    {
        $res = array();
        $results = $this->api->getCartRules();

        $codes = array();
        foreach ($results as $quote) {
            $codes[] = $this->api->formatCartRule($quote);
        }

        $res['message']['codes']  = $codes;

        return $res;
    }

    private function getProducts()
    {
        $res = array();
        $results = $this->api->getProducts();

        $products = array();
        foreach ($results as $product) {
            $products[] = $this->api->formatProduct($product);
        }

        $res['message']['products']  = $products;

        return $res;
    }

    private function getCategories()
    {
        $res = array();
        $results = $this->api->getCategories();

        $categories = array();
        foreach ($results as $category) {
            $categories[] = $this->api->formatCategory($category);
        }

        $res['message']['categories']  = $categories;

        return $res;
    }
}
