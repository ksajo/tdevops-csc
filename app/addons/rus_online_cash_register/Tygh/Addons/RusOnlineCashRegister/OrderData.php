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

use DateTime;
use DateTimeZone;

/**
 * Model of the additional order data. Provides methods for checking current payment status.
 *
 * @package Tygh\Addons\RusOnlineCashRegister
 */
class OrderData
{
    const STATUS_NONE = 0;
    const STATUS_PAID = 1;
    const STATUS_REFUND = 2;
    const STATUS_PREPAID = 3;

    /** @var int */
    protected $order_id;

    /** @var int */
    protected $status;

    /** @var DateTime */
    protected $timestamp;

    /**
     * Gets order identifier.
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * Sets order identifier.
     *
     * @param int $order_id
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * Gets order status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets status.
     *
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = (int) $status;
    }

    /**
     * Gets timestamp.
     *
     * @return DateTime|null
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Sets timestamp.
     *
     * @param DateTime|string|int $timestamp
     */
    public function setTimestamp($timestamp)
    {
        if ($timestamp instanceof DateTime) {
            $this->timestamp = $timestamp;
        } elseif (is_numeric($timestamp)) {
            $this->timestamp = DateTime::createFromFormat('U', $timestamp);
            $this->timestamp->setTimezone(new DateTimeZone(date_default_timezone_get()));
        } else {
            $this->timestamp = date_create($timestamp);
        }
    }

    /**
     * Convert object to array.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'order_id' => $this->order_id,
            'status' => $this->status,
            'timestamp' => $this->timestamp ? $this->timestamp->getTimestamp() : null
        );
    }

    /**
     * @return bool Whether to the order status is paid.
     */
    public function isStatusPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * @return bool Whether to the order status is prepaid.
     */
    public function isStatusPrepaid()
    {
        return $this->status === self::STATUS_PREPAID;
    }

    /**
     * @return bool Whether to the order status is refund.
     */
    public function isStatusRefund()
    {
        return $this->status === self::STATUS_REFUND;
    }

    /**
     * Create instance from array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data)
    {
        $self = new self;

        if (array_key_exists('order_id', $data)) {
            $self->setOrderId($data['order_id']);
        }

        if (array_key_exists('status', $data)) {
            $self->setStatus($data['status']);
        }

        if (array_key_exists('timestamp', $data)) {
            $self->setTimestamp($data['timestamp']);
        }

        return $self;
    }
}