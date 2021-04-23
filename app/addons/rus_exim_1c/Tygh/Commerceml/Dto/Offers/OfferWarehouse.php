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


namespace Tygh\Commerceml\Dto\Offers;


class OfferWarehouse
{
    /** @var int */
    protected $id;

    /** @var int */
    protected $amount;

    /**
     * OfferWarehouse constructor.
     *
     * @param int $id
     * @param int $amount
     */
    protected function __construct($id, $amount)
    {
        $this->id = (int) $id;
        $this->amount = (int) $amount;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $warehouse_id
     * @param int $amount
     *
     * @return \Tygh\Commerceml\Dto\Offers\OfferWarehouse
     */
    public static function create($warehouse_id, $amount)
    {
        return new self($warehouse_id, $amount);
    }
}