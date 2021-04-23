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

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'subscribe_policy') {
    $subscribe_confidentiality = Registry::get('addons.rus_personal_data_processing.subscribe_confidentiality');
    $request_active_consent = Registry::get('addons.rus_personal_data_processing.request_active_consent');

    Tygh::$app['view']->assign('autoclicked', $_REQUEST['autoclicked']);

    if ($subscribe_confidentiality == 'Y') {
        if ($request_active_consent == 'Y') {
            Tygh::$app['view']->assign(
                'subscribe_text_policy',
                __(
                    'addons.rus_personal_data_processing.accepting_checkbox',
                    array(
                        '[link]' => fn_url('personal_data.manage')
                    )
                )
            );
        } else {
            Tygh::$app['view']->assign(
                'subscribe_text_policy',
                __(
                    'addons.rus_personal_data_processing.subscribe_text_policy',
                    array(
                        '[link]' => fn_url('personal_data.manage')
                    )
                )
            );
        }
    }

    Tygh::$app['view']->assign('request_active_consent', $request_active_consent == 'Y');
}

if ($mode == 'manage') {
    fn_add_breadcrumb(__('addons.rus_personal_data_processing.title_personal_data'));

    if (fn_allowed_for('MULTIVENDOR')) {
        $company_data = [
            'company' => Registry::get('settings.Company.company_name')
        ];
    } else {
        $company_id = fn_get_runtime_company_id();
        $company_data = fn_get_company_data($company_id);
    }

    $company_url = fn_url();
    $company_name = $company_data['company'];
    $company_storefront = parse_url($company_url);
    if (isset($company_storefront['path']) && strlen($company_storefront['path']) > 1) {
        $company_storefront = $company_storefront['host'] . $company_storefront['path'];
    } else {
        $company_storefront = $company_storefront['host'];
    }

    $policy_description = __('addons.rus_personal_data_processing.confidentiality_policy_description', array('[company_name]' => $company_name, '[company_url]' => $company_url, '[company_storefront]' => $company_storefront));

    Tygh::$app['view']->assign('policy_description', $policy_description);
}