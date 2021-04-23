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

use Tygh\Addons\CommerceML\Dto\CategoryDto;
use Tygh\Addons\CommerceML\Dto\PriceTypeDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto;
use Tygh\Addons\CommerceML\Dto\TaxDto;
use Tygh\Addons\CommerceML\Dto\CurrencyDto;
use Tygh\Enum\UsergroupTypes;

defined('BOOTSTRAP') or die('Access denied');

/**
 * @var array<string, array{is_creatable: array<callable>|bool, items_provider: array<callable>}> $schema Declares mapping for entities sync
 */
$schema = [
    PriceTypeDto::REPRESENT_ENTITY_TYPE => [
        'is_creatable' => false,
        'items_provider' => static function () {
            $items = [
                PriceTypeDto::TYPE_BASE_PRICE => __('base_price'),
                PriceTypeDto::TYPE_LIST_PRICE => __('list_price'),
            ];

            $customers_usergroups = fn_get_usergroups([
                'type'            => UsergroupTypes::TYPE_CUSTOMER,
                'include_default' => true
            ]);

            foreach ($customers_usergroups as $usergroup) {
                if ($usergroup['usergroup_id'] === USERGROUP_ALL) {
                    continue;
                }

                $key = PriceTypeDto::createLocalIdByUsergroupId($usergroup['usergroup_id']);
                $items[$key] = $usergroup['usergroup'];
            }

            return $items;
        },
    ],
    TaxDto::REPRESENT_ENTITY_TYPE => [
        'is_creatable' => false,
        'items_provider' => static function () {
            return array_column(fn_get_taxes(), 'tax', 'tax_id');
        },
    ],
    CurrencyDto::REPRESENT_ENTITY_TYPE => [
        'is_creatable' => false,
        'items_provider' => static function () {
            return array_column(fn_get_currencies_list(), 'description', 'currency_code');
        },
    ],
    CategoryDto::REPRESENT_ENTITY_TYPE => [
        'is_creatable' => static function () {
            return fn_allowed_for('ULTIMATE') || empty(fn_get_runtime_company_id());
        }
    ],
    ProductFeatureDto::REPRESENT_ENTITY_TYPE => [
        'is_creatable' => static function () {
            return fn_allowed_for('ULTIMATE') || empty(fn_get_runtime_company_id());
        },
    ],
    ProductFeatureVariantDto::REPRESENT_ENTITY_TYPE => [
        'is_creatable' => static function () {
            return fn_allowed_for('ULTIMATE') || empty(fn_get_runtime_company_id());
        },
        'parent' => ProductFeatureDto::REPRESENT_ENTITY_TYPE,
    ]
];

return $schema;
