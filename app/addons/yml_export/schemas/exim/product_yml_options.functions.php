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


function fn_export_yml2_option($yml_option_id)
{
    static $options_type = null;

    if (!isset($options_type)) {
        $options_type = fn_get_schema('yml', 'options_type');
    }

    $option_type = "";
    if (isset($options_type[$yml_option_id])) {
        $option_type = $options_type[$yml_option_id]['value'];
    }

    return $option_type;
}

function fn_import_yml2_option($yml_option_type)
{
    static $options_type_codes = null;

    if (!isset($options_type_codes)) {
        $options_type = fn_get_schema('yml', 'options_type');

        foreach($options_type as $type_code => $type_data) {
            $options_type_codes[$type_data['value']] = $type_code;
        }
    }

    $option_type_code = false;
    if (isset($options_type_codes[$yml_option_type])) {
        $option_type_code = $options_type_codes[$yml_option_type];
    }

    return $option_type_code;
}