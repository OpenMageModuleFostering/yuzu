<?php

/**
 * Api Helper
 *
 * @category    Yuzu
 * @package     Yuzu_Tags
 * @copyright   Copyright (c) 2015 Yuzu (http://www.yuzu.co)
 * @author      Olivier Mouren <olivier@yuzu.co>
 */
class Yuzu_Tags_Helper_Api extends Mage_Core_Helper_Abstract
{
    private $request;

    private $message;

    private $res = array();

    public function setRequest(Mage_Core_Controller_Request_Http $request)
    {
        $this->request = $request;
        $this->message = json_decode($this->decodeBase64($this->getPostData()['message']), true);
    }

    public function getPostData()
    {
        return $this->request->getPost();
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->res;
    }

    public function encodeBase64($data)
    {
        return base64_encode($data);
    }

    public function decodeBase64($data)
    {
        return base64_decode($data);
    }

    public function getOrders()
    {
        $query = json_decode($this->getPostData()['query'], true);

        $page = (int) $query['page'];
        $limit = (int) $query['limit'];

        /** @var Mage_Sales_Model_Resource_Order_Collection $q */
        $q = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToSort('updated_at', 'asc')
            ->setCurPage($page)
            ->setPageSize($limit);

        if (!empty($query['date_from']) && !empty($query['date_to'])) {
            $q = $q->addAttributeToFilter('updated_at', ['from' => $query['date_from'], 'to' => $query['date_to']]);
        } elseif (!empty($query['date_from'])) {
            $q = $q->addAttributeToFilter('updated_at', ['from' => $query['date_from']]);
        } elseif (!empty($query['date_to'])) {
            $q = $q->addAttributeToFilter('updated_at', ['to' => $query['date_from']]);
        }

        if (!empty($this->message['id_shop'])) {
            $q = $q->addAttributeToFilter('store_id', $this->message['id_shop']);
        }

        $this->signResponse();

        if ($page > $q->getLastPageNumber()) {
            return null;
        }

        return $q;
    }

    public function getOrder()
    {
        $query = json_decode($this->getPostData()['query'], true);

        /** @var Mage_Sales_Model_Resource_Order_Collection $q */
        $q = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('increment_id', $query['id_order']);

        if (!empty($this->message['id_shop'])) {
            $q = $q->addAttributeToFilter('store_id', $this->message['id_shop']);
        }

        $this->signResponse();

        return $q->getFirstItem()->getId() ? $q->getFirstItem() : null;
    }

    public function formatOrder(Mage_Sales_Model_Order $order)
    {
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId())->getData();

        $products = array();
        /** @var Mage_Sales_Model_Order_Item $product */
        foreach ($order->getItemsCollection() as $product) {
            $products[] = array(
                'id_product' => $product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'quantity' => $product->getQtyOrdered(),
                'price' => $product->getPrice(),
            );
        }

        $formatedOrder = array(
            'order' => array(
                'id_order' => $order->getIncrementId(),
                'created_at' => $order->getCreatedAt(),
                'updated_at' => $order->getUpdatedAt(),
                'status' => $order->getStatus(),
                'coupon_code' => $order->getCouponCode(),
//                    'coupon_rule_name' => $order->getCouponCode(),
                'shipping_description' => $order->getShippingDescription(),
                'grand_total' => $order->getGrandTotal(),
                'shipping_amount' => $order->getShippingAmount(),
                'discount_amount' => $order->getDiscountAmount(),
                'discount_description' => $order->getDiscountDescription(),
                'tax_amount' => $order->getTaxAmount(),
                'subtotal' => $order->getSubtotal(),
                'weight' => $order->getWeight(),
                'currency_code' => $order->getOrderCurrencyCode(),
                'is_virtual' => $order->getIsVirtual(),
                'ip' => $order->getRemoteIp(),
                'gift_message_id' => $order->getGiftMessageId(),
                'store_id' => $order->getStoreId(),
            ),
            'customer' => array(
                'id_customer' => $order->getCustomerId(),
                'firstname' => $order->getCustomerFirstname(),
                'lastname' => $order->getCustomerLastname(),
                'email' => $order->getCustomerEmail(),
                'dob' => $customer['dob'],
//                    'phone' => 'phone',
                'group_id' => $order->getCustomerGroupId(),
                'gender' => $customer['gender'],
                'taxvat' => $customer['taxvat'],
                'is_guest' => $order->getCustomerIsGuest(),
            ),
            'products' => $products,
        );

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        if ($billingAddress) {
            $formatedOrder['billing_address'] = array(
                'id' => $order->getBillingAddressId(),
                'region' => $billingAddress->getRegion(),
                'postcode' => $billingAddress->getPostcode(),
                'prefix' => $billingAddress->getPrefix(),
                'company' => $billingAddress->getCompany(),
                'firstname' => $billingAddress->getFirstname(),
                'lastname' => $billingAddress->getLastname(),
                'street' => $billingAddress->getStreet(),
                'city' => $billingAddress->getCity(),
                'fax' => $billingAddress->getFax(),
                'telephone' => $billingAddress->getTelephone(),
                'country_id' => $billingAddress->getCountryId(),
            );
        }

        if ($shippingAddress) {
            $formatedOrder['shipping_address'] = array(
                'id' => $order->getShippingAddressId(),
                'region' => $shippingAddress->getRegion(),
                'postcode' => $shippingAddress->getPostcode(),
                'prefix' => $shippingAddress->getPrefix(),
                'company' => $shippingAddress->getCompany(),
                'firstname' => $shippingAddress->getFirstname(),
                'lastname' => $shippingAddress->getLastname(),
                'street' => $shippingAddress->getStreet(),
                'city' => $shippingAddress->getCity(),
                'fax' => $shippingAddress->getFax(),
                'telephone' => $shippingAddress->getTelephone(),
                'country_id' => $shippingAddress->getCountryId(),
            );
        }

        return $formatedOrder;
    }

    public function getCartRules()
    {
        /** @var Mage_SalesRule_Model_Resource_Rule_Collection $q */
        $q = Mage::getModel('salesrule/rule')->getCollection();

        $this->signResponse();

        return $q;
    }

    public function formatCartRule(Mage_SalesRule_Model_Rule $rule)
    {
        return array(
            'id_quote' => $rule->getId(),
            'name' => $rule->getName(),
            'description' => $rule->getDescription(),
            'code' => $rule->getPrimaryCoupon()->getCode(),
            'date_from' => $rule->getFromDate(),
            'date_to' => $rule->getToDate(),
            'active' => $rule->getIsActive(),
            'action' => $rule->getSimpleAction(),
            'amount' => $rule->getDiscountAmount(),
            'quantity' => $rule->getUsesPerCoupon(),
            'quantity_per_user' => $rule->getUsesPerCustomer(),
        );
    }

    public function getCategories()
    {
        /** @var Mage_Catalog_Model_Resource_Category_Collection $q */
        $q = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('description');

        if (!empty($this->message['id_shop'])) {
            $rootid = Mage::app()->getStore($this->message['id_shop'])->getRootCategoryId();
            $q = $q->addFieldToFilter('path', ['like' => "1/$rootid/%"]);
        }

        $this->signResponse();

        return $q;
    }

    public function formatCategory(Mage_Catalog_Model_Category $category)
    {
        return array(
            'id_category' => $category->getId(),
            'id_parent' => $category->getParentId(),
            'name' => $category->getName(),
            'description' => $category->getDescription(),
        );
    }

    public function getProducts()
    {
        $query = json_decode($this->getPostData()['query'], true);

        $page = (int) $query['page'];
        $limit = (int) $query['limit'];

        /** @var Mage_Catalog_Model_Resource_Product_Collection $q */
        $q = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('description')
            ->addAttributeToSelect('price')
            ->setCurPage($page)
            ->setPageSize($limit);


        if (!empty($this->message['id_shop'])) {
            $q = $q->addStoreFilter($this->message['id_shop']);
        }

        $this->signResponse();

        if ($page > $q->getLastPageNumber()) {
            return null;
        }

        return $q;
    }

    public function formatProduct(Mage_Catalog_Model_Product $product)
    {
        $fullProduct = Mage::getModel('catalog/product')->load($product->getId());

        $images = array();
        foreach ($fullProduct->getMediaGalleryImages() as $image) {
            $images[] = $image->getUrl();
        }

        return array(
            'id_product' => $product->getId(),
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'categories' => $product->getCategoryIds(),
            'url' => $product->getProductUrl(),
            'images' => $images,
            'active' => $product->getStatus(),
        );
    }

    private function signResponse()
    {
        if (!empty($this->message['id_shop'])) {
            $this->res['sign'] = sha1(
                $this->getPostData()['query']
                .Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/merchant_key', $this->message['id_shop'])
                .Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/secret_key', $this->message['id_shop'])
            );
        } else {
            $this->res['sign'] = sha1(
                $this->getPostData()['query']
                .Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/merchant_key')
                .Mage::helper('yuzu_tags')->getConfig('yuzu_tags/general/secret_key')
            );
        }
    }
}
