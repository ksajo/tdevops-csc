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

namespace Tygh\Addons\StorefrontRestApi;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Addons\StorefrontRestApi\ProfileFields\Hydrator;
use Tygh\Addons\StorefrontRestApi\ProfileFields\Manager;
use Tygh\Addons\StorefrontRestApi\ProfileFields\Validator;
use Tygh\Registry;

/**
 * Class ServiceProvider is intended to register services and components of the "Storefront REST API" add-on to the application
 * container.
 *
 * @package Tygh\Addons\ProductVariations
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        $app['addons.storefront_rest_api.profile_fields.manager'] = function(Container $app) {
            return new Manager(
                Registry::get('settings.General.quick_registration') === 'Y',
                Registry::get('settings.Checkout.address_position'),
                $app['addons.storefront_rest_api.profile_fields.validator'],
                $app['addons.storefront_rest_api.profile_fields.hydrator']
            );
        };

        $app['addons.storefront_rest_api.profile_fields.hydrator'] = function(Container $app) {
            return new Hydrator();
        };

        $app['addons.storefront_rest_api.profile_fields.validator'] = function(Container $app) {
            return new Validator();
        };
    }
}
