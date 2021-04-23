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


namespace Tygh\Addons\RusOnlineCashRegister\Receipt;

use Tygh\Addons\RusTaxes\Receipt\Item as BaseItem;
use Tygh\Addons\RusTaxes\Receipt\Receipt as BaseReceipt;

/**
 * Model of receipt item.
 *
 * @package Tygh\Addons\RusOnlineCashRegister\Receipt
 */
class Item
{
    /** @var string|null */
    protected $name;

    /** @var float|null */
    protected $price;

    /** @var float|null  */
    protected $quantity;

    /** @var string|null */
    protected $tax_type;

    /** @var float|null */
    protected $tax_sum;

    /** @var float|null */
    protected $discount;

    /**
     * Item constructor.
     *
     * @param string        $name           Item name
     * @param float         $price          Item price
     * @param float         $quantity       Item quantity
     * @param string        $tax_type       Item tax type
     * @param float         $tax_sum        Item tax sum
     * @param float         $discount       Item total discount
     */
    public function __construct($name, $price, $quantity, $tax_type, $tax_sum, $discount = 0.0)
    {
        if ($name !== null) {
            $this->name = (string) $name;
        }

        if ($price !== null) {
            $this->price = (float) $price;
        }

        if ($quantity !== null) {
            $this->quantity = (float) $quantity;
        }

        if ($tax_type !== null) {
            $this->tax_type = $tax_type;
        }

        if ($tax_sum !== null) {
            $this->tax_sum = $tax_sum;
        }

        $this->discount = $discount;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return float|null
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return string|null
     */
    public function getTaxType()
    {
        return $this->tax_type;
    }

    /**
     * @return float|null
     */
    public function getTaxSum()
    {
        return $this->tax_sum;
    }

    /**
     * Gets item total sum.
     *
     * @return float
     */
    public function getSum()
    {
        return BaseReceipt::roundPrice($this->quantity * $this->price - $this->discount);
    }

    /**
     * @return float|null
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Convert to array.
     *
     * @return array
     */
    public function toArray()
    {
        $result = array(
            'name' => $this->name,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'tax_type' => $this->tax_type,
            'tax_sum' => $this->tax_sum,
            'discount' => $this->discount
        );

        return $result;
    }

    /**
     * Create object from array.
     *
     * @param array $data
     *
     * @return Item
     */
    public static function fromArray(array $data)
    {
        return new self(
            isset($data['name']) ? $data['name'] : null,
            isset($data['price']) ? $data['price'] : null,
            isset($data['quantity']) ? $data['quantity'] : null,
            isset($data['tax_type']) ? $data['tax_type'] : null,
            isset($data['tax_sum']) ? $data['tax_sum'] : null,
            isset($data['discount']) ? $data['discount'] : 0.0
        );
    }

    /**
     * Create receipt item from base receipt item.
     *
     * @param BaseItem $item
     *
     * @return Item
     */
    public static function fromBaseReceiptItem(BaseItem $item)
    {
        return new self(
            $item->getName(),
            $item->getPrice(),
            $item->getQuantity(),
            $item->getTaxType(),
            $item->getTaxSum(),
            $item->getTotalDiscount()
        );
    }
}