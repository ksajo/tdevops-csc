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

if ($mode == 'pickpoint_city') {

    $params = $_REQUEST;

    if (defined('AJAX_REQUEST') && $params['to_state']) {
    	$cities = fn_get_schema('pickpoint', 'cities', 'php', true);
        $pickpoint_cities = $cities[$params['to_state']];
        $city = key($pickpoint_cities);

        Registry::get('ajax')->assign('pickpoint_city', $city);
        exit();
    }
}