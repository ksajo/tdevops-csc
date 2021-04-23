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

/**
 * Filters and validates company inn.
 *
 * @param string $value     New setting value.
 * @param string $old_value Old setting value.
 */
function fn_settings_actions_addons_rus_online_cash_register_inn($value, $old_value)
{
    $value = trim($value);

    if (function_exists('ctype_digit')) {
        $result = ctype_digit($value);
    } else {
        $result = preg_match('/^[0-9]+$/', $value) ? true : false;
    }

    if (!$result) {
        fn_set_notification('E', __('error'), __('rus_online_cash_register.inn_is_invalid'));
    }
}
