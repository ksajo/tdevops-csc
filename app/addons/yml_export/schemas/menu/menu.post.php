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

$schema['top']['administration']['items']['yml_export'] = [
    'attrs' => [
        'class' => 'is-addon'
    ],
    'href' => 'yml.manage',
    'type' => 'title',
    'position' => 1550,
    'subitems' => [
        'yml_export.price_list' => [
            'href' => 'yml.manage',
            'position' => 10,
        ],
        'yml_export.offers_params' => [
            'href' => 'yml.offers_params',
            'position' => 20,
        ]
    ],
];

return $schema;
