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

namespace Tygh\Addons\Retailcrm;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Addons\Retailcrm\Client\ApiClient;
use Tygh\Addons\Retailcrm\Converters\CustomerConverter;
use Tygh\Addons\Retailcrm\Converters\OrderConverter;
use Tygh\Registry;
use Tygh\Settings as StorageSettings;

/**
 * Class ServiceProvider is intended to register services and components of the "retailcrm" add-on to the application
 * container.
 *
 * @package Tygh\Addons\Retailcrm
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        $app['addons.retailcrm.api_client_factory'] = function (Container $app) {
            return function ($host, $api_key) {
                return new ApiClient('https://' . $host, $api_key);
            };
        };

        $app['addons.retailcrm.api_client'] = function (Container $app) {
            return call_user_func($app['addons.retailcrm.api_client_factory'],
                Registry::get('addons.retailcrm.retailcrm_host'),
                Registry::get('addons.retailcrm.retailcrm_api_key')
            );
        };

        $app['addons.retailcrm.settings'] = function () {
            return new Settings(StorageSettings::instance());
        };

        $app['addons.retailcrm.logger'] = function () {
            $dir = rtrim(Registry::get('config.dir.files'), '/');
            fn_mkdir($dir);

            return new Logger(
                $dir . '/retailcrm_logs/retailcrm.log',
                DEFAULT_FILE_PERMISSIONS,
                DEFAULT_DIR_PERMISSIONS
            );
        };

        $app['addons.retailcrm.converters.customer'] = function (Container $app) {
            return new CustomerConverter($app['addons.retailcrm.settings']);
        };

        $app['addons.retailcrm.converters.order'] = function (Container $app) {
            return new OrderConverter($app['addons.retailcrm.settings']);
        };

        $app['addons.retailcrm.service'] = function (Container $app) {
            return new Service(
                $app['addons.retailcrm.settings'],
                $app['addons.retailcrm.converters.customer'],
                $app['addons.retailcrm.converters.order'],
                $app['addons.retailcrm.api_client'],
                $app['addons.retailcrm.logger']
            );
        };
    }
}
