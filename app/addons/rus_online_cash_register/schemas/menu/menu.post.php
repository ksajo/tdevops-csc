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

$schema['top']['administration']['items']['online_cash_register'] = [
    'attrs' => [
        'class'=>'is-addon'
    ],
    'href' => 'online_cash_register.receipts',
    'position' => 800,
    'subitems' => [
        'online_cash_register.receipts' => [
            'href' => 'online_cash_register.receipts',
            'position' => 100,
        ],
        'online_cash_register.logs' => [
            'href' => 'online_cash_register.logs',
            'position' => 200,
        ],
    ],
];

return $schema;
