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


use Tygh\Common\OperationResult;
use Tygh\Addons\Retailcrm\Service as RetailcrmService;
use Tygh\Enum\YesNo;
use Tygh\Registry;

/**
 * Validates retailCRM credentials.
 *
 * @param string $host      RetailCRM host.
 * @param string $api_key   RetailCRM API key.
 *
 * @return OperationResult
 */
function fn_retailcrm_validate_retailcrm_credentials($host, $api_key)
{
    $result = new OperationResult();

    if (empty($host)) {
        $result->addError('empty_host', __('retailcrm.error.host_is_required'));
    }

    if (empty($api_key)) {
        $result->addError('empty_api_key', __('retailcrm.error.api_key_is_required'));
    }

    if (!empty($host) && !empty($api_key)) {
        try {
            /** @var \RetailCrm\ApiClient $client */
            $client = call_user_func(Tygh::$app['addons.retailcrm.api_client_factory'], $host, $api_key);
            $response = $client->statusesList();

            if (!$response->isSuccessful()) {
                $result->addError('client_error', __('retailcrm.error.could_not_connect_by_http_code', array(
                    '[code]' => $response->getStatusCode()
                )));
            }
        } catch (Exception $exception) {
            $result->addError('client_error', __('retailcrm.error.could_not_connect', array(
                '[reason]' => $exception->getMessage()
            )));
        }
    }

    $result->setSuccess($result->getErrors() === array());

    return $result;
}

/**
 * Validates retailCRM site mapping settings.
 *
 * @param array $value List of site mapping. (company_id => retailcrm_shop_id)
 *
 * @return OperationResult
 */
function fn_retailcrm_validate_retailcrm_map_sites(array $value)
{
    $result = new OperationResult(true);

    if (count($value) !== count(array_unique($value))) {
        $result->setSuccess(false);
        $result->addError('map_sites', __('retailcrm.error.map_sites_invalid'));
    }

    return $result;
}

/**
 * Filters the retailCRM host.
 *
 * @param string $host RetailCRM host.
 *
 * @return string
 */
function fn_retailcrm_filter_retailcrm_host($host)
{
    return trim(preg_replace('/^.*:\/\//', '', $host), "\t\n\0\x0B/");
}

/**
 * Executes instructions after addon install.
 */
function fn_retailcrm_install()
{
    fn_set_storage_data(RetailcrmService::KEY_SYNC_ORDER_TIME, time());
}

/**
 * Executes instructions after addon uninstall.
 */
function fn_retailcrm_uninstall()
{
    fn_set_storage_data(RetailcrmService::KEY_SYNC_ORDER_TIME, null);
}

/**
 * Gets console command for sync orders.
 *
 * @return string
 */
function fn_retailcrm_get_console_cmd()
{
    $console_cmd = sprintf(
        '*/5 * * * * php %s/%s --dispatch=retailcrm.sync --switch_company_id=0',
        DIR_ROOT,
        Registry::get('config.admin_index')
    );

    /**
     * Executed after string of the console command was builded; Allows to modify string of the console command.
     *
     * @param array  $price_list    Data of price list
     * @param string $console_cmd   String of the console command
     */
    fn_set_hook('retailcrm_get_console_cmd', $console_cmd);

    return $console_cmd;
}

/**
 * Hook handler for creating order.
 *
 * @param int $order_id Order identifier.
 */
function fn_retailcrm_place_order($order_id)
{
    if (AREA !== 'C') {
        return;
    }

    $order = fn_get_order_info($order_id);

    if ($order['is_parent_order'] === 'Y') {
        return;
    }

    /** @var \Tygh\Addons\Retailcrm\Service $service */
    $service = Tygh::$app['addons.retailcrm.service'];

    $service->createRetailCrmOrder($order);
}

/**
 * Hook handler for changing order status.
 *
 * @param $status_to
 * @param $status_from
 * @param $order_info
 * @param $force_notification
 * @param $order_statuses
 * @param $place_order
 */
function fn_retailcrm_change_order_status($status_to, $status_from, $order_info, $force_notification, $order_statuses, $place_order)
{
    if (AREA !== 'C') {
        return;
    }

    /** @var \Tygh\Addons\Retailcrm\Service $service */
    $service = Tygh::$app['addons.retailcrm.service'];

    if ($status_from == STATUS_INCOMPLETED_ORDER && $status_to != STATUS_INCOMPLETED_ORDER && !$service->isRetailCrmOrderExists($order_info['order_id'], $order_info['company_id'])) {
        $order = fn_get_order_info($order_info['order_id']);
        $order['status'] = $status_to;

        if ($order['is_parent_order'] === 'Y') {
            return;
        }

        $service->createRetailCrmOrder($order);
    } else {
        $service->updateRetailCrmOrderStatus($order_info['order_id'], $status_to, $order_info['company_id']);
    }
}

/**
 * The "yml_export_get_options_post" hook handler.
 *
 * Actions performed:
 *  - Adds to $options (properties of price list) new option which forbid using external yml categories.
 *
 * @see fn_yml_get_options
 */
function fn_retailcrm_yml_export_get_options_post($price_list_id, &$options)
{
    $options['use_yml_categories'] = $options['used_for_retailcrm'] === YesNo::NO;
}