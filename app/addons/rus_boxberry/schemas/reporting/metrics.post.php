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

/** @var array $schema */

$schema['boxbery'] = function () {
    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'rus_boxberry');
    $shipping_ids = db_get_fields(
        'SELECT shipping_id FROM ?:shippings WHERE service_id IN (?n) AND status = ?s',
        $service_ids, 'A'
    );

    foreach ($shipping_ids as $shipping_id) {
        $params = fn_get_shipping_params($shipping_id);

        if (!empty($params['password'])) {
            return true;
        }
    }

    return false;
};

return $schema;
