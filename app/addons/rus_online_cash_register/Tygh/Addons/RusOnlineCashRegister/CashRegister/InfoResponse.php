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


namespace Tygh\Addons\RusOnlineCashRegister\CashRegister;

use Tygh\Addons\RusOnlineCashRegister\Receipt\Requisites;

/**
 * The response class represents request response on retrieve receipt data.
 *
 * @package Tygh\Addons\RusOnlineCashRegister\CashRegister
 */
class InfoResponse extends Response
{
    /** @var Requisites|null */
    protected $receipt_requisites;

    /**
     * Gets receipt requisites.
     *
     * @return Requisites|null
     */
    public function getReceiptRequisites()
    {
        return $this->receipt_requisites;
    }

    /**
     * Sets receipt requisites.
     *
     * @param Requisites $receipt_requisites
     */
    public function setReceiptRequisites(Requisites $receipt_requisites)
    {
        $this->receipt_requisites = $receipt_requisites;
    }
}