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

$sending_services = array(
    1 => array(
        'label' => __('shipping.russianpost.simple_delivery_notice'),
        'name' => 'delivery_notice',
        'exclude_ids' => array(2),
    ),
    2 => array(
        'label' => __('shipping.russianpost.registry_delivery_notice'),
        'name' => 'registered_notice',
        'exclude_ids' => array(1),
    ),
    4 => array(
        'label' => __('shipping.russianpost.russian_post_shipping_careful'),
        'name' => 'careful',
    ),
    6 => array(
        'label' => __('shipping.russianpost.cumbersome_parcel'),
        'name' => 'ponderous_parcel',
    ),
    7 => array(
        'label' => __('shipping.russianpost.delivery_by_hand'),
        'name' => 'delivery_by_hand',
    ),
    8 => array(
        'label' => __('shipping.russianpost.personally'),
        'name' => 'personally',
    ),
    9 => array(
        'label' => __('shipping.russianpost.delivery_document'),
        'name' => 'delivery_document',
    ),
    10 => array(
        'label' => __('shipping.russianpost.delivery_product'),
        'name' => 'delivery_product',
    ),
    12 => array(
        'label' => __('shipping.russianpost.oversize'),
        'name' => 'oversize',
    ),
    14 => array(
        'label' => __('shipping.russianpost.russian_post_shipping_insurance'),
        'name' => 'insurance',
    ),
    22 => array(
        'label' => __('shipping.russianpost.check_investment'),
        'name' => 'check_investment',
        'exclude_ids' => array(23),
    ),
    23 => array(
        'label' => __('shipping.russianpost.compliance_investment'),
        'name' => 'compliance_investment',
        'exclude_ids' => array(22),
    ),
    24 => array(
        'label' => __('shipping.russianpost.cash_sender'),
        'name' => 'cash_sender',
    ),
    25 => array(
        'label' => __('shipping.russianpost.customs_fee'),
        'name' => 'customs_fee',
    ),
    26 => array(
        'label' => __('shipping.russianpost.delivery_courier'),
        'name' => 'delivery_courier',
    ),
    27 => array(
        'label' => __('shipping.russianpost.package_pochta'),
        'name' => 'package_pochta',
    ),
    28 => array(
        'label' => __('shipping.russianpost.corporate_client'),
        'name' => 'corporate_client',
    ),
    29 => array(
        'label' => __('shipping.russianpost.home_delivery'),
        'name' => 'home_delivery',
    ),
    30 => array(
        'label' => __('shipping.russianpost.postage_delivery_notice'),
        'name' => 'postage_delivery_notice',
    ),
    31 => array(
        'label' => __('shipping.russianpost.trusty_packet'),
        'name' => 'trusty_packet',
    ),
    32 => array(
        'label' => __('shipping.russianpost.safety_insurance'),
        'name' => 'safety_insurance',
    ),
    33 => array(
        'label' => __('shipping.russianpost.delivery_report'),
        'name' => 'delivery_report',
    ),
    34 => array(
        'label' => __('shipping.russianpost.add_barcode'),
        'name' => 'add_barcode',
    ),
    35 => array(
        'label' => __('shipping.russianpost.packaging_items'),
        'name' => 'packaging_items',
    ),
    36 => array(
        'label' => __('shipping.russianpost.add_sticker'),
        'name' => 'add_sticker',
    ),
    37 => array(
        'label' => __('shipping.russianpost.shipping_delivery'),
        'name' => 'shipping_delivery',
    ),
    38 => array(
        'label' => __('shipping.russianpost.check_suite'),
        'name' => 'check_suite',
    ),
    39 => array(
        'label' => __('shipping.russianpost.return_statement'),
        'name' => 'return_statement',
    ),
    40 => array(
        'label' => __('shipping.russianpost.delivery_far_place'),
        'name' => 'delivery_far_place',
    ),
    41 => array(
        'label' => __('shipping.russianpost.sms_unit_send'),
        'name' => 'sms_unit_send',
        'exclude_ids' => array(43, 44),
    ),
    42 => array(
        'label' => __('shipping.russianpost.sms_unit_recipient'),
        'name' => 'sms_unit_recipient',
        'exclude_ids' => array(43, 44),
    ),
    43 => array(
        'label' => __('shipping.russianpost.sms_part_send'),
        'name' => 'sms_part_send',
        'exclude_ids' => array(41, 42),
    ),
    44 => array(
        'label' => __('shipping.russianpost.sms_part_recipient'),
        'name' => 'sms_part_recipient',
        'exclude_ids' => array(41, 42),
    ),
    45 => array(
        'label' => __('shipping.russianpost.agreement_prolongation'),
        'name' => 'agreement_prolongation',
    ),
    57 => array(
        'label' => __('shipping.russianpost.crossoperator'),
        'name' => 'crossoperator',
    ),
);

return $sending_services;
