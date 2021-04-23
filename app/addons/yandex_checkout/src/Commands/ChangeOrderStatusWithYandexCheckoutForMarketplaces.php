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
use Tygh\Addons\YandexCheckout\Enum\PaymentMode;
use Tygh\Addons\YandexCheckout\Enum\PaymentStatus;
use Tygh\Addons\YandexCheckout\Enum\SettlementType;
use Tygh\Addons\YandexCheckout\Payments\YandexCheckoutForMarketplaces;
use Tygh\Addons\YandexCheckout\ServiceProvider;
use Tygh\Common\OperationResult;
use Tygh\Enum\YesNo;
use YooKassa\Model\PaymentInterface;

class ChangeOrderStatusWithYandexCheckoutForMarketplaces
{
    const PAID_ORDER_STATUS = 'P';

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
     * @param \Tygh\Addons\YandexCheckout\Payments\YandexCheckoutForMarketplaces $payment_processor
     *
     * @return \YooKassa\Model\PaymentInterface|null
     */
    protected function getPaymentInfo(YandexCheckoutForMarketplaces $payment_processor)
    {
        $payment_service = ServiceProvider::getPaymentService();
        $payment_info = null;
        try {
            $payment_info = $payment_processor->getPaymentInfo($payment_service->getPaymentId($this->order_info));
            if ($payment_info->getStatus() !== PaymentStatus::SUCCEEDED) {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }

        return $payment_info;
    }

    /**
     * @return bool
     */
    protected function isFullPrepaymentReceiptRequired()
    {
        $processor_params = $this->order_info['payment_method']['processor_params'];

        return YesNo::toBool($processor_params['send_receipt'])
            && $this->status_to === self::PAID_ORDER_STATUS;
    }

    /**
     * @return bool
     */
    protected function isFullPrepaymentReceiptSent()
    {
        return isset($this->order_id['payment_info']['yandex_checkout.full_prepayment_receipt_id']);
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
     * @param \Tygh\Addons\YandexCheckout\Payments\YandexCheckoutForMarketplaces $payment_processor
     * @param string                                                             $payment_mode
     *
     * @return \Tygh\Common\OperationResult
     */
    protected function sendReceipt(YandexCheckoutForMarketplaces $payment_processor, $payment_mode)
    {
        $result = new OperationResult(true);

        try {
            $company_data = fn_get_company_data($this->order_info['company_id']);
            $receipt = $payment_processor->createReceipt(
                $this->order_info,
                $company_data['yandex_checkout_shopid'],
                $payment_mode,
                SettlementType::PREPAYMENT
            );
            fn_update_order_payment_info(
                $this->order_id,
                ["yandex_checkout.{$payment_mode}_receipt_id" => $receipt->getId()]
            );
        } catch (Exception $exception) {
            $result->setSuccess(false);
            $result->addError(0, $exception->getMessage());

            fn_update_order_payment_info(
                $this->order_id,
                ["yandex_checkout.{$payment_mode}_receipt_error" => $exception->getMessage()]
            );
        }

        return $result;
    }

    /**
     * @param PaymentInterface $payment_info Information about payment.
     *
     * @return bool
     */
    protected function isEligibleForWithdrawal(PaymentInterface $payment_info)
    {
        $payment_service = ServiceProvider::getPaymentService();
        $processor_params = $this->order_info['payment_method']['processor_params'];

        return ($this->status_to === self::PAID_ORDER_STATUS || $this->status_to === $processor_params['final_success_status'])
            && !empty($this->order_info['company_id'])
            && $payment_service->hasTransferForCompany($payment_info, $this->order_info['company_id']);
    }

    /**
     * @return bool
     */
    protected function isWithdrawn()
    {
        return isset($this->order_info['payment_info']['yandex_checkout.withdrawal_amount']);
    }

    /**
     * @param float $amount
     */
    protected function withdraw($amount)
    {
        $payouts_manager = ServiceProvider::getPayoutsManagerService()->getManager($this->order_info['company_id']);

        $payouts_manager->createWithdrawal(
            $amount,
            $this->order_id
        );

        fn_update_order_payment_info(
            $this->order_id,
            ['yandex_checkout.withdrawal_amount' => $amount]
        );
    }

    public function run()
    {
        $result = new OperationResult(true);
        $processor_params = $this->order_info['payment_method']['processor_params'];

        $payment_processor = new YandexCheckoutForMarketplaces(
            $processor_params['shop_id'],
            $processor_params['scid'],
            ServiceProvider::getReceiptService(),
            ServiceProvider::getPayoutsManagerService()
        );

        $payment_info = $this->getPaymentInfo($payment_processor);
        if (!$payment_info) {
            return $result;
        }

        // create full prepayment receipt
        if ($this->isFullPrepaymentReceiptRequired()
            && !$this->isFullPrepaymentReceiptSent()
        ) {
            $result = $this->sendReceipt(
                $payment_processor,
                PaymentMode::FULL_PREPAYMENT
            );
        }

        // create withdrawal
        if ($this->isEligibleForWithdrawal($payment_info)
            && !$this->isWithdrawn()
        ) {
            $payment_service = ServiceProvider::getPaymentService();

            $this->withdraw(
                $payment_service->getTransferForCompany($payment_info, $this->order_info['company_id'])
            );
        }

        // create full payment receipt
        if ($this->isFullPaymentReceiptRequired()
            && !$this->isFullReceiptSent()
        ) {
            $result = $this->sendReceipt(
                $payment_processor,
                PaymentMode::FULL_PAYMENT
            );
        }

        return $result;
    }
}