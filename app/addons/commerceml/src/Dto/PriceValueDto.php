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


namespace Tygh\Addons\CommerceML\Dto;

/**
 * Class PriceValueDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class PriceValueDto
{
    /**
     * @var \Tygh\Addons\CommerceML\Dto\IdDto
     */
    public $price_type_id;

    /**
     * @var float
     */
    public $price;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\IdDto|null
     */
    public $currency_code;

    /**
     * @param \Tygh\Addons\CommerceML\Dto\IdDto      $price_type_id Price type object instance
     * @param float                                  $price         Price
     * @param \Tygh\Addons\CommerceML\Dto\IdDto|null $currency_code Currency code
     *
     * @return \Tygh\Addons\CommerceML\Dto\PriceValueDto
     */
    public static function create(IdDto $price_type_id, $price, IdDto $currency_code = null)
    {
        $object = new self();
        $object->price_type_id = $price_type_id;
        $object->price = (float) $price;
        $object->currency_code = $currency_code;

        return $object;
    }
}
