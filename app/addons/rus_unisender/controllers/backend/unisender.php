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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'add_selected') {
        fn_add_users_to_unisender($_REQUEST['user_ids']);

    } elseif ($mode == 'update') {

        fn_unisender_update_fields($_REQUEST['unisender_data']['fields']);

        return array(CONTROLLER_STATUS_OK, 'unisender.manage');
    }

    if ($mode == 'send_sms') {

        if (isset($_REQUEST['sms_data']) && !empty($_REQUEST['sms_data'])) {
            $params = $_REQUEST['sms_data'];

            fn_rus_unisender_send_sms($params['text'], $params['phone']); //user_sms
        }

        if (defined('AJAX_REQUEST')) {
            exit;
        } elseif (!empty($_REQUEST['return_url'])) {
            return array(CONTROLLER_STATUS_OK, $_REQUEST['return_url']);
        } else {
            return array(CONTROLLER_STATUS_OK, 'profiles.update?user_id=' . $_REQUEST['user_id']);
        }

    }

    return array(CONTROLLER_STATUS_OK, 'profiles.manage?user_type=C');
}

if ($mode == 'manage') {

    // [Page sections]
    Registry::set('navigation.tabs', array (
        'fields' => array (
            'title' => __('addons.rus_unisender.map_fields'),
            'js' => true
        ),
    ));
    // [/Page sections]

    if (fn_allowed_for('MULTIVENDOR') || Registry::get('runtime.company_id') || Registry::get('runtime.simple_ultimate')) {
        $fields = fn_unisender_get_fields();
        $unisender_fields = fn_unisender_get_unisender_fields();
        $profile_fields = fn_get_profile_fields('ALL', array(), CART_LANGUAGE);

        // FIXME: code for compatibility
        foreach ($fields as &$field) {
            if (empty($field['unisender_field_id'])) {
                $field['unisender_field_id'] = fn_unisender_compatibility($unisender_fields, $field);
            }
        }

        Tygh::$app['view']->assign('unisender_fields', $unisender_fields);
        Tygh::$app['view']->assign('profile_fields', $profile_fields);
        Tygh::$app['view']->assign('fields', $fields);
    }
}
