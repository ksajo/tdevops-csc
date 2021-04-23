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

namespace Tygh\Addons\YandexDelivery;

use Tygh\Addons\InstallerInterface;
use Tygh\Addons\YandexDelivery\Services\YandexDeliveryService;
use Tygh\Core\ApplicationInterface;
use Tygh\Enum\ObjectStatuses;
use Tygh\Languages\Languages;

class Installer implements InstallerInterface
{
    /**
     * @var \Tygh\Core\ApplicationInterface
     */
    protected $app;

    /**
     * @inheritDoc
     */
    public function __construct(ApplicationInterface $app)
    {
        $this->app = $app;
    }

    /**
     * Returns new object of this class.
     *
     * @param ApplicationInterface $app Application interface.
     *
     * @return Installer
     */
    public static function factory(ApplicationInterface $app)
    {
        return new self($app);
    }

    /**
     * @inheritDoc
     */
    public function onBeforeInstall()
    {
    }

    /**
     * @inheritDoc
     */
    public function onInstall()
    {
        $shipping_service = [
            'status'  => ObjectStatuses::ACTIVE,
            'module'  => YandexDeliveryService::MODULE,
            'code'    => 'yandex',
            'sp_file' => '',
        ];
        $shipping_service['service_id'] = db_get_field(
            'SELECT service_id FROM ?:shipping_services WHERE module = ?s AND code = ?s',
            $shipping_service['module'],
            $shipping_service['code']
        );
        if (empty($shipping_service['service_id'])) {
            $shipping_service['service_id'] = db_query('INSERT INTO ?:shipping_services ?e', $shipping_service);
        }

        if (empty($shipping_service['service_id'])) {
            return;
        }
        $languages = Languages::getAll();
        foreach (array_keys($languages) as $lang_code) {
            if ($lang_code === 'ru') {
                $shipping_service['description'] = 'Яндекс.Доставка';
            } else {
                $shipping_service['description'] = 'Yandex.Delivery';
            }
            $shipping_service['lang_code'] = $lang_code;
            db_query('INSERT INTO ?:shipping_service_descriptions ?e', $shipping_service);
        }
    }

    /**
     * @inheritDoc
     */
    public function onUninstall()
    {
        $services_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', YandexDeliveryService::MODULE);
        if (empty($services_ids)) {
            return;
        }
        db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $services_ids);
        db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $services_ids);

        $shipping_ids = db_get_fields('SELECT shipping_id FROM ?:shippings WHERE service_id IN (?a)', $services_ids);
        if (empty($shipping_ids)) {
            return;
        }
        foreach ($shipping_ids as $shipping_id) {
            fn_delete_shipping($shipping_id);
        }
    }
}
