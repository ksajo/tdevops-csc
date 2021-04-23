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

use Tygh\Addons\YandexCheckout\Enum\ProcessorScript;
use Tygh\Enum\ObjectStatuses;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    || !fn_allowed_for('MULTIVENDOR')
) {
    return [CONTROLLER_STATUS_OK];
}

if ($mode === 'update') {
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $yandex_checkout_for_marketplaces_payment_methods = fn_get_payments(
        [
            'processor_script' => ProcessorScript::YANDEX_CHECKOUT_FOR_MARKETPLACES,
            'status' => ObjectStatuses::ACTIVE,
        ]
    );
    $view->assign('is_yandex_checkout_for_marketplaces_used', !empty($yandex_checkout_for_marketplaces_payment_methods));
    $view->assign('is_vendor_plans_installed', Registry::get('addons.vendor_plans.status') === ObjectStatuses::ACTIVE);
    $view->assign('addons_page', fn_url('addons.manage'));
}

return [CONTROLLER_STATUS_OK];
