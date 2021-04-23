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

// rus_build_kupivkredit dbazhenov

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_define('KVK_API_URL', 'loans.tinkoff.ru');
fn_define('KVK_API_TEST_URL', 'loans-qa.tcsbank.ru');
fn_define('KVK_INSTRUCTION_URL', 'https://www.tinkoff.ru/business/loans/');
