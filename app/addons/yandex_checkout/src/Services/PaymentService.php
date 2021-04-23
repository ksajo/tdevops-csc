<?php

namespace Tygh\Addons\YandexCheckout\Services;

use YooKassa\Model\PaymentInterface;

/**
 * Class PaymentService collects functions that requires to operations with payment information
 *
 * @package Tygh\Addons\YandexCheckout\Services
 */
class PaymentService
{
    /**
     * Returns payment id from order information
     *
     * @param array $order_info Order information
     *
     * @return string Payment ID
     */
    function getPaymentId(array $order_info)
    {
        $payment_id = '';
        if (isset($order_info['payment_info']['payment_id'])) {
            $payment_id = $order_info['payment_info']['payment_id'];
        } elseif (isset($order_info['payment_info']['id'])) {
            $payment_id = $order_info['payment_info']['id'];
        }

        return $payment_id;
    }

    function hasTransferForCompany(PaymentInterface $payment_info, $company_id)
    {
        return !empty($payment_info->metadata["transfer_{$company_id}"]);
    }

    function getTransferForCompany(PaymentInterface $payment_info, $company_id)
    {
        if (!$this->hasTransferForCompany($payment_info, $company_id)) {
            return 0.0;
        }

        return (float) $payment_info->metadata["transfer_{$company_id}"];
    }
}