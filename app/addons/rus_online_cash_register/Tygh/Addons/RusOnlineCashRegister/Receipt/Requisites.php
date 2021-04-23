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

/**
 * Model of the receipt requisites.
 *
 * @package Tygh\Addons\RusOnlineCashRegister
 */
class Requisites
{
    /** @var string|null Fiscal the receipt number */
    protected $fiscal_receipt_number;

    /** @var string|null Shift number */
    protected $shift_number;

    /** @var DateTime|null Fiscal timestamp */
    protected $receipt_timestamp;

    /** @var float|null Fiscal document total sum */
    protected $receipt_total;

    /** @var string|null Fiscal the register number */
    protected $fn_number;

    /** @var string|null Number of fiscal register*/
    protected $ecr_registration_number;

    /** @var string|null Fiscal the document number */
    protected $fiscal_document_number;

    /** @var string|null Fiscal the document attribute */
    protected $fiscal_document_attribute;

    /**
     * Gets shift number.
     *
     * @return string|null
     */
    public function getShiftNumber()
    {
        return $this->shift_number;
    }

    /**
     * Sets shift number.
     *
     * @param string $shift_number
     */
    public function setShiftNumber($shift_number)
    {
        $this->shift_number = $shift_number;
    }

    /**
     * Gets number of fiscal register.
     *
     * @return string|null
     */
    public function getEcrRegistrationNumber()
    {
        return $this->ecr_registration_number;
    }

    /**
     * Sets number of fiscal register.
     *
     * @param string $ecr_registration_number
     */
    public function setEcrRegistrationNumber($ecr_registration_number)
    {
        $this->ecr_registration_number = $ecr_registration_number;
    }

    /**
     * Gets fiscal document number.
     *
     * @return string|null
     */
    public function getFiscalDocumentNumber()
    {
        return $this->fiscal_document_number;
    }

    /**
     * Sets fiscal document number.
     *
     * @param string $fiscal_document_number
     */
    public function setFiscalDocumentNumber($fiscal_document_number)
    {
        $this->fiscal_document_number = $fiscal_document_number;
    }

    /**
     * Gets fiscal document attribute.
     *
     * @return string|null
     */
    public function getFiscalDocumentAttribute()
    {
        return $this->fiscal_document_attribute;
    }

    /**
     * Sets fiscal document number.
     *
     * @param string $fiscal_document_attribute
     */
    public function setFiscalDocumentAttribute($fiscal_document_attribute)
    {
        $this->fiscal_document_attribute = $fiscal_document_attribute;
    }

    /**
     * Gets fiscal timestamp.
     *
     * @return DateTime|null
     */
    public function getReceiptTimestamp()
    {
        return $this->receipt_timestamp;
    }

    /**
     * Sets fiscal timestamp.
     *
     * @param DateTime|string|int $receipt_timestamp
     */
    public function setReceiptTimestamp($receipt_timestamp)
    {
        if ($receipt_timestamp instanceof DateTime) {
            $this->receipt_timestamp = $receipt_timestamp;
        } elseif (is_numeric($receipt_timestamp)) {
            $this->receipt_timestamp = DateTime::createFromFormat('U', $receipt_timestamp);
            $this->receipt_timestamp->setTimezone(new DateTimeZone(date_default_timezone_get()));
        } elseif ($receipt_timestamp) {
            $this->receipt_timestamp = date_create($receipt_timestamp);
        }
    }

    /**
     * Gets fiscal the register number
     *
     * @return null|string
     */
    public function getFnNumber()
    {
        return $this->fn_number;
    }

    /**
     * Sets fiscal the register number
     *
     * @param null|string $fn_number
     */
    public function setFnNumber($fn_number)
    {
        $this->fn_number = $fn_number;
    }

    /**
     * Gets fiscal the receipt number
     *
     * @return null|string
     */
    public function getFiscalReceiptNumber()
    {
        return $this->fiscal_receipt_number;
    }

    /**
     * Sets fiscal the receipt number
     *
     * @param null|string $fiscal_receipt_number
     */
    public function setFiscalReceiptNumber($fiscal_receipt_number)
    {
        $this->fiscal_receipt_number = $fiscal_receipt_number;
    }

    /**
     * Gets fiscal document total sum.
     *
     * @return float|null
     */
    public function getReceiptTotal()
    {
        return $this->receipt_total;
    }

    /**
     * Sets fiscal document total sum.
     *
     * @param float $receipt_total
     */
    public function setReceiptTotal($receipt_total)
    {
        $this->receipt_total = $receipt_total;
    }

    /**
     * Merge data.
     *
     * @param Requisites $receipt
     */
    public function merge(Requisites $receipt)
    {
        $data = array_filter($receipt->toArray());
        $this->loadFromArray($data);
    }

    /**
     * Convert object to array.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'fiscal_receipt_number' => $this->fiscal_receipt_number,
            'shift_number' => $this->shift_number,
            'receipt_timestamp' => $this->receipt_timestamp ? $this->receipt_timestamp->getTimestamp() : null,
            'receipt_total' => $this->receipt_total,
            'fn_number' => $this->fn_number,
            'ecr_registration_number' => $this->ecr_registration_number,
            'fiscal_document_number' => $this->fiscal_document_number,
            'fiscal_document_attribute' => $this->fiscal_document_attribute,
        );
    }

    /**
     * Configure receipt from array.
     *
     * @param array $data
     */
    public function loadFromArray(array $data)
    {
        if (array_key_exists('fiscal_receipt_number', $data)) {
            $this->setFiscalReceiptNumber($data['fiscal_receipt_number']);
        }

        if (array_key_exists('shift_number', $data)) {
            $this->setShiftNumber($data['shift_number']);
        }

        if (array_key_exists('fn_number', $data)) {
            $this->setFnNumber($data['fn_number']);
        }

        if (array_key_exists('receipt_timestamp', $data)) {
            $this->setReceiptTimestamp($data['receipt_timestamp']);
        }

        if (array_key_exists('receipt_total', $data)) {
            $this->setReceiptTotal($data['receipt_total']);
        }

        if (array_key_exists('ecr_registration_number', $data)) {
            $this->setEcrRegistrationNumber($data['ecr_registration_number']);
        }

        if (array_key_exists('fiscal_document_number', $data)) {
            $this->setFiscalDocumentNumber($data['fiscal_document_number']);
        }

        if (array_key_exists('fiscal_document_attribute', $data)) {
            $this->setFiscalDocumentAttribute($data['fiscal_document_attribute']);
        }
    }

    /**
     * Create object from array.
     *
     * @param array $data
     *
     * @return Requisites
     */
    public static function fromArray(array $data)
    {
        $self = new self;
        $self->loadFromArray($data);

        return $self;
    }
}