<?php

namespace Tygh\Shippings\YandexDelivery\Objects;

class OrderItem extends YandexObject
{
    public $_prefix = "orderitem_";
    public $_fields = array("article", "name", "quantity", "cost", "weight", "width", "height", "length", "id", "vat_value");
    public $_critical = array("name", "quantity", "cost");
}
