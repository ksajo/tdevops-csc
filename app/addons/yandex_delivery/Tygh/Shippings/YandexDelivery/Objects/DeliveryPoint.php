<?php

namespace Tygh\Shippings\YandexDelivery\Objects;


class DeliveryPoint extends YandexObject
{
    public $_prefix = "deliverypoint_";
    public $_fields = array("index", "city", "street", "house", "build", "housing", "porch", "code", "floor", "flat", "station");
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
