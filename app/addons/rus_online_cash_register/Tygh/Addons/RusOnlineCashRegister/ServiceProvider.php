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
use Pimple\ServiceProviderInterface;
use Tygh\Registry;

/**
 * Class ServiceProvider is intended to register services and components of the "rus_online_cash_register" add-on to the application
 * container.
 *
 * @package Tygh\Addons\Barcode
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        $app['addons.rus_online_cash_register.factory'] = function (Container $app) {
            return new Factory($app);
        };

        $app['addons.rus_online_cash_register.request_logger'] = function (Container $app) {
            return new RequestLogger($app['db']);
        };

        $app['addons.rus_online_cash_register.receipt_repository'] = function (Container $app) {
            return new ReceiptRepository($app['db']);
        };

        $app['addons.rus_online_cash_register.order_data_repository'] = function (Container $app) {
            return new OrderDataRepository($app['db']);
        };

        $app['addons.rus_online_cash_register.cash_register'] = function (Container $app) {
            /** @var Factory $factory */
            $factory = $app['addons.rus_online_cash_register.factory'];

            return $factory->createCashRegister(
                Registry::get('addons.rus_online_cash_register.atol_inn'),
                Registry::get('addons.rus_online_cash_register.atol_group_code'),
                Registry::get('addons.rus_online_cash_register.atol_payment_address'),
                Registry::get('addons.rus_online_cash_register.atol_login'),
                Registry::get('addons.rus_online_cash_register.atol_password'),
                null,
                Registry::get('addons.rus_online_cash_register.mode'),
                Registry::get('addons.rus_online_cash_register.api_version'),
                Registry::get('settings.Company.company_site_administrator')
            );
        };

        $app['addons.rus_online_cash_register.service'] = function (Container $app) {
            return new Service(
                $app['addons.rus_online_cash_register.cash_register'],
                $app['addons.rus_online_cash_register.receipt_repository'],
                $app['addons.rus_taxes.receipt_factory'],
                fn_rus_online_cash_register_get_payments_external_ids(),
                Registry::get('addons.rus_online_cash_register.currency'),
                Registry::get('addons.rus_online_cash_register.sno')
            );
        };
    }
}
