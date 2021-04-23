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


use Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt;

/**
 * Interface for online cash register.
 *
 * @package Tygh\Addons\RusOnlineCashRegister
 */
interface ICashRegister
{
    /**
     * Sends receipt to cash register.
     *
     * @param Receipt $receipt Instance of the receipt.
     *
     * @return SendResponse
     */
    public function send(Receipt $receipt);

    /**
     * Gets receipt info by UUID.
     *
     * @param string $uuid UUID of the receipt.
     *
     * @return InfoResponse
     */
    public function info($uuid);
}