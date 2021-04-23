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

$cart = &Tygh::$app['session']['cart'];
$edit_step = !empty($cart['edit_step']) ? $cart['edit_step'] : '';

if ($mode == 'checkout') {
    if (!empty($cart['guest_checkout']) && $edit_step == 'step_two') {
        $policy_description = __('addons.rus_personal_data_processing.policy_description', array('[name_button]' => __('continue'), '[link]' => fn_url('personal_data.manage')));
    } else {
        $policy_description = __('addons.rus_personal_data_processing.policy_description', array('[name_button]' => __('register'), '[link]' => fn_url('personal_data.manage')));
    }

    Tygh::$app['view']->assign('policy_description', $policy_description);
}