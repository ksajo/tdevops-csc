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

namespace Tygh\Addons\Retailcrm;

use Tygh\Settings as StorageSettings;

/**
 * The class provides methods for access the add-on settings.
 *
 * @package Tygh\Addons\Retailcrm
 */
class Settings
{
    /** @var StorageSettings */
    protected $settings_storage;

    /** @var array Order status to RetailCRM order status */
    protected $map_order_statuses = array();

    /** @var array Payment identifier to RetailCRM payment code */
    protected $map_payment_types = array();

    /** @var array Shipping identifier to RetailCRM shipping code */
    protected $map_shipping_types = array();

    /** @var array Storefront identifier to RetailCRM site code */
    protected $map_sites = array();

    /** @var string RetailCRM order creating method */
    protected $order_method;

    /** @var string RetailCRM order type */
    protected $order_type;

    /**
     * Settings constructor.
     *
     * @param StorageSettings $settings Settings storage instance.
     */
    public function __construct(StorageSettings $settings)
    {
        $this->settings_storage = $settings;

        $this->map_order_statuses = $this->getSettingValue('retailcrm_map_order_statuses', true);
        $this->map_payment_types = $this->getSettingValue('retailcrm_map_payment_types', true);
        $this->map_shipping_types = $this->getSettingValue('retailcrm_map_shipping_types', true);
        $this->map_sites = $this->getSettingValue('retailcrm_map_sites', true);
        $this->order_method = $this->getSettingValue('retailcrm_order_method');
        $this->order_type = $this->getSettingValue('retailcrm_order_type');
    }

    /**
     * Gets RetailCRM order creating method.
     *
     * @return string
     */
    public function getOrderMethod()
    {
        return $this->order_method;
    }

    /**
     * Gets RetailCRM order type.
     *
     * @return string
     */
    public function getOrderType()
    {
        return $this->order_type;
    }

    /**
     * Sets RetailCRM order creating method.
     *
     * @param string $value
     */
    public function setOrderMethod($value)
    {
        $this->order_method = $value;
        $this->setSettingValue('retailcrm_order_method', $value);
    }

    /**
     * Sets RetailCRM order type.
     *
     * @param string $value
     */
    public function setOrderType($value)
    {
        $this->order_type = $value;
        $this->setSettingValue('retailcrm_order_type', $value);
    }

    /**
     * Gets mapping of order status to RetailCRM order status.
     *
     * @return array
     */
    public function getMapOrderStatuses()
    {
        return $this->map_order_statuses;
    }

    /**
     * Sets mapping of order status to RetailCRM order status.
     *
     * @param array $data
     */
    public function setMapOrderStatuses(array $data)
    {
        $data = array_filter($data);

        $this->map_order_statuses = $data;
        $this->setSettingValue('retailcrm_map_order_statuses', $data, true);
    }

    /**
     * Gets RetailCRM order status by store order status.
     *
     * @param string $internal_value Order status
     *
     * @return bool|string
     */
    public function getExternalOrderStatus($internal_value)
    {
        return $this->findByIndex($this->map_order_statuses, $internal_value);
    }

    /**
     * Gets order status by RetailCRM order status.
     *
     * @param string $external_value RetailCRM order status.
     *
     * @return bool|string
     */
    public function getInternalOrderStatus($external_value)
    {
        return $this->findByValue($this->map_order_statuses, $external_value);
    }

    /**
     * Gets mapping of storefront identifier to RetailCRM site code.
     *
     * @return array
     */
    public function getMapSites()
    {
        return $this->map_sites;
    }

    /**
     * Sets mapping of storefront identifier to RetailCRM site code.
     *
     * @param array $data
     */
    public function setMapSites(array $data)
    {
        $data = array_filter(array_unique($data));

        $this->map_sites = $data;
        $this->setSettingValue('retailcrm_map_sites', $data, true);
    }

    /**
     * Gets RetailCRM site code by storefront identifier.
     *
     * @param int $internal_value Storefront identifier.
     *
     * @return bool|string
     */
    public function getExternalSite($internal_value)
    {
        return $this->findByIndex($this->map_sites, $internal_value);
    }

    /**
     * Gets storefront identifier by RetailCRM site code.
     *
     * @param string $external_value RetailCRM site code.
     *
     * @return bool|int
     */
    public function getInternalSite($external_value)
    {
        return $this->findByValue($this->map_sites, $external_value);
    }

    /**
     * Gets mapping of shipping identifier to RetailCRM shipping code.
     *
     * @return array
     */
    public function getMapShippingTypes()
    {
        return $this->map_shipping_types;
    }

    /**
     * Sets mapping of shipping identifier to RetailCRM shipping code.
     *
     * @param array $data
     */
    public function setMapShippingTypes(array $data)
    {
        $data = array_filter($data);

        $this->map_shipping_types = $data;
        $this->setSettingValue('retailcrm_map_shipping_types', $data, true);
    }

    /**
     * Gets RetailCRM shipping code by shipping identifier.
     *
     * @param int $internal_value Shipping identifier.
     *
     * @return bool|string
     */
    public function getExternalShippingType($internal_value)
    {
        return $this->findByIndex($this->map_shipping_types, $internal_value);
    }

    /**
     * Gets shipping identifier by RetailCRM shipping code.
     *
     * @param string $external_value RetailCRM shipping code.
     *
     * @return bool|int
     */
    public function getInternalShippingType($external_value)
    {
        return $this->findByValue($this->map_shipping_types, $external_value);
    }

    /**
     * Gets mapping of payment identifier to RetailCRM payment code.
     *
     * @return array
     */
    public function getMapPaymentTypes()
    {
        return $this->map_payment_types;
    }

    /**
     * Sets mapping of payment identifier to RetailCRM payment code.
     *
     * @param array $data
     */
    public function setMapPaymentTypes(array $data)
    {
        $data = array_filter($data);

        $this->map_payment_types = $data;
        $this->setSettingValue('retailcrm_map_payment_types', $data, true);
    }

    /**
     * Gets RetailCRM payment code by payment identifier.
     *
     * @param int $internal_value Payment identifier.
     *
     * @return bool|string
     */
    public function getExternalPaymentType($internal_value)
    {
        return $this->findByIndex($this->map_payment_types, $internal_value);
    }

    /**
     * Gets payment identifier by RetailCRM payment code.
     *
     * @param string $external_value RetailCRM payment code.
     *
     * @return bool|int
     */
    public function getInternalPaymentType($external_value)
    {
        return $this->findByValue($this->map_payment_types, $external_value);
    }

    /**
     * Finds array index by value.
     *
     * @param array $map
     * @param mixed $value
     *
     * @return bool|mixed
     */
    protected function findByValue($map, $value)
    {
        return array_search($value, $map);
    }

    /**
     * Finds value by index from array.
     *
     * @param array         $map
     * @param string|int    $index
     *
     * @return bool|mixed
     */
    protected function findByIndex($map, $index)
    {
        return isset($map[$index]) ? $map[$index] : false;
    }

    /**
     * Sets setting value.
     *
     * @param string        $setting_name
     * @param array|string  $value
     * @param bool          $is_serialized
     */
    protected function setSettingValue($setting_name, $value, $is_serialized = false)
    {
        if ($is_serialized) {
            $value = json_encode($value);
        }

        $this->settings_storage->updateValue($setting_name, $value);
    }

    /**
     * Gets setting value.
     *
     * @param string $setting_name
     * @param bool   $is_serialized
     *
     * @return array|bool|string
     */
    protected function getSettingValue($setting_name, $is_serialized = false)
    {
        $value = $this->settings_storage->getValue($setting_name, 'retailcrm');

        if ($is_serialized) {
            $value = @json_decode($value, true);

            if (!is_array($value)) {
                $value = array();
            }
        }

        return $value;
    }
}