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

$schema = array (
    'C' => array(
        'name' => __('yml2_option_color'),
        'value' =>'Цвет'
    ),
    'S' => array(
        'name' => __('yml2_option_size'),
        'value' =>'Размер',
        'types' => array(
            'RU' => __('yml2_option_type_ru'),
            'INT' => __('yml2_option_type_int'),
            'Height' => __('yml2_option_type_height'),
            'Months' => __('yml2_option_type_months'),
            'Years' => __('yml2_option_type_years'),
            'INCH' => __('yml2_option_type_inch'),
            'Round' => __('yml2_option_type_round'),
        ),
        'customer_type' => true
    ),
    'H' => array(
        'name' => __('yml2_option_height'),
        'value' =>'Рост',
        'types' => array(
            'RU' => __('yml2_option_type_ru'),
            'INT' => __('yml2_option_type_int')
        ),
        'customer_type' => true
    ),
    'E' => array(
        'name' => __('yml2_option_chest'),
        'value' =>'Обхват груди',
        'types' => array(
            'RU' => __('yml2_option_type_ru'),
            'INT' => __('yml2_option_type_int')
        ),
        'customer_type' => true
    ),
    'N' => array(
        'name' => __('yml2_option_neck_circle'),
        'value' =>'Обхват шеи',
        'types' => array(
            'RU' => __('yml2_option_type_ru'),
            'INT' => __('yml2_option_type_int')
        ),
        'customer_type' => true
    ),
    'U' => array(
        'name' => __('yml2_option_underbust_circumference'),
        'value' =>'Обхват под грудью',
        'types' => array(
            'RU' => __('yml2_option_type_ru'),
            'INT' => __('yml2_option_type_int')
        ),
        'customer_type' => true
    ),
    'W' => array(
        'name' => __('yml2_option_waist_line'),
        'value' =>'Обхват талии',
        'types' => array(
            'RU' => __('yml2_option_type_ru'),
            'INT' => __('yml2_option_type_int')
        ),
        'customer_type' => true
    ),
    'P' => array(
        'name' => __('yml2_option_cup'),
        'value' =>'Чашка',
        'types' => array(
            'RU' => __('yml2_option_type_ru'),
            'INT' => __('yml2_option_type_int')
        ),
        'customer_type' => true
    ),
    'A' => array(
        'name' => __('yml2_option_pants_size'),
        'value' =>'Размер трусов',
        'types' => array(
            'RU' => __('yml2_option_type_ru'),
            'INT' => __('yml2_option_type_int')
        ),
        'customer_type' => true
    )
);

return $schema;