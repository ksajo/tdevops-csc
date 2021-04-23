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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

define('PAYLER_TIMEOUT', 45);

// Time for awaiting callback
define('RBK_MAX_AWAITING_TIME', 20);
define('RK_MAX_AWAITING_TIME', 10);
define('YM_MAX_AWAITING_TIME', 10);

define('PAYMASTER_MAX_AWAITING_TIME', 10);
define('PAYANYWAY_GATEWAY_URL', 'https://kassa.payanyway.ru');

fn_define('YANDEX_MONEY_CODE_SUCCESS', 0);
fn_define('YANDEX_MONEY_CODE_AUTH_ERROR', 1);
fn_define('YANDEX_MONEY_CODE_TRANSFER_REFUSED', 100);
fn_define('YANDEX_MONEY_CODE_REQUEST_PARSE_ERROR', 200);
