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

use Tygh\Registry;

$path = Registry::get('config.dir.addons') . 'rus_hybrid_auth/lib/Hybrid/Providers/';

$schema['vkontakte'] = array(
    'provider' => 'Vkontakte',
    'keys' => array(
        'id' => array(
            'db_field' => 'app_id',
            'type' => 'input',
            'label' => 'id',
            'required' => true
        ),
        'secret' => array(
            'db_field' => 'app_secret_key',
            'type' => 'input',
            'label' => 'secret_key',
            'required' => true
        )
    ),
    'wrapper' => array(
        'path' => $path . 'Vkontakte.php',
        'class' => 'Hybrid_Providers_Vkontakte',
    ),
    'instruction' => 'rus_hybrid_auth.instruction_vkontakte'
);

$schema['mailru'] = array(
    'provider' => 'Mailru',
    'keys' => array(
        'id' => array(
            'db_field' => 'app_id',
            'type' => 'input',
            'label' => 'id',
            'required' => true
        ),
        'secret' => array(
            'db_field' => 'app_secret_key',
            'type' => 'input',
            'label' => 'secret_key',
            'required' => true
        )
    ),
    'wrapper' => array(
        'path' => $path . 'Mailru.php',
        'class' => 'Hybrid_Providers_Mailru',
    ),
    'instruction' => 'rus_hybrid_auth.instruction_mailru'
);

$schema['yandex'] = array(
    'provider' => 'Yandex',
    'keys' => array(
        'id' => array(
            'db_field' => 'app_id',
            'type' => 'input',
            'label' => 'id',
            'required' => true
        ),
        'secret' => array(
            'db_field' => 'app_secret_key',
            'type' => 'input',
            'label' => 'secret_key',
            'required' => true
        ),
    ),
    'params' => array(
        'yandex_callback' => array(
            'type' => 'template',
            'template' => 'addons/hybrid_auth/components/callback_url.tpl',
        )
    ),
    'wrapper' => array(
        'path' => $path . 'Yandex.php',
        'class' => '\Tygh\HybridProvidersYandex',
    ),
    'instruction' => 'rus_hybrid_auth.instruction_yandex'
);

$schema['odnoklassniki'] = array(
    'provider' => 'Odnoklassniki',
    'keys' => array(
        'id' => array(
            'db_field' => 'app_id',
            'type' => 'input',
            'label' => 'id',
            'required' => true
        ),
        'key' => array(
            'db_field' => 'app_public_key',
            'type' => 'input',
            'label' => 'public_key',
            'required' => true
        ),
        'secret' => array(
            'db_field' => 'app_secret_key',
            'type' => 'input',
            'label' => 'secret_key',
            'required' => true
        ),
    ),
    'params' => array(
        'odnoklassniki_callback' => array(
            'type' => 'template',
            'template' => 'addons/hybrid_auth/components/callback_url.tpl',
        )
    ),
    'wrapper' => array(
        'path' => $path . 'Odnoklassniki.php',
        'class' => '\Tygh\HybridProvidersOdnoklassniki',
    ),
    'instruction' => 'rus_hybrid_auth.instruction_odnoklassniki'
);

return $schema;
