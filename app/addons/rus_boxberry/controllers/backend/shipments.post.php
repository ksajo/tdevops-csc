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
use Tygh\Shippings\BoxberryClient;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'details') {

    $shipment_data = Tygh::$app['view']->getTemplateVars('shipment');

    if ($shipment_data['carrier'] == 'rus_boxberry' && !empty($shipment_data['tracking_number'])) {
        $service_params = fn_get_shipping_params($shipment_data['shipping_id']);
        $boxberry = new BoxberryClient($service_params);

        $status = $boxberry->getStatus($shipment_data['tracking_number']);
        Tygh::$app['view']->assign('boxberry_status', $status);
    }
}
