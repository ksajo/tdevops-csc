<?php

namespace Tygh\Shippings\YandexDelivery\Objects;

class RequestDeliveryList extends YandexObject
{
    public $_fields = array("city_from", "city_to", "weight", "height", "width", "length", "create_date", "index_city", "total_cost", "delivery_type");
    public $_critical = array("city_from", "city_to", "weight", "height", "width", "length");
}
