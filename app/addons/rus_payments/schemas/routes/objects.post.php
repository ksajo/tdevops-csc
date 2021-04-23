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

/**
 * @var array<string, array> $schema
 */
$schema['/payment_notification/result/robokassa'] = [
    'dispatch' => 'payment_notification.result',
    'payment'  => 'robokassa',
];

$schema['/payment_notification/success/robokassa'] = [
    'dispatch' => 'payment_notification.return',
    'payment'  => 'robokassa',
];

$schema['/payment_notification/fail/robokassa'] = [
    'dispatch' => 'payment_notification.cancel',
    'payment'  => 'robokassa',
];

$schema['/payment_notification/process/yoomoney_p2p'] = [
    'dispatch' => 'payment_notification.process',
    'payment'  => 'yandex_p2p',
];

return $schema;
