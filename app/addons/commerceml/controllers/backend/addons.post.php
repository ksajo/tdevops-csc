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

defined('BOOTSTRAP') or die('Access denied');

/** @var string $mode */

if (
    $_SERVER['REQUEST_METHOD'] === 'GET'
    && $mode === 'update'
    && $_REQUEST['addon'] === 'commerceml'
) {
    $options = Tygh::$app['view']->getTemplateVars('options');

    $addon_setting_ids = [];

    foreach ($options as $tab_id => $tab_options) {
        foreach ($tab_options as $setting_id => $option_item) {
            $addon_setting_ids[$tab_id][$option_item['name']] = $setting_id;
        }
    }

    Tygh::$app['view']->assign('addon_setting_ids', $addon_setting_ids);
}
