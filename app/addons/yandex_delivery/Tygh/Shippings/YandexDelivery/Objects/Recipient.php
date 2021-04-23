<?php

namespace Tygh\Shippings\YandexDelivery\Objects;

class Recipient extends YandexObject
{
    public $_prefix = "recipient_";
    public $_fields = array("first_name", "middle_name", "last_name", "phone", "email", "comment", "time_from", "time_to");
    public $_critical = array("first_name", "last_name");
  
    public function fixField($name, $value)
    {
        if ($name == 'phone') {
            if (is_array($value)) {
                foreach ($value as $key => $phone) {
                    $value[$key] = preg_replace("/[^0-9]/", '', $phone);
                }
            } else {
                $value = preg_replace("/[^0-9]/", '', $value);
            }
        }

        return parent::fixField($name, $value);
    }
  
    /**
     * @param Order|null $order
     * @return bool
     */
    public function validate($order = null)
    {
        if (isset($order->user_status_id) && $order->user_status_id == ORDER_DRAFT_STATUS) {
            $this->_critical = array();
        } else {
            $this->_critical = array("first_name", "last_name");
        }
    
        return parent::validate();
    }
}
