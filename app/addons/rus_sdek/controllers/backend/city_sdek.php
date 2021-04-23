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
use Tygh\Shippings\RusSdek;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'autocomplete_city') {
    $params = $_REQUEST;

    if (defined('AJAX_REQUEST') && $params['q']) {
        $params['check_state'] = db_get_field(
            'SELECT ?:states.code FROM ?:state_descriptions'
            . ' LEFT JOIN ?:states ON ?:state_descriptions.state_id = ?:states.state_id'
            . ' WHERE ?:state_descriptions.state = ?s',
            $params['check_state']
        );

        $cities = fn_rus_cities_find_cities($params);
        $select = fn_rus_cities_format_to_autocomplete($cities);

        Registry::get('ajax')->assign('autocomplete', $select);
        exit();
    }

} elseif ($mode == 'sdek_get_city_data') {
    $params = $_REQUEST;

    if (defined('AJAX_REQUEST')) {
        $location['country'] = 'RU';

        if (!empty($params['check_country']) && $params['check_country'] != 'undefined') {
            $location['country'] = $params['check_country'];

            if (!empty($params['check_state']) && $params['check_state'] != 'undefined') {
                $state_code = db_get_field("SELECT b.code FROM ?:state_descriptions as a LEFT JOIN ?:states as b ON a.state_id = b.state_id WHERE a.state = ?s ", $params['check_state']);

                if (!empty($state_code)) {
                    $location['state'] = $state_code;
                }
            }
        }

        $location['city'] = $params['var_city'];

        $data = RusSdek::cityId($location);

        $city_data = array(
            'from_city_id' => $data,
        );

        Tygh::$app['view']->assign('sdek_new_city_data', $city_data);
        Tygh::$app['view']->display('addons/rus_sdek/views/shippings/components/services/sdek.tpl');
        exit;
    }

} elseif ($mode == 'select_state') {
    if (defined('AJAX_REQUEST')) {
        $states = fn_get_all_states();

        Tygh::$app['view']->assign('_country', $_REQUEST['country']);
        Tygh::$app['view']->assign('_state', $_REQUEST['state']);
        Tygh::$app['view']->assign('states', $states);
        Tygh::$app['view']->display('addons/rus_sdek/views/shippings/components/services/sdek.tpl');
        exit;
    }
}
