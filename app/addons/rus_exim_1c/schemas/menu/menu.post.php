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

$schema['top']['administration']['items']['commerceml'] = [
    'attrs' => [
        'class' => 'is-addon'
    ],
    'position' => 1410,
    'href' => 'commerceml.currencies',
    'subitems' => [
        'commerceml_currencies' => [
            'href' => 'commerceml.currencies',
            'position' => 100
        ],
        'commerceml_prices' => [
            'href' => 'commerceml.offers',
            'position' => 200
        ],
        'commerceml_taxes' => [
            'href' => 'commerceml.taxes',
            'position' => 300
        ],
    ],
];

return $schema;
