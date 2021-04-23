<?php

namespace Tygh\Shippings\YandexDelivery\Objects;

class Delivery extends YandexObject
{
    public $_prefix = "delivery_";
    public $_fields = array("direction", "delivery", "price", "pickuppoint", "to_ms_warehouse", "interval", "tariff", "to_yd_warehouse");
    public $_critical = array();
  
    /**
     * @param Order|null $order
     * @return bool
     */
    public function validate($order = null)
    {
        if (isset($order->user_status_id) && $order->user_status_id == ORDER_DRAFT_STATUS) {
            $this->_critical = array();
        }
    
        return parent::validate();
    }
}
