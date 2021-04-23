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

/**
 * Model of receipt payment.
 *
 * @package Tygh\Addons\RusOnlineCashRegister\Receipt
 */
class Payment
{
    /** @var int */
    protected $type;

    /** @var float */
    protected $sum;

    /**
     * Payment constructor.
     *
     * @param int    $type  Payment type on cash register.
     * @param string $sum   Paid sum.
     */
    public function __construct($type, $sum)
    {
        $this->type = (int) $type;
        $this->sum = (float) $sum;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return float
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * Convert to array.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'type' => $this->type,
            'sum' => $this->sum,
        );
    }

    /**
     * Create object from array,
     *
     * @param array $data
     *
     * @return Payment
     */
    public static function fromArray(array $data)
    {
        return new self($data['type'], $data['sum']);
    }
}