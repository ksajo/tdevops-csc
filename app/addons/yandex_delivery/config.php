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

fn_define('YD_DISPLAY_TYPE', 'cms');
fn_define('YD_CACHE_DAY', SECONDS_IN_DAY);
fn_define('YD_CACHE_DELIVERY', 60 * 10); // 10 min
fn_define("ORDER_DRAFT_STATUS", -2);
fn_define('YD_DELIVERY_COURIER', 'TODOOR');
fn_define('YD_DELIVERY_PICKUP', 'PICKUP');
fn_define('YD_MODULE_NAME', 'yandex');


fn_define("YD_ERROR_SUCCESS", "");
fn_define("YD_ERROR_WRONG_PARAM", "Неверный тип входных данных!");
fn_define("YD_ERROR_VALIDATION_EMPTY", "Ошибка валидации: заполнены не все поля!");
fn_define("YD_ERROR_VALIDATION", "Ошибка валидации: поля заполнены неверно!");
fn_define("YD_ERROR_CONFIG", "Ошибка настроек");
