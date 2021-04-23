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


$schema = array(
    'default' => array(
        'general' => array(
            'urls' => array(
                'type' => 'template',
                'template' => 'addons/yml_export/views/yml/components/urls.tpl',
                'update_only' => true
            ),
            'enable_authorization' => array(
                'type' => 'checkbox',
                'default' => 'Y',
            ),
            'access_key' => array(
                'type' => 'template',
                'template' => 'addons/yml_export/views/yml/components/access_key.tpl'
            ),
            'name_price_list' => array(
                'type' => 'input',
                'default' => 'Яндекс.Маркет',
                'required' => true,
            ),
            'shop_name' => array(
                'type' => 'input',
                'value' => '',
                'default' => '',
                'required' => true,
            ),
            'export_encoding' => array(
                'type' => 'selectbox',
                'variants' => array(
                    'utf-8' => 'yml_export.utf8',
                    'windows-1251' => 'yml_export.windows1251'
                ),
                'default' => 'utf-8',
            ),
            'enable_cpa' => array(
                'type' => 'selectbox',
                'variants' => array(
                    'Y' => 'yml2_true',
                    'N' => 'yml2_false'
                ),
                'default' => 'yes',
                'tooltip' => __("yml_export.tooltip_enable_cpa"),
            ),
            'detailed_generation' => array(
                'type' => 'checkbox',
                'default' => 'Y',
            )
        ),
        'export_data' => array(
            'utm_link' => array(
                'type' => 'input',
                'default' => '',
                'class' => 'input-large',
                'placeholder' => 'utm_source=yandex_market&utm_medium=cpc&utm_content={product_code}'
            ),
            'export_stock' => array(
                'type' => 'checkbox',
                'default' => 'N',
            ),
            'export_null_price' => array(
                'type' => 'checkbox',
                'default' => 'N',
            ),
            'export_shared_products' => array(
                'type' => 'checkbox',
                'default' => 'N',
                'disabled' => ''
            ),
            'minimal_discount' => array(
                'type' => 'input',
                'default' => '5',
            ),
            'export_min_product_price' => array(
                'type' => 'input',
                'default' => '',
            ),
            'export_max_product_price' => array(
                'type' => 'input',
                'default' => '',
            ),
            'export_default_fee' => array(
                'type' => 'input',
                'default' => YML_MIN_FEE,
                'min' => YML_MIN_FEE,
                'tooltip' => __("ttc_yml_export.fee"),
            ),
        ),
        'export_fields' => array(
            'weight' => array(
                'type' => 'checkbox',
                'default' => 'N',
            ),
            'dimensions' => array(
                'type' => 'checkbox',
                'default' => 'N',
            ),
            'not_downloadable' => array(
                'type' => 'checkbox',
                'default' => 'N',
            ),
        ),
        'images' => array(
            'image_type' => array(
                'type' => 'selectbox',
                'variants' => array(
                    'thumbnail' => 'yml_export.thumbnail',
                    'detailed' => 'yml_export.detailed'
                ),
                'default' => 'detailed',
            ),
            'thumbnail_width' => array(
                'type' => 'input',
                'default' => '280',
                'min' => '250'
            ),
            'thumbnail_height' => array(
                'type' => 'input',
                'default' => '280',
                'min' => '250'
            ),
            'check_watermarks_addon' => array(
                'type' => 'template',
                'template' => 'addons/yml_export/views/yml/components/check_watermarks_addon.tpl'
            ),
        ),
        'delivery_options' => array(
            'store' => array(
                'type' => 'selectbox',
                'variants' => array(
                    'Y' => 'yes',
                    'N' => 'no',
                    '' => 'none'
                ),
                'default' => 'N',
            ),
            'pickup' => array(
                'type' => 'selectbox',
                'variants' => array(
                    'Y' => 'yes',
                    'N' => 'no',
                    '' => 'none'
                ),
                'default' => 'N',
            ),
            'delivery' => array(
                'type' => 'selectbox',
                'variants' => array(
                    'Y' => 'yes',
                    'N' => 'no',
                    '' => 'none'
                ),
                'default' => 'Y',
            ),
            'options' => array(
                'type' => 'template',
                'template' => 'addons/yml_export/common/yml_delivery_options.tpl',
                'name_data' => 'delivery_options',
            ),
        ),
        'categories' => array(
            'export_hidden_categories' => array(
                'type' => 'checkbox',
                'default' => 'N',
            ),
            'exclude_categories_not_logging' => array(
                'type' => 'checkbox',
                'default' => 'Y',
            ),
            'categories' => array(
                'type' => 'template',
                'template' => 'addons/yml_export/views/yml/components/categories.tpl',
                'name_data' => 'company_id',
            ),
        )
    )
);

return $schema;