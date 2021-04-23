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

use Tygh\Enum\Addons\VendorCommunication\CommunicationTypes;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$schema['controllers']['vendor_communication'] = array(
    'modes' => array(
        'delete_thread' => array(
            'permissions' => false,
        ),
        'm_delete_thread' => array(
            'permissions' => false,
        ),
        'create_thread' => array(
            'param_permissions' => array(
                'communication_type' => array(
                    CommunicationTypes::VENDOR_TO_ADMIN => true,
                    CommunicationTypes::VENDOR_TO_CUSTOMER => true,
                ),
            ),
            'default_permissions' => false,
        ),
        'threads' => array(
            'param_permissions' => array(
                'communication_type' => array(
                    CommunicationTypes::VENDOR_TO_ADMIN => true,
                    CommunicationTypes::VENDOR_TO_CUSTOMER => true,
                ),
            ),
            'default_permissions' => false,
        ),
        'post_message' => [
            'permissions' => true
        ],
        'view' => [
            'param_permissions' => [
                'communication_type' => [
                    CommunicationTypes::VENDOR_TO_ADMIN  => true,
                    CommunicationTypes::VENDOR_TO_CUSTOMER => true,
                ],
            ],
            'default_permissions' => false,
        ]
    ),
);

return $schema;
