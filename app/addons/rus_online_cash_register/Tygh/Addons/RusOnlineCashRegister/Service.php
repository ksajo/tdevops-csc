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


namespace Tygh\Addons\RusOnlineCashRegister;


use Tygh\Addons\RusOnlineCashRegister\CashRegister\ICashRegister;
use Tygh\Addons\RusOnlineCashRegister\CashRegister\InfoResponse;
use Tygh\Addons\RusOnlineCashRegister\CashRegister\Response;
use Tygh\Addons\RusOnlineCashRegister\CashRegister\SendResponse;
use Tygh\Addons\RusOnlineCashRegister\Receipt\Payment;
use Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt;
use Tygh\Addons\RusTaxes\ReceiptFactory;

/**
 * Class provides methods for register cash receipts.
 *
 * @package Tygh\Addons\RusOnlineCashRegister
 */
class Service
{
    /** @var ICashRegister */
    protected $cash_register;

    /** @var ReceiptRepository */
    protected $receipt_repository;

    /** @var ReceiptFactory */
    protected $receipt_factory;

    /** @var array */
    protected $payments_map;

    /** @var string */
    protected $currency;

    /** @var string */
    protected $sno;

    /**
     * Service constructor.
     *
     * @param ICashRegister        $cash_register          Cash register instance
     * @param ReceiptRepository    $receipt_repository     Receipt repository instance
     * @param ReceiptFactory       $receipt_factory        Receipt factory
     * @param array                $payments_map           Payments map (local payment identifier => external payment identifier)
     * @param string               $currency               Currency code (RUB, USD)
     * @param string               $sno                    Taxation system
     */
    public function __construct(
        ICashRegister $cash_register,
        ReceiptRepository $receipt_repository,
        ReceiptFactory $receipt_factory,
        array $payments_map,
        $currency,
        $sno
    )
    {
        $this->cash_register = $cash_register;
        $this->receipt_repository = $receipt_repository;
        $this->receipt_factory = $receipt_factory;
        $this->payments_map = $payments_map;
        $this->currency = $currency;
        $this->sno = $sno;
    }

    /**
     * Sends receipt to cash register.
     *
     * @param Receipt $receipt  Instance of the receipt.
     *
     * @return Response
     */
    public function sendReceipt(Receipt $receipt)
    {
        $response = $this->cash_register->send($receipt);
        $this->processResponse($response, $receipt);

        return $response;
    }

    /**
     * Retrieves receipt info by UUID.
     *
     * @param string $uuid Receipt UUID
     *
     * @return InfoResponse
     */
    public function getReceiptInfo($uuid)
    {
        return $this->cash_register->info($uuid);
    }

    /**
     * Retrieves receipt info and update it.
     *
     * @param Receipt $receipt Instance of the receipt.
     *
     * @return Response
     */
    public function refreshReceipt(Receipt $receipt)
    {
        $response = $this->cash_register->info($receipt->getUUID());
        $this->updateReceiptByInfoResponse($receipt, $response);

        return $response;
    }

    /**
     * Update receipt by response.
     *
     * @param Receipt       $receipt    Instance of the receipt.
     * @param InfoResponse  $response   Instance of the info response.
     */
    public function updateReceiptByInfoResponse(Receipt $receipt, InfoResponse $response)
    {
        $this->processResponse($response, $receipt);
    }

    /**
     * Convert order data to receipt.
     *
     * @param array  $order          Order data
     * @param int    $type           Receipt type (sale = 0, refund = 1, buy = 2)
     * @param string $payment_method Payment method ('full_prepayment', 'full_payment')
     *
     * @return Receipt|null
     */
    public function getReceiptFromOrder(array $order, $type, $payment_method = Receipt::PAYMENT_METHOD_FULL_PAYMENT)
    {
        $payment_id = isset($order['payment_id']) ? $order['payment_id'] : 0;
        $base_receipt = $this->receipt_factory->createReceiptFromOrder($order, $this->currency, false);

        if ($base_receipt === null) {
            return null;
        }

        $receipt = Receipt::fromBaseReceipt($base_receipt);
        $receipt->setType($type);
        $receipt->setSno($this->sno);
        $receipt->setStatus(Receipt::STATUS_WAIT);
        $receipt->setObjectType('order');
        $receipt->setObjectId($order['order_id']);
        $receipt->setTimestamp(TIME);
        $receipt->setCurrency($this->currency);
        $receipt->setPaymentMethod($payment_method);

        $receipt->setPayment(new Payment(
            $this->getExternalPaymentId($payment_id),
            $receipt->getTotal())
        );

        return $receipt;
    }

    /**
     * Checks response.
     *
     * @param Response $response
     * @param Receipt  $receipt
     */
    protected function processResponse(Response $response, Receipt $receipt)
    {
        if ($response->getStatus() !== null) {
            $receipt->setStatus($response->getStatus());
            $receipt->setStatusMessage($response->getStatusMessage());
        }

        if (!$response->hasErrors()) {
            if ($response instanceof SendResponse) {
                $receipt->setUUID($response->getUUID());
            } elseif ($response instanceof InfoResponse) {
                $receipt->setRequisites($response->getReceiptRequisites());
            }
        }

        $this->receipt_repository->save($receipt);
    }

    /**
     * Gets external payment id.
     *
     * @param int $payment_id Payment identifier.
     *
     * @return int
     */
    protected function getExternalPaymentId($payment_id)
    {
        return isset($this->payments_map[$payment_id]) ? $this->payments_map[$payment_id] : 0;
    }
}
