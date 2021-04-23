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

use Tygh\Addons\RusTaxes\TaxType;
use Tygh\Tygh;

defined('BOOTSTRAP') or die('Access denied');

/** @var string $mode */

if ($mode === 'update' || $mode === 'add') {
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];
    $view->assign('tax_types', TaxType::getList());
} elseif ($mode === 'convert_legacy_taxes') {
    /** @var \Tygh\Database\Connection $db */
    $db = Tygh::$app['db'];
    $taxes = $db->getColumn('SELECT tax_id FROM ?:taxes WHERE tax_type IN (?a)', [TaxType::VAT_18, TaxType::VAT_118]);

    if ($taxes) {
        $destinations = fn_get_destinations();

        $tax_types = TaxType::getList(true);

        $taxes_list = '';
        foreach ($taxes as $tax_id) {

            $tax = fn_get_tax($tax_id);

            $old_tax_type = $tax['tax_type'];

            if ($old_tax_type === TaxType::VAT_18) {
                $tax['tax_type'] = TaxType::VAT_20;
            } elseif ($old_tax_type === TaxType::VAT_118) {
                $tax['tax_type'] = TaxType::VAT_120;
            }

            $tax['rates'] = [];
            foreach ($destinations as $destination) {
                $tax['rates'][$destination['destination_id']] = [
                    'rate_value' => 20,
                    'rate_type'  => 'P',
                ];
            }

            fn_update_tax($tax, $tax_id);

            $taxes_list .= __('rus_taxes.tax_rates_changes.tax_changed', [
                '[tax_url]'      => fn_url('taxes.update?tax_id=' . $tax_id),
                '[tax]'          => $tax['tax'],
                '[old_tax_type]' => $tax_types[$old_tax_type]['name'],
                '[new_tax_type]' => $tax_types[$tax['tax_type']]['name'],
            ]);
        }

        $failing_services = $shipping_methods = $payment_methods = '';

        $shippings = $db->getArray(
            'SELECT s.shipping_id, sd.shipping FROM ?:shippings AS s'
            . ' LEFT JOIN ?:shipping_descriptions AS sd ON sd.shipping_id = s.shipping_id'
            . ' WHERE s.service_id IN (SELECT ss.service_id FROM ?:shipping_services AS ss WHERE ss.module = ?s AND ss.code = ?s)'
            . ' AND sd.lang_code = ?s',
            'yandex',
            'yandex',
            CART_LANGUAGE
        );
        if ($shippings) {
            foreach ($shippings as &$shipping) {
                $shipping = __('rus_taxes.tax_rates_changes.failing_services.shipping', [
                    '[item_url]'  => fn_url('shippings.update?shipping_id=' . $shipping['shipping_id']),
                    '[item_text]' => $shipping['shipping'],
                ]);
            }
            unset($shipping);
            $shipping_methods .= __('rus_taxes.tax_rates_changes.failing_services.shippings', [
                '[shippings_list]' => implode('', $shippings)
            ]);
        }

        $payments = $db->getArray(
            'SELECT p.payment_id, pd.payment FROM ?:payments AS p'
            . ' LEFT JOIN ?:payment_descriptions AS pd ON pd.payment_id = p.payment_id'
            . ' WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script = ?s)'
            . ' AND pd.lang_code = ?s',
            'paymaster.php',
            CART_LANGUAGE
        );
        if ($payments) {
            foreach ($payments as &$payment) {
                $payment = __('rus_taxes.tax_rates_changes.failing_services.payment', [
                    '[item_text]' => $payment['payment'],
                ]);
            }
            unset($payment);
            $payment_methods .= __('rus_taxes.tax_rates_changes.failing_services.payments', [
                '[payments_list]' => implode('', $payments)
            ]);
        }

        if ($shippings || $payments) {
            $failing_services = __('rus_taxes.tax_rates_changes.failing_services', [
                '[shipping_methods]' => $shipping_methods,
                '[payment_methods]'  => $payment_methods,
            ]);
        }

        fn_set_notification(
            'I',
            __('rus_taxes.tax_rates_changes.title'),
            __('rus_taxes.tax_rates_changes.results', [
                '[taxes_list]'       => $taxes_list,
                '[failing_services]' => $failing_services,
            ]),
            'K'
        );
    }

    return [CONTROLLER_STATUS_OK, 'taxes.manage'];
}
