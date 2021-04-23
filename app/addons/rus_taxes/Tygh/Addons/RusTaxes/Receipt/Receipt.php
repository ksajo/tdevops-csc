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

namespace Tygh\Addons\RusTaxes\Receipt;


use Tygh\Tools\Math;

/**
 * Model of receipt.
 *
 * @package Tygh\Addons\RusTaxes\Receipt
 */
class Receipt
{
    /** @var string Customer email */
    protected $email;

    /** @var string Customer phone number */
    protected $phone;

    /** @var Item[] Receipt items */
    protected $items = array();

    /**
     * Receipt constructor.
     *
     * @param string $email Customer email
     * @param string $phone Customer phone number
     * @param Item[] $items Receipt items
     */
    public function __construct($email, $phone, array $items = array())
    {
        $this->setEmail($email);
        $this->setPhone($phone);
        $this->setItems($items);
    }

    /**
     * Gets receipt items.
     *
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Sets receipt items.
     *
     * @param Item[] $items Receipt items
     */
    public function setItems(array $items)
    {
        foreach ($items as $item) {
            $this->setItem($item);
        }
    }

    /**
     * Set receipt item.
     *
     * @param Item $item Receipt item
     */
    public function setItem(Item $item)
    {
        $this->items[] = $item;
    }

    /**
     * Gets customer email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets customer email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = trim($email);
    }

    /**
     * Gets customer phone number
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Sets customer phone number
     *
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = trim($phone);
    }

    /**
     * Gets receipt total.
     *
     * @param array $item_types If is set  total will be calculated by these types.
     *
     * @return float
     */
    public function getTotal(array $item_types = array())
    {
        $total = 0.0;

        foreach ($this->items as $item) {
            if ($this->isTypeAvailable($item->getType(), $item_types)) {
                $total += $item->getTotal();
            }
        }

        return $total;
    }

    /**
     * Divides total discount by receipt items.
     *
     * @param float  $total_discount      Order total discount
     * @param array  $item_types          If is set than the discount will be divided between items with these types
     * @param string $priority_item_type  If total of priority items are more than order discount, discount will be applied only on them
     */
    public function setTotalDiscount($total_discount, array $item_types = [], $priority_item_type = Item::TYPE_PRODUCT)
    {
        $total_discount = (float) $total_discount;

        if ($priority_item_type && $this->isTypeAvailable($priority_item_type, $item_types) && $this->getTotal([$priority_item_type]) > $total_discount) {
            $item_types = [$priority_item_type];
        }

        if (!empty($total_discount)) {
            $total = $this->getTotal($item_types);

            foreach ($this->items as $item) {
                if (!$this->isTypeAvailable($item->getType(), $item_types)) {
                    continue;
                }

                $item_total = $item->getTotal();
                $discount = $this->roundPrice($item_total / $total * $total_discount);

                $item->setTotalDiscount($item->getTotalDiscount() + $discount);

                $total_discount -= $discount;
                $total -= $item_total;
            }
        }
    }

    /**
     * Allocates total discounts for receipt items by unit.
     * As a result of the operation, new  receipt items can be allocated.
     *
     * @param array $item_types     If is set than the discount remainder will be divided between items with these types
     */
    public function allocateDiscountByUnit(array $item_types = array())
    {
        $discount_remainder = 0.0;

        foreach ($this->items as $item) {
            $total_discount = $item->getTotalDiscount();

            if (!empty($total_discount)) {
                $discount = $this->roundPrice($total_discount / $item->getQuantity());

                $item->setPrice($item->getPrice() - $discount);
                $item->setTotalDiscount(0);

                $discount_remainder += $total_discount - $discount * $item->getQuantity();
            }
        }

        if (!empty($discount_remainder)) {
            foreach ($this->items as $item) {
                if ($this->isTypeAvailable($item->getType(), $item_types)
                    && $item->getQuantity() == 1
                    && $item->getPrice() > $discount_remainder
                ) {
                    $item->setPrice($item->getPrice() - $discount_remainder);
                    $discount_remainder = 0;
                    break;
                }
            }
        }

        if (!empty($discount_remainder)) {
            foreach ($this->items as $item) {
                if ($this->isTypeAvailable($item->getType(), $item_types)
                    && $item->getQuantity() > 1
                    && $item->getPrice() > $discount_remainder
                ) {
                    $clone_item = clone $item;
                    $clone_item->setQuantity(1);
                    $clone_item->setPrice($clone_item->getPrice() - $discount_remainder);

                    $item->setQuantity($item->getQuantity() - 1);
                    $this->setItem($clone_item);
                    break;
                }
            }
        }
    }

    /**
     * Remove item from receipt.
     *
     * @param int|string    $id     Receipt item identifier
     * @param string        $type   Receipt item type (product, shipping, surcharge)
     *
     * @return bool
     */
    public function removeItem($id, $type)
    {
        $result = false;

        foreach ($this->items as $key => $item) {
            if ($item->getId() == $id && $item->getType() == $type) {
                unset($this->items[$key]);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Update item quantity.
     *
     * @param int|string    $id         Receipt item identifier
     * @param string        $type       Receipt item type (product, shipping, surcharge)
     * @param float         $quantity   New receipt item quantity
     *
     * @return bool
     */
    public function setItemQuantity($id, $type, $quantity)
    {
        $result = false;
        $keys = array();
        $quantity = (float) $quantity;
        $current_quantity = 0;

        foreach ($this->items as $key => $item) {
            if ($item->getId() == $id && $item->getType() == $type) {
                $keys[] = $key;
                $current_quantity += $item->getQuantity();
            }
        }

        if (!empty($keys) && $current_quantity != $quantity) {
            $diff = $quantity - $current_quantity;

            if ($diff > 0) {
                $item = $this->items[reset($keys)];
                $item->setQuantity($item->getQuantity() + $diff);
            } else {
                foreach ($keys as $key) {
                    $item = $this->items[$key];

                    $diff = $item->getQuantity() + $diff;

                    if ($diff <= 0) {
                        unset($this->items[$key]);
                    } else {
                        $item->setQuantity($diff);
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Gets receipt item.
     *
     * @param int|string    $id         Receipt item identifier
     * @param string        $type       Receipt item type (product, shipping, surcharge)
     *
     * @return null|Item
     */
    public function getItem($id, $type)
    {
        foreach ($this->items as $key => $item) {
            if ($item->getId() == $id && $item->getType() == $type) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Converts receipt to array
     *
     * @return array
     */
    public function toArray()
    {
        $result = array(
            'email' => $this->email,
            'phone' => $this->phone,
            'items' => array()
        );

        foreach ($this->items as $item) {
            $result['items'][] = $item->toArray();
        }

        return $result;
    }

    /**
     * Rounds price value.
     *
     * @param float $price
     *
     * @return float
     */
    public static function roundPrice($price)
    {
        $price = round($price, 6, PHP_ROUND_HALF_DOWN);

        if ($price >= 0) {
            return Math::floorToPrecision($price, 0.01);
        } else {
            return Math::ceilToPrecision($price, 0.01);
        }
    }

    /**
     * Checks if item type is available.
     *
     * @param string    $type       Item type (product, shipping, etc)
     * @param array     $item_types List of the item types
     *
     * @return bool
     */
    protected function isTypeAvailable($type, $item_types)
    {
        return empty($item_types) || in_array($type, $item_types, true);
    }
}