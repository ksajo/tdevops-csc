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

namespace Tygh\Addons\YandexCheckout\Commands;

use Exception;
use Tygh\Addons\YandexCheckout\Enum\PaymentStatus;
use Tygh\Addons\YandexCheckout\Payments\YandexCheckout;
use Tygh\Addons\YandexCheckout\ServiceProvider;
use Tygh\Common\OperationResult;
use Tygh\Enum\YesNo;
use YooKassa\Model\PaymentInterface;

class ChangeOrderStatusWithYandexCheckout
{
    /**
     * @var int
     */
    protected $order_id;

    /**
     * @var string
     */
    protected $status_to;

    /**
     * @var array
     */
    protected $order_info;

    public function __construct($order_id, $status_to, array $order_info)
    {
        $this->order_id = $order_id;
        $this->status_to = $status_to;
        $this->order_info = $order_info;
    }

    /**
     * @param \Tygh\Addons\YandexCheckout\Payments\YandexCheckout $payment_processor
     *
     * @return \YooKassa\Model\PaymentInterface|null
     */
    protected function getPaymentInfo(YandexCheckout $payment_processor)
    {
        $payment_service = ServiceProvider::getPaymentService();
        $payment_info = null;
        try {
            $payment_info = $payment_processor->getPaymentInfo($payment_service->getPaymentId($this->order_info));
        } catch (Exception $e) {
            return null;
        }

        return $payment_info;
    }

    /**
     * @return bool
     */
    protected function isFullPaymentReceiptRequired()
    {
        $processor_params = $this->order_info['payment_method']['processor_params'];

        return YesNo::toBool($processor_params['send_receipt'])
            && $this->status_to === $processor_params['final_success_status'];
    }

    /**
     * @return bool
     */
    protected function isFullReceiptSent()
    {
        return isset($this->order_info['payment_info']['yandex_checkout.full_payment_receipt_id']);
    }

    /**
     * Checks payment status at Yandex.Checkout side and decides does this payment require capture.
     *
     * @param PaymentInterface $payment_info Payment info from Yandex.Checkout side
     *
     * @return bool
     */
    protected function isPaymentCaptureRequired(PaymentInterface $payment_info)
    {
        $processor_params = $this->order_info['payment_method']['processor_params'];

        return YesNo::toBool($processor_params['are_held_payments_enabled'])
            && $payment_info->getStatus() === PaymentStatus::WAITING_FOR_CAPTURE
            && in_array($this->status_to, fn_get_settled_order_statuses());
    }

    /**
     * @param \Tygh\Addons\YandexCheckout\Payments\YandexCheckout $payment_processor
     *
     * @return \Tygh\Common\OperationResult
     */
    protected function sendReceipt(YandexCheckout $payment_processor)
    {
        $result = new OperationResult(true);

        try {
            $receipt = $payment_processor->createReceipt(
                $this->order_info
            );
            fn_update_order_payment_info(
                $this->order_id,
                ['yandex_checkout.full_payment_receipt_id' => $receipt->getId()]
            );
        } catch (Exception $exception) {
            $result->setSuccess(false);
            $result->addError(0, $exception->getMessage());

            fn_update_order_payment_info(
                $this->order_id,
                ['yandex_checkout.full_payment_receipt_error' => $exception->getMessage()]
            );
        }

        return $result;
    }

    /**
     * Captures postponed payment.
     *
     * @param YandexCheckout $payment_processor Payment processor object
     *
     * @return OperationResult
     */
    protected function capturePayment(YandexCheckout $payment_processor)
    {
        $result = new OperationResult(true);

        try {
            $response = $payment_processor->capturePayment($this->order_info, $this->order_info['payment_method']['processor_params']);
            fn_update_order_payment_info($this->order_id, ['status' => $response->getStatus()]);
        } catch (Exception $exception) {
            $result->setSuccess(false);
            $result->addError('0', $exception->getMessage());
        }

        return $result;
    }

    public function run()
    {
        $result = new OperationResult(true);

        $processor_params = $this->order_info['payment_method']['processor_params'];

        $payment_processor = new YandexCheckout(
            $processor_params['shop_id'],
            $processor_params['scid'],
            ServiceProvider::getReceiptService()
        );

        $payment_info = $this->getPaymentInfo($payment_processor);
        if (!$payment_info) {
            return $result;
        }

        if ($this->isPaymentCaptureRequired($payment_info)) {
            $result = $this->capturePayment($payment_processor);
        }

        if ($this->isFullPaymentReceiptRequired()
            && !$this->isFullReceiptSent()
        ) {
            $result = $this->sendReceipt($payment_processor);
        }

        return $result;
    }
}