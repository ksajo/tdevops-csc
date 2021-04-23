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


namespace Tygh\Addons\RusOnlineCashRegister\Receipt;

use DateTime;
use DateTimeZone;
use Tygh\Addons\RusTaxes\Receipt\Receipt as BaseReceipt;

/**
 * Model of the receipt
 *
 * @package Tygh\Addons\RusOnlineCashRegister\Receipt
 */
class Receipt
{
    const TYPE_SELL = 1;

    const TYPE_SELL_REFUND = 2;

    const TYPE_BUY = 3;

    const TYPE_BUY_REFUND = 4;

    const TYPE_SELL_CORRECTION = 5;

    const TYPE_BUY_CORRECTION = 6;

    const STATUS_WAIT = 1;

    const STATUS_DONE = 2;

    const STATUS_FAIL = 3;

    const PAYMENT_METHOD_FULL_PAYMENT = 'full_payment';

    const PAYMENT_METHOD_FULL_PREPAYMENT = 'full_prepayment';

    /** @var int|null */
    protected $id;

    /** @var string|null */
    protected $object_type;

    /** @var int|null */
    protected $object_id;

    /** @var string|null Unique identifier of document */
    protected $uuid;

    /** @var int|null receipt status (wait = 0, done = 1, fail = 2) */
    protected $status;

    /** @var string|null Status message of the receipt */
    protected $status_message;

    /** @var int|null receipt type (sale = 0, refund = 1, buy = 2) */
    protected $type;

    /** @var string|null taxation system */
    protected $sno;

    /** @var DateTime|null */
    protected $timestamp;

    /** @var string|null */
    protected $email;

    /** @var string|null */
    protected $phone;

    /** @var float */
    protected $total = 0;

    /** @var Item[] */
    protected $items = array();

    /** @var Payment[] */
    protected $payments = array();

    /** @var Requisites|null */
    protected $requisites;

    /** @var string */
    protected $currency;

    /** @var string receipt payment method ('full_prepayment', 'full_payment') */
    protected $payment_method;

    /**
     * Gets receipt identifier.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets receipt identifier.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Sets receipt items.
     * 
     * @param Item[] $items
     */
    public function setItems(array $items)
    {
        $this->items = array();

        foreach ($items as $item) {
            if (is_array($item)) {
                $item = Item::fromArray($item);
            }

            $this->setItem($item);
        }
    }

    /**
     * Sets receipt item.
     *
     * @param Item $item
     */
    public function setItem(Item $item)
    {
        $this->items[] = $item;
        $this->total += $item->getSum();
    }

    /**
     * Sets receipt payments.
     *
     * @param Payment[] $payments
     */
    public function setPayments(array $payments)
    {
        $this->payments = array();

        foreach ($payments as $payment) {
            if (is_array($payment)) {
                $payment = Payment::fromArray($payment);
            }

            $this->setPayment($payment);
        }
    }

    /**
     * Sets receipt payment.
     *
     * @param Payment $payment
     */
    public function setPayment(Payment $payment)
    {
        $this->payments[] = $payment;
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
     * Gets customer email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Gets customer phone.
     *
     * @return string|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Gets total sum.
     *
     * @return float|null
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Gets receipt items.
     *
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Gets receipt payments.
     *
     * @return Payment[]
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Gets object type.
     *
     * @return string|null
     */
    public function getObjectType()
    {
        return $this->object_type;
    }

    /**
     * Get object identifier.
     *
     * @return int|null
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * Gets receipt type (sale = 0, refund = 1, buy = 2).
     *
     * @return int|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets receipt type.
     *
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Sets receipt reference object type.
     *
     * @param string $object_type
     */
    public function setObjectType($object_type)
    {
        $this->object_type = $object_type;
    }

    /**
     * Sets receipt reference object identifier.
     *
     * @param int $object_id
     */
    public function setObjectId($object_id)
    {
        $this->object_id = $object_id;
    }

    /**
     * Sets receipt timestamp.
     *
     * @param DateTime|int $timestamp
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
     * Sets receipt customer email address.
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Sets receipt customer phone.
     *
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * Sets receipt custom currency.
     *
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * Gets receipt custom currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Sets receipt payment method.
     *
     * @param string $payment_method
     */
    public function setPaymentMethod($payment_method)
    {
        $this->payment_method = $payment_method;
    }

    /**
     * Gets receipt payment method.
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * Gets receipt requisites.
     *
     * @return Requisites
     */
    public function getRequisites()
    {
        return $this->requisites;
    }

    /**
     * Sets receipt requisites.
     * 
     * @param Requisites $requisites
     */
    public function setRequisites(Requisites $requisites)
    {
        $this->requisites = $requisites;
    }

    /**
     * @return bool
     */
    public function isTypeSell()
    {
        return $this->type === self::TYPE_SELL;
    }

    /**
     * @return bool
     */
    public function isTypeSellRefund()
    {
        return $this->type === self::TYPE_SELL_REFUND;
    }

    /**
     * @return bool
     */
    public function isTypeRefund()
    {
        return $this->type === self::TYPE_SELL_REFUND;
    }

    /**
     * @return bool
     */
    public function isTypeBuy()
    {
        return $this->type === self::TYPE_BUY;
    }

    /**
     * Gets receipt type code.
     *
     * @return null|string
     */
    public function getTypeCode()
    {
        switch ($this->type) {
            case self::TYPE_SELL:
                $result = 'sell';
                break;
            case self::TYPE_SELL_REFUND:
                $result = 'sell_refund';
                break;
            case self::TYPE_BUY:
                $result = 'buy';
                break;
            case self::TYPE_BUY_REFUND:
                $result = 'buy_refund';
                break;
            default:
                $result = null;
                break;
        }

        return $result;
    }

    /**
     * Gets receipt taxation system
     *
     * @return null|string
     */
    public function getSno()
    {
        return $this->sno;
    }

    /**
     * Sets receipt taxation system
     *
     * @param null|string $sno
     */
    public function setSno($sno)
    {
        $this->sno = $sno;
    }

    /**
     * Sets unique receipt identifier.
     *
     * @param string $uuid
     */
    public function setUUID($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * Gets unique receipt identifier.
     *
     * @return string
     */
    public function getUUID()
    {
        return $this->uuid;
    }

    /**
     * Gets receipt status.
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Gets receipt status code.
     *
     * @return null|string
     */
    public function getStatusCode()
    {
        switch ($this->status) {
            case self::STATUS_DONE:
                $result = 'done';
                break;
            case self::STATUS_WAIT:
                $result = 'wait';
                break;
            case self::STATUS_FAIL:
                $result = 'fail';
                break;
            default:
                $result = null;
                break;
        }

        return $result;
    }

    /**
     * Sets receipt status.
     *
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isStatusWait()
    {
        return $this->status == self::STATUS_WAIT;
    }

    /**
     * @return bool
     */
    public function isStatusFail()
    {
        return $this->status == self::STATUS_FAIL;
    }

    /**
     * @return bool
     */
    public function isStatusDone()
    {
        return $this->status == self::STATUS_DONE;
    }


    /**
     * Gets status message.
     *
     * @return string|null
     */
    public function getStatusMessage()
    {
        return $this->status_message;
    }

    /**
     * Sets receipt status message.
     *
     * @param string $status_message
     */
    public function setStatusMessage($status_message)
    {
        $this->status_message = $status_message;
    }

    /**
     * Gets external identifier.
     */
    public function getExternalId()
    {
        return $this->object_type . '_' . $this->object_id . '_' . $this->getTimestamp()->getTimestamp();
    }

    /**
     * Convert object to array.
     *
     * @return array
     */
    public function toArray()
    {
        $result = [
            'id' => $this->id,
            'object_id' => $this->object_id,
            'object_type' => $this->object_type,
            'uuid' => $this->uuid,
            'status' => $this->status,
            'status_message' => $this->status_message,
            'type' => $this->type,
            'sno' => $this->sno,
            'timestamp' => $this->timestamp ? $this->timestamp->getTimestamp() : null,
            'email' => $this->email,
            'phone' => $this->phone,
            'requisites' => $this->requisites ? $this->requisites->toArray() : null,
            'items' => array(),
            'payments' => array(),
            'currency' => $this->currency
        ];

        foreach ($this->items as $item) {
            $result['items'][] = $item->toArray();
        }

        foreach ($this->payments as $item) {
            $result['payments'][] = $item->toArray();
        }

        return $result;
    }

    /**
     * Load receipt from array.
     *
     * @param array $data
     */
    public function loadFromArray(array $data)
    {
        if (array_key_exists('id', $data)) {
            $this->setId($data['id']);
        }

        if (array_key_exists('object_id', $data)) {
            $this->setObjectId($data['object_id']);
        }

        if (array_key_exists('object_type', $data)) {
            $this->setObjectType($data['object_type']);
        }

        if (array_key_exists('type', $data)) {
            $this->setType($data['type']);
        }

        if (array_key_exists('sno', $data)) {
            $this->setSno($data['sno']);
        }

        if (array_key_exists('timestamp', $data)) {
            $this->setTimestamp($data['timestamp']);
        }

        if (array_key_exists('email', $data)) {
            $this->setEmail($data['email']);
        }

        if (array_key_exists('phone', $data)) {
            $this->setPhone($data['phone']);
        }

        if (array_key_exists('requisites', $data)) {
            $this->setRequisites($data['requisites']);
        }

        if (array_key_exists('items', $data)) {
            $this->setItems($data['items']);
        }

        if (array_key_exists('payments', $data)) {
            $this->setPayments($data['payments']);
        }

        if (array_key_exists('uuid', $data)) {
            $this->setUUID($data['uuid']);
        }

        if (array_key_exists('status', $data)) {
            $this->setStatus($data['status']);
        }

        if (array_key_exists('status_message', $data)) {
            $this->setStatusMessage($data['status_message']);
        }

        if (array_key_exists('currency', $data)) {
            $this->setCurrency($data['currency']);
        }

        if (array_key_exists('payment_method', $data)) {
            $this->setPaymentMethod($data['payment_method']);
        }
    }

    /**
     * Create object from array.
     *
     * @param array $data
     *
     * @return Receipt
     */
    public static function fromArray(array $data)
    {
        $self = new self();
        $self->loadFromArray($data);

        return $self;
    }

    /**
     * Creates receipt instances from base receipt.
     *
     * @param BaseReceipt $receipt
     *
     * @return Receipt
     */
    public static function fromBaseReceipt(BaseReceipt $receipt)
    {
        $self = new self();

        $self->setEmail($receipt->getEmail());
        $self->setPhone($receipt->getPhone());

        foreach ($receipt->getItems() as $item) {
            $self->setItem(Item::fromBaseReceiptItem($item));
        }

        return $self;
    }
}