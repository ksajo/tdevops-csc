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

namespace Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol;

use Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt;
/**
 * The request class represents request creating receipt.
 *
 * @package Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol
 */
class ReceiptRequest
{
    /**
     * @var Receipt Instance of the receipt.
     */
    protected $receipt;

    /**
     * @var string URL address for notification of the processed receipt.
     */
    protected $callback_url;

    /**
     * @var string Company inn
     */
    protected $inn;

    /**
     * @var string Payment address
     */
    protected $payment_address;

    /**
     * InvoiceRequest constructor.
     *
     * @param Receipt   $receipt            Instance of the receipt
     * @param string    $inn                Company inn
     * @param string    $payment_address    Payment address
     * @param string    $callback_url       URL address for notification of the processed receipt
     */
    public function __construct(Receipt $receipt, $inn, $payment_address, $callback_url)
    {
        $this->receipt = $receipt;
        $this->inn = $inn;
        $this->payment_address = $payment_address;
        $this->callback_url = $callback_url;
    }

    /**
     * Calls a method depending on the version of the API.
     *
     * @return string
     */
    public function json()
    {
        $receipt = $this->receipt;

        $result = array(
            'timestamp' => $receipt->getTimestamp()->format('d.m.Y h:i:s A'),
            'external_id' => $receipt->getExternalId(),
            'service' => array(
                'callback_url' => $this->callback_url,
                'inn' => $this->inn,
                'payment_address' => $this->payment_address,
            ),
            'receipt' => array(
                'attributes' => array(
                    'email' => '',
                    'phone' => '',
                ),
                'total' => $receipt->getTotal(),
                'items' => array(),
                'payments' => array(),
            ),
        );
        if ($receipt->getSno()) {
            $result['receipt']['attributes']['sno'] = $receipt->getSno();
        }
        if ($receipt->getEmail()) {
            $result['receipt']['attributes']['email'] = $receipt->getEmail();
        } elseif ($receipt->getPhone()) {
            $phone = $receipt->getPhone();
            if (strpos($phone, '+7') === 0) {
                $phone = substr($phone, 2);
            }
            $result['receipt']['attributes']['phone'] = $phone;
        }
        foreach ($receipt->getPayments() as $payment) {
            $result['receipt']['payments'][] = array(
                'type' => $payment->getType(),
                'sum' => $payment->getSum(),
            );
        }
        foreach ($receipt->getItems() as $item) {
            $data = array(
                'name' => $this->truncateItemName($item->getName()),
                'price' => round($item->getPrice(), 2),
                'quantity' => $item->getQuantity(),
                'sum' => round($item->getSum(), 2),
                'tax' => $item->getTaxType()
            );
            $result['receipt']['items'][] = $data;
        }
        return json_encode($result);
    }

    /**
     * Truncates item name.
     *
     * @param string $name      Item name
     * @param int    $length    Length of the new item name
     * @param string $suffix    String to append to the end of truncated string
     *
     * @return string
     */
    protected function truncateItemName($name, $length = 64, $suffix = '...')
    {
        if (function_exists('mb_strlen') && mb_strlen($name, 'UTF-8') > $length) {
            $length -= mb_strlen($suffix);
            $name = rtrim(mb_substr($name, 0, $length, 'UTF-8')) . $suffix;
        }
        return $name;
    }
}
