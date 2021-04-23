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

use Tygh\Enum\OutOfStockActions;
use Tygh\Tygh;
use Tygh\Enum\ProductTracking;

defined('BOOTSTRAP') or die('Access denied');

if ($mode === 'view' || $mode === 'quick_view') {
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    /** @var array $product */
    $product = $view->getTemplateVars('product');
    if (
        !$product
        || !$product['price']
        || (!$product['amount']
            && $product['tracking'] !== ProductTracking::DO_NOT_TRACK
            && $product['out_of_stock_actions'] !== OutOfStockActions::BUY_IN_ADVANCE
        )
    ) {
        return [CONTROLLER_STATUS_OK];
    }
    if (fn_allowed_for('ULTIMATE')) {
        $payments = fn_get_payments([
            'processor_script' => 'kupivkredit.php',
            'company_id' => $product['company_id'],
        ]);
    } else {
        $payments = fn_get_vendor_payment_methods([
            'processor_script' => 'kupivkredit.php',
            'company_id' => $product['company_id'],
        ]);
    }
    $view->assign('tinkoff_payments_exist', !empty($payments));
}

return [CONTROLLER_STATUS_OK];
