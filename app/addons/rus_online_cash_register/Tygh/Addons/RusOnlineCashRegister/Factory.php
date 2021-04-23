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


use Pimple\Container;
use Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol\CashRegister;
use Tygh\Addons\RusOnlineCashRegister\CashRegister\ICashRegister;
use Tygh\Addons\RusTaxes\ReceiptFactory;
use Tygh\Http;

/**
 * Class provides methods for creating cash register instance and cash register service.
 *
 * @package Tygh\Addons\RusOnlineCashRegister
 */
class Factory
{
    protected $container;

    /**
     * Factory constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Create CashRegister instance.
     *
     * @param string        $inn                Company INN
     * @param string        $group_code         ATOL Group code
     * @param string        $payment_address    ATOL Payment address
     * @param string        $login              ATOL login
     * @param string        $password           ATOL password
     * @param RequestLogger $request_logger     Instance of the RequestLogger
     * @param string        $mode               ATOL mode
     *
     * @return ICashRegister
     */
    public function createCashRegister($inn, $group_code, $payment_address, $login, $password, $request_logger = null, $mode = 'live', $api_version = '4', $company_email = 'admin@example.com')
    {
        return new CashRegister(
            $inn,
            $group_code,
            $payment_address,
            $login,
            $password,
            fn_url('online_cash_register.callback_atol', 'C'),
            new Http(),
            $request_logger ? $request_logger : $this->container['addons.rus_online_cash_register.request_logger'],
            $mode,
            $api_version,
            $company_email
        );
    }

    /**
     * Create CashRegister instance by params.
     *
     * @param array $params
     *
     * @return ICashRegister
     */
    public function createCashRegisterByArray(array $params)
    {
        return $this->createCashRegister(
            isset($params['atol_inn']) ? $params['atol_inn'] : null,
            isset($params['atol_group_code']) ? $params['atol_group_code'] : null,
            isset($params['atol_payment_address']) ? $params['atol_payment_address'] : null,
            isset($params['atol_login']) ? $params['atol_login'] : null,
            isset($params['atol_password']) ? $params['atol_password'] : null,
            null,
            isset($params['mode']) ? $params['mode'] : 'live',
            isset($params['api_version']) ? $params['api_version'] : '4'
        );
    }
}
