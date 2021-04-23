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
    'yandex_money' => array(
        'processor' => 'Яндекс.Касса [Устарел]',
        'processor_script' => 'yandex_money.php',
        'processor_template' => 'addons/rus_payments/views/orders/components/payments/yandex_money.tpl',
        'admin_template' => 'yandex_money.tpl',
        'callback' => 'Y',
        'type' => 'P',
        'position' => 10,
        'addon' => 'rus_payments',
    ),
    'yandex_p2p' => array(
        'processor' => 'ЮMoney: Перевод на счёт',
        'processor_script' => 'yandex_p2p.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'yandex_p2p.tpl',
        'callback' => 'Y',
        'type' => 'P',
        'position' => 20,
        'addon' => 'rus_payments',
    ),
    'sbrf' => array(
        'processor' => 'Сбербанк: QR-код',
        'processor_script' => 'sbrf.php',
        'processor_template' => '',
        'admin_template' => 'sbrf_receipt.tpl',
        'callback' => 'N',
        'type' => 'P',
        'position' => 30,
        'addon' => 'rus_payments',
    ),
    'webmoney' => array(
        'processor' => 'WebMoney',
        'processor_script' => 'webmoney.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'webmoney.tpl',
        'callback' => 'N',
        'type' => 'P',
        'position' => 35,
        'addon' => 'rus_payments',
    ),
    'robokassa' => array(
        'processor' => 'Robokassa',
        'processor_script' => 'robokassa.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'robokassa.tpl',
        'callback' => 'N',
        'type' => 'P',
        'position' => 40,
        'addon' => 'rus_payments',
    ),
    'assist' => array(
        'processor' => 'Assist',
        'processor_script' => 'assist.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'assist.tpl',
        'callback' => 'N',
        'type' => 'P',
        'position' => 45,
        'addon' => 'rus_payments',
    ),
    'rbk' => array(
        'processor' => 'RBK.money',
        'processor_script' => 'rbk.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'rbk.tpl',
        'callback' => 'Y',
        'type' => 'P',
        'position' => 60,
        'addon' => 'rus_payments',
    ),
    'vsevcredit' => array(
        'processor' => 'Вкредит24.ру',
        'processor_script' => 'vsevcredit.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'vsevcredit.tpl',
        'callback' => 'Y',
        'type' => 'P',
        'position' => 65,
        'addon' => 'rus_payments',
    ),
    'paymaster' => array(
        'processor' => 'PayMaster',
        'processor_script' => 'paymaster.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'paymaster.tpl',
        'callback' => 'Y',
        'type' => 'P',
        'position' => 70,
        'addon' => 'rus_payments',
    ),
    'avangard' => array(
        'processor' => 'Avangard',
        'processor_script' => 'avangard.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'avangard.tpl',
        'callback' => 'Y',
        'type' => 'P',
        'position' => 75,
        'addon' => 'rus_payments',
    ),
    'payanyway' => array(
        'processor' => 'PayAnyWay',
        'processor_script' => 'payanyway.php',
        'processor_template' => 'addons/rus_payments/views/orders/components/payments/payanyway.tpl',
        'admin_template' => 'payanyway.tpl',
        'callback' => 'Y',
        'type' => 'P',
        'position' => 80,
        'addon' => 'rus_payments',
    ),
    'payler' => array(
        'processor' => 'Payler',
        'processor_script' => 'payler.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'payler.tpl',
        'callback' => 'N',
        'type' => 'P',
        'position' => 85,
        'addon' => 'rus_payments',
    ),
    'account' => array(
        'processor' => 'Выставить счет',
        'processor_script' => 'account.php',
        'processor_template' => 'addons/rus_payments/views/orders/components/payments/account_payment.tpl',
        'admin_template' => 'account_payment.tpl',
        'callback' => 'N',
        'type' => 'P',
        'position' => 90,
        'addon' => 'rus_payments',
    ),
);

return $schema;
