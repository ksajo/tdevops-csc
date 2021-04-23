<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol\v4;

use Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol\ReceiptRequest as ReceiptRequestV3;
use Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt;

/**
 * The class represents creating receipt in format with API v4.0
 *
 * @package Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol\v4
 */
class ReceiptRequest extends ReceiptRequestV3
{
    /** @var Receipt Instance of the receipt */
    protected $receipt;

    /** @var string Company INN */
    protected $inn;

    /** @var string  Payment address*/
    protected $payments_address;

    /** @var string  URL address for notification of the processed receipt */
    protected $callback_url;

    /** @var string Company administrator email */
    protected $company_email;

    /**
     * InvoiceRequest constructor with format API v4
     *
     * @param Receipt $receipt          Instance of the receipt
     * @param string  $inn              Company INN
     * @param string  $payment_address  Payment address
     * @param string  $callback_url     URL address for notification of the processed receipt
     * @param string  $company_email    Company administrator email
     */
    public function __construct(Receipt $receipt, $inn, $payment_address, $callback_url, $company_email)
    {
        $this->receipt = $receipt;
        $this->inn = $inn;
        $this->payment_address = $payment_address;
        $this->callback_url = $callback_url;
        $this->company_email = $company_email;
    }
    
    /**
     * Gets json data with API v4.0 format
     *
     * @return string
     */
    public function json()
    {
        $receipt = $this->receipt;

        $result = array(
            'timestamp' => $receipt->getTimestamp()->format('d.m.Y h:i:s'),
            'external_id' => $receipt->getExternalId(),
            'service' => array(
                'callback_url' => $this->callback_url,
            ),
            'receipt' => array(
                'company' => array(
                    'email' => $this->company_email,
                    'inn' => $this->inn,
                    'payment_address' => $this->payment_address,
                ),
                'total' => $receipt->getTotal(),
                'items' => array(),
                'payments' => array(),
            ),
        );

        if ($receipt->getSno()) {
            $result['receipt']['company']['sno'] = $receipt->getSno();
        }

        if ($receipt->getEmail()) {
            $result['receipt']['client']['email'] = $receipt->getEmail();
        } elseif ($receipt->getPhone()) {
            $phone = $receipt->getPhone();
            if (strpos($phone, '+7') === 0) {
                $phone = substr($phone, 2);
            }
            $result['receipt']['client']['phone'] = $phone;
        }

        foreach ($receipt->getPayments() as $payment) {
            $result['receipt']['payments'][] = array(
                'type' => $payment->getType(),
                'sum' => $payment->getSum(),
            );
        }

        foreach ($receipt->getItems() as $item) {
            $data = [
                'name'           => $this->truncateItemName($item->getName()),
                'price'          => round($item->getPrice(), 2),
                'quantity'       => $item->getQuantity(),
                'sum'            => round($item->getSum(), 2),
                'vat'            => [
                    'type' => $item->getTaxType()
                ],
                'payment_method' => $receipt->getPaymentMethod(),
                'payment_object' => 'payment',
            ];

            $result['receipt']['items'][] = $data;
        }

        return json_encode($result);
    }
}
