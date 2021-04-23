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
use Tygh\Ym\Yml2;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * @var string $mode
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'generate') {
    $access_key = !empty($_REQUEST['access_key']) ? $_REQUEST['access_key'] : '';
    $price_id = !empty($_REQUEST['price_id']) ? $_REQUEST['price_id'] : 0;

    if (empty($price_id) && !empty($access_key)) {
        $price_id = fn_yml_get_price_id($access_key);
    }

    $options = fn_yml_get_options($price_id);

    if (isset($options['used_for_retailcrm']) && $options['used_for_retailcrm'] === 'Y') {
        /** @var \Composer\Autoload\ClassLoader $class_loader */
        $class_loader = Registry::get('class_loader');

        $class_loader->addClassMap(array(
            'Tygh\\Ym\\Offers' => Registry::get('config.dir.addons') . 'retailcrm/Tygh/Addons/Retailcrm/Ym/Offers.php'
        ));
    }
}
