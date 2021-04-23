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

$schema['vendor_communication'] = array(
    'modes' => array(
        'delete_thread' => array(
            'permissions' => 'manage_vendor_communication',
        ),
        'm_delete_thread' => array(
            'permissions' => 'manage_vendor_communication',
        ),
        'create_thread' => array(
            'param_permissions' => array(
                'communication_type' => array(
                    CommunicationTypes::VENDOR_TO_ADMIN => 'manage_admin_communication',
                    CommunicationTypes::VENDOR_TO_CUSTOMER => 'manage_vendor_communication',
                ),
            ),
        ),
        'threads' => array(
            'param_permissions' => array(
                'communication_type' => array(
                    CommunicationTypes::VENDOR_TO_ADMIN => 'view_admin_communication',
                    CommunicationTypes::VENDOR_TO_CUSTOMER => 'view_vendor_communication',
                ),
            ),
        ),
        'post_message' => [
            'param_permissions' => [
                'communication_type' => [
                    CommunicationTypes::VENDOR_TO_ADMIN  => 'manage_admin_communication',
                    CommunicationTypes::VENDOR_TO_CUSTOMER => 'manage_vendor_communication',
                ],
            ],
        ],
        'view' => [
            'param_permissions' => [
                'communication_type' => [
                    CommunicationTypes::VENDOR_TO_ADMIN  => 'view_admin_communication',
                    CommunicationTypes::VENDOR_TO_CUSTOMER => 'view_vendor_communication',
                ],
            ],
        ]
    ),
);

return $schema;
