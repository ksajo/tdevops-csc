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

namespace Tygh\Addons\YandexCheckout\HookHandlers;

class CompaniesHookHandler
{
    /**
     * The "get_company_data" hook handler.
     *
     * Actions performed:
     *     - Adds Yandex shopId into a list of fetched fields.
     *
     * @see \fn_get_company_data()
     */
    public function onGetCompanyData($company_id, $lang_code, $extra, &$fields, $join, $condition)
    {
        $fields[] = db_quote('yandex_checkout_shopid');
    }
}