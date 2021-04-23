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

use Tygh\Addons\CommerceML\Dto\LocalIdDto;
use Tygh\Addons\CommerceML\ServiceProvider;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\YesNo;
use Tygh\Registry;
use Tygh\Tygh;
use Tygh\Addons\CommerceML\Formators\OrderFormator;

defined('BOOTSTRAP') or die('Access denied');

/**
 * @var array<string, array> $schema
 */
$schema = [
    'catalog_importer.new_product_status' => [
        'type' => 'enum',
        'variants' => [
            ObjectStatuses::ACTIVE,
            ObjectStatuses::DISABLED,
            ObjectStatuses::HIDDEN,
        ],
    ],
    'catalog_importer.allow_import_features' => [
        'type' => 'bool',
        'default' => static function ($company_id) {
            return ServiceProvider::isProductFeaturesManageAllowed($company_id);
        },
        'editable' => static function ($company_id) {
            return ServiceProvider::isProductFeaturesManageAllowed($company_id);
        },
        'feedback' => true,
    ],
    'catalog_importer.allow_import_categories' => [
        'type' => 'bool',
        'default' => static function ($company_id) {
            return !($company_id && fn_allowed_for('MULTIVENDOR'));
        },
        'editable' => static function ($company_id) {
            return ServiceProvider::isCategoriesManageAllowed($company_id);
        },
        'feedback' => true,
    ],
    'catalog_importer.default_category_id' => [
        'type' => 'int',
        'default' => static function ($company_id) {
            if (fn_allowed_for('MULTIVENDOR')) {
                $company_id = 0;
            }
            return fn_get_or_create_default_category_id($company_id);
        },
        'feedback' => true,
    ],
    'catalog_importer.product_category_update_strategy' => [
        'type' => 'enum',
        'variants' => [
            'append',
            'replace_main',
            'ignore'
        ],
        'default' => 'append',
        'feedback' => true,
    ],
    'catalog_importer.product_image_update_strategy' => [
        'type' => 'enum',
        'variants' => [
            'append',
            'replace',
            'ignore'
        ],
        'default' => 'append',
        'feedback' => true,
    ],
    'catalog_importer.allow_matching_category_by_name' => [
        'type' => 'bool',
        'default' => false,
        'editable' => static function ($company_id) {
            return ServiceProvider::isCategoriesManageAllowed($company_id);
        },
    ],
    'catalog_importer.allow_matching_product_by_product_code' => [
        'type' => 'bool',
        'default' => false,
    ],
    'catalog_importer.import_mode' => [
        'type' => 'enum',
        'variants' => [
            'all',
            'only_new',
            'only_existing',
            'none'
        ],
        'default' => 'all',
    ],
    'catalog_importer.allow_import_offers' => [
        'type' => 'bool',
        'default' => true,
    ],
    'catalog_importer.hide_out_of_stock_products' => [
        'type' => 'bool',
        'default' => !YesNo::toBool(Registry::get('settings.General.show_out_of_stock_products')),
    ],
    'catalog_importer.allow_update_product_name' => [
        'type' => 'bool',
        'default' => true,
        'feedback' => true,
    ],
    'catalog_importer.allow_update_product_code' => [
        'type' => 'bool',
        'default' => true,
        'feedback' => true,
    ],
    'catalog_importer.allow_update_product_full_description' => [
        'type' => 'bool',
        'default' => true,
        'feedback' => true,
    ],
    'catalog_importer.allow_update_product_short_description' => [
        'type' => 'bool',
        'default' => true,
        'feedback' => true,
    ],
    'catalog_importer.allow_update_product_page_title' => [
        'type' => 'bool',
        'default' => true,
        'feedback' => true,
    ],
    'catalog_importer.allow_update_product_promotext' => [
        'type' => 'bool',
        'default' => true,
        'feedback' => true,
    ],
    'catalog_convertor.product_name_source' => [
        'type' => 'enum',
        'variants' => [
            'name',
            'full_name'
        ],
    ],
    'catalog_convertor.product_code_source' => [
        'type' => 'enum',
        'variants' => [
            'article',
            'code',
            'bar'
        ],
    ],
    'catalog_convertor.full_description_source' => [
        'type' => 'enum',
        'variants' => [
            'none',
            'description',
            'html_description',
            'full_name',
        ],
    ],
    'catalog_convertor.short_description_source' => [
        'type' => 'enum',
        'variants' => [
            'none',
            'description',
            'html_description',
            'full_name',
        ],
    ],
    'catalog_convertor.page_title_source' => [
        'type' => 'enum',
        'variants' => [
            'none',
            'name',
            'full_name',
        ],
    ],
    'catalog_convertor.promo_text_property_source' => [
        'type' => 'string',
        'default' => '',
    ],
    'catalog_convertor.weight_property_source_list' => [
        'type' => 'string[]',
        'default' => [],
        'feedback' => true,
    ],
    'catalog_convertor.weight_as_feature_value' => [
        'type' => 'bool',
        'default' => true,
    ],
    'catalog_convertor.free_shipping_property_source_list' => [
        'type' => 'string[]',
        'default' => [],
        'feedback' => true,
    ],
    'catalog_convertor.free_shipping_as_feature_value' => [
        'type' => 'bool',
        'default' => true,
    ],
    'catalog_convertor.shipping_cost_property_source_list' => [
        'type' => 'string[]',
        'default' => [],
        'feedback' => true,
    ],
    'catalog_convertor.shipping_cost_as_feature_value' => [
        'type' => 'bool',
        'default' => true,
    ],
    'catalog_convertor.number_of_items_property_source_list' => [
        'type' => 'string[]',
        'default' => [],
        'feedback' => true,
    ],
    'catalog_convertor.number_of_items_as_feature_value' => [
        'type' => 'bool',
        'default' => true,
    ],
    'catalog_convertor.box_length_property_source_list' => [
        'type' => 'string[]',
        'default' => [],
        'feedback' => true,
    ],
    'catalog_convertor.box_length_as_feature_value' => [
        'type' => 'bool',
        'default' => true,
    ],
    'catalog_convertor.box_width_property_source_list' => [
        'type' => 'string[]',
        'default' => [],
        'feedback' => true,
    ],
    'catalog_convertor.box_width_as_feature_value' => [
        'type' => 'bool',
        'default' => true,
    ],
    'catalog_convertor.box_height_property_source_list' => [
        'type' => 'string[]',
        'default' => [],
        'feedback' => true,
    ],
    'catalog_convertor.box_height_as_feature_value' => [
        'type' => 'bool',
        'default' => true,
    ],
    'catalog_convertor.property_allowlist' => [
        'type' => 'string[]',
        'default' => [],
        'editable' => false,
    ],
    'catalog_convertor.property_blocklist' => [
        'type' => 'string[]',
        'default' => [],
        'editable' => false,
    ],
    'mapping.category.default_variant' => [
        'type' => 'enum',
        'variants' => static function ($company_id) {
            $variants = [];

            if (ServiceProvider::isCategoriesManageAllowed($company_id)) {
                $variants[] = LocalIdDto::VALUE_CREATE;
            }

            $variants[] = LocalIdDto::VALUE_USE_DEFAULT;

            return $variants;
        },
        'variants_labels' => static function () {
            return [
                LocalIdDto::VALUE_CREATE => __('commerceml.map.category.create'),
                LocalIdDto::VALUE_USE_DEFAULT => __('commerceml.map.category.use_default'),
            ];
        },
        'value' => static function ($company_id, array $settings) {
            if ($settings['catalog_importer.allow_import_categories'] && ServiceProvider::isCategoriesManageAllowed($company_id)) {
                return LocalIdDto::VALUE_CREATE;
            }

            return LocalIdDto::VALUE_USE_DEFAULT;
        },
        'editable' => false,
    ],
    'mapping.feature.default_variant' => [
        'type' => 'enum',
        'variants' => static function ($company_id) {
            $variants = [];

            if (ServiceProvider::isProductFeaturesManageAllowed($company_id)) {
                $variants[] = LocalIdDto::VALUE_CREATE;
            }

            $variants[] = LocalIdDto::VALUE_SKIP;

            return $variants;
        },
        'variants_labels' => static function () {
            return [
                LocalIdDto::VALUE_CREATE => __('commerceml.map.product_feature.create'),
                LocalIdDto::VALUE_SKIP   => __('commerceml.map.product_feature.skip'),
            ];
        },
        'value' => static function ($company_id, array $settings) {
            if ($settings['catalog_importer.allow_import_features'] && ServiceProvider::isProductFeaturesManageAllowed($company_id)) {
                return LocalIdDto::VALUE_CREATE;
            }

            return LocalIdDto::VALUE_SKIP;
        },
        'editable' => false,
    ],
    'mapping.feature_variant.default_variant' => [
        'type' => 'enum',
        'variants' => static function ($company_id) {
            $variants = [];

            if (ServiceProvider::isProductFeaturesManageAllowed($company_id)) {
                $variants[] = LocalIdDto::VALUE_CREATE;
            }

            $variants[] = LocalIdDto::VALUE_SKIP;

            return $variants;
        },
        'variants_labels' => static function () {
            return [
                LocalIdDto::VALUE_CREATE => __('commerceml.map.product_feature_variant.create'),
                LocalIdDto::VALUE_SKIP   => __('commerceml.map.product_feature_variant.skip'),
            ];
        },
        'value' => static function ($company_id, array $settings) {
            if ($settings['catalog_importer.allow_import_features'] && ServiceProvider::isProductFeaturesManageAllowed($company_id)) {
                return LocalIdDto::VALUE_CREATE;
            }

            return LocalIdDto::VALUE_SKIP;
        },
        'editable' => false,
    ],
    'default_lang' => [
        'type' => 'string',
        'variants' => static function () {
            return array_keys(Tygh::$app['languages']);
        },
        'variants_labels' => static function () {
            return array_column(Tygh::$app['languages'], 'name', 'lang_code');
        },
        'default' => Registry::get('settings.Appearance.backend_default_language'),
    ],
    'orders_exporter.strategy' => [
        'type' => 'enum',
        'variants' => [
            OrderFormator::STRATEGY_ALL,
            OrderFormator::STRATEGY_NEW,
        ],
        'default' => OrderFormator::STRATEGY_ALL,
    ],
    'orders_exporter.statuses_filter' => [
        'type' => 'string[]',
        'variants' => static function () {
            return array_keys(fn_get_simple_statuses());
        },
        'variants_labels' => static function () {
            return fn_get_simple_statuses();
        },
        'default' => 'all'
    ],
    'orders_exporter.export_from_order_id' => [
        'type' => 'int'
    ],
    'orders_exporter.export_order_statuses' => [
        'type'    => 'bool',
        'default' => true
    ],
    'orders_exporter.export_product_options' => [
        'type'    => 'bool',
        'default' => true
    ],
    'orders_exporter.export_shipping_fee' => [
        'type'    => 'bool',
        'default' => true
    ],
    'orders_importer.import_changes' => [
        'type'    => 'bool',
        'default' => true
    ]
];

return $schema;
