<?php

namespace Tygh\Shippings\YandexDelivery\Objects;

class Order extends YandexObject
{
    public $_prefix = "order_";
    public $_fields = array("num", "shipment_date", "shipment_type", "weight", "width", "height", "length", "payment_method", "delivery_cost", "assessed_value", "comment", "items", "sender", "requisite", "warehouse", "user_status_id", "total_cost", 'amount_prepaid');
    public $_critical = array("num");
  
    public $_wrongItem;
  
    public function x__construct()
    {
        parent::__construct();
    }
  
    public function appendItem(OrderItem $item)
    {
        if (!is_array($this->items)) {
            $this->items = array();
        }
      
        $appended = $item->appendToArray($this->items[count($this->items)], true);
        if (!$appended) {
            $this->_wrongItem = $item;
        }
    }
  
    public function validate()
    {
        $validated = parent::validate();
        if ($this->_wrongItem) {
            if (!$this->_wrongItem->validate()) {
               $validated = false;
            }
        }
    
        return $validated;
    }
}
