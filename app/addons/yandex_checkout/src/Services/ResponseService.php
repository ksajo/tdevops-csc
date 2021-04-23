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

namespace Tygh\Addons\YandexCheckout\Services;

use Tygh\Addons\YandexCheckout\Enum\PaymentStatus;
use YooKassa\Model\Metadata;
use YooKassa\Model\Notification\NotificationCanceled;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\NotificationEventType;

/**
 * Class ResponseService allows to work with responses from Yandex.Checkout API
 *
 * @package Tygh\Addons\YandexCheckout\Services
 */
class ResponseService
{
    /**
     * @param $response
     *
     * @return \YooKassa\Model\Metadata
     */
    public function getMetadataFromNotification($response)
    {
        $notification = $this->getNotificationFromResponse($response);
        if (!$notification) {
            return new Metadata();
        }
        $request_object = $notification->getObject();
        return $request_object->getMetadata();
    }

    /**
     * @param $response
     *
     * @return \YooKassa\Model\Notification\NotificationCanceled|\YooKassa\Model\Notification\NotificationSucceeded|null
     */
    protected function getNotificationFromResponse($response)
    {
        $request_body = $this->getRequest($response);
        switch ($request_body['event']) {
            case NotificationEventType::PAYMENT_SUCCEEDED:
                $notification = new NotificationSucceeded($request_body);
                break;
            case NotificationEventType::PAYMENT_CANCELED:
                $notification = new NotificationCanceled($request_body);
                break;
            default:
                return null;
        }
        return $notification;
    }

    /**
     * @param $response
     *
     * @return string|null
     */
    public function getPaymentIdFromNotification($response)
    {
        $notification = $this->getNotificationFromResponse($response);
        if (!$notification) {
            return null;
        }
        $response_object = $notification->getObject();
        return $response_object->getId();
    }

    /**
     * @param $response
     *
     * @return string
     */
    public function getStatusFromNotification($response)
    {
        $request_body = $this->getRequest($response);
        return $request_body['event'] === NotificationEventType::PAYMENT_SUCCEEDED ? PaymentStatus::SUCCEEDED : PaymentStatus::CANCELED;
    }

    /**
     * @param $response
     *
     * @return mixed
     */
    protected function getRequest($response)
    {
        return json_decode($response, true);
    }
}