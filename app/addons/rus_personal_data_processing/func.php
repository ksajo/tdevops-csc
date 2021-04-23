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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Registry;
use Tygh\Languages\Languages;

function fn_rus_personal_data_processing_information()
{
    $url_service = 'http://pd.rsoc.ru/operators-registry/notification/form/';
    $url_language = fn_url('languages.translations?q=addons.rus_personal_data_processing');

    $registry_description = __('addons.rus_personal_data_processing.updated_personal_data_registry_description', array('[url_service]' => $url_service, '[link]' => $url_language));

    return $registry_description;
}