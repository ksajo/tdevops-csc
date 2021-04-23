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

namespace Tygh\Addons\RusTaxes;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Registry;

/**
 * Class ServiceProvider is intended to register services and components of the "rus_taxes" add-on to the application
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
        $app['addons.rus_taxes.receipt_factory'] = function () {
            return new ReceiptFactory(
                CART_PRIMARY_CURRENCY,
                TaxType::getMap(),
                Registry::get('settings.Appearance.cart_prices_w_taxes') === 'Y'
            );
        };
    }
}
