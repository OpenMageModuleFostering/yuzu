<?php

/**
 * Checkout block
 *
 * @category    Yuzu
 * @package     Yuzu_Tags
 * @copyright   Copyright (c) 2015 Yuzu (http://www.yuzu.co)
 * @author      Jonathan Martin <jonathan@yuzu.co>
 */
class Yuzu_Tags_Block_Checkout extends Yuzu_Tags_Block_Abstract
{
    public function getEvent()
    {
        try {
            $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
            if ($orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);

                //Order data
                $yuOrder = array(
                    'id'          => $order->getIncrementId(),
                    'discount'    => number_format($order->getDiscountAmount(), 2, ".", ""),
                    'subtotal'    => number_format($order->getSubtotal(), 2, ".", ""),
                    'shipping'    => number_format($order->getShippingAmount(), 2, ".", ""),
                    'tax'         => number_format($order->getTaxAmount(), 2, ".", ""),
                    'paymentType' => $order->getPayment() ? $order->getPayment()->getMethodInstance()->getTitle() : 'unknown',
                    'shippingMethod' => $order->getShippingDescription(),
                    'total'       => number_format($order->getGrandTotal(), 2, ".", ""),
                    'currency'    => $order->getOrderCurrencyCode(),
                    'coupon'    => $order->getCouponCode()
                );
                $event = $yuOrder;

                //Products data
                $yuItems = array();
                foreach ($order->getAllVisibleItems() as $item) {

                    $options = $item->getProductOptions();
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
                        'quantity' => number_format($item->getQtyOrdered(), 0, ".", ""),
                        'price'    => number_format($item->getRowTotal(), 2, ".", ""),
                        'discount'     => number_format($item->getDiscountAmount(), 2, ".", ""),
                        'options'     => $itemOptions,
                        'name'     => $item->getName()
                    );

                }
                $event['lines'] = $yuItems;

                //Customer data
                $yuCustomer = array(
                    'id' => ($order->getCustomerId()) ? $order->getCustomerId() : '0',
                    'lastName' => $order->getCustomerLastname(),
                    'firstName' => $order->getCustomerFirstname(),
                    'email' => $order->getCustomerEmail(),
                    'gender' => $this->getGender($order->getCustomerGender()),
                    'birthday' => $order->getCustomerDob(),
                    'addresses' => array()
                );

                //Shipping Address
                $shippingAddress = Mage::getModel('sales/order_address')->load($order->getShippingAddressId());
                $shipping = array();
                $shipping['street'][] = $shippingAddress->getStreet1();
                if ($shippingAddress->getStreet2()) {
                    $shipping['street'][] = $shippingAddress->getStreet2();
                }
                $shipping['postalCode'] = $shippingAddress->getPostcode();
                $shipping['city'] = $shippingAddress->getCity();
                $shipping['state'] = $shippingAddress->getRegion();
                $shipping['country'] = $shippingAddress->getCountry();
                $shipping['type'] = 'shipping';
                $yuCustomer['addresses'][] = $shipping;

                //Billing Address
                $billingAddress = Mage::getModel('sales/order_address')->load($order->getBillingAddressId());
                $billing = array();
                $billing['street'][] = $billingAddress->getStreet1();
                if ($billingAddress->getStreet2()) {
                    $billing['street'][] = $billingAddress->getStreet2();
                }
                $billing['postalCode'] = $billingAddress->getPostcode();
                $billing['city'] = $billingAddress->getCity();
                $billing['state'] = $billingAddress->getRegion();
                $billing['country'] = $billingAddress->getCountry();
                $billing['type'] = 'billing';
                $yuCustomer['addresses'][] = $billing;

                $event['customer'] = $yuCustomer;

                return json_encode($event);
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    private function getGender($rawGender)
    {
        $gender = array('1' => 'm', '2' => 'f');

        return (isset($gender[$rawGender])) ? $gender[$rawGender] : '';
    }
}