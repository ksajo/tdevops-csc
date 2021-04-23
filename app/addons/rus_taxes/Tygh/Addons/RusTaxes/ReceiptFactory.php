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

namespace Tygh\Addons\RusTaxes;


use Exception;
use Tygh\Addons\RusTaxes\Receipt\Item;
use Tygh\Addons\RusTaxes\Receipt\Receipt;

/**
 * Provides methods to creating receipt from order.
 *
 * @package Tygh\Addons\RusTaxes
 */
class ReceiptFactory
{
    const TAX_TYPE_PRODUCT = 'P';

    const TAX_TYPE_SHIPPING = 'S';

    const TAX_TYPE_PAYMENT_SURCHARGE = 'PS';

    /** @var string Primary currency code (RUB, USD) */
    protected $primary_currency;

    /** @var array List of local taxes to external taxes */
    protected $taxes_map;

    /** @var bool Prices with taxes (settings.Appearance.cart_prices_w_taxes)  */
    protected $prices_with_taxes;

    /** @var string Callback function for convert price */
    protected $currency_converter_callback;


    /**
     * ReceiptFactory constructor.
     *
     * @param string      $primary_currency             Primary currency code (RUB, USD)
     * @param array       $taxes_map                    Tax types map (tax_id => tax_type)
     * @param bool        $prices_with_taxes            Prices with taxes (settings.Appearance.cart_prices_w_taxes)
     * @param string      $currency_converter_callback  Callback function for converting price
     *
     * @throws Exception
     */
    public function __construct(
        $primary_currency,
        array $taxes_map,
        $prices_with_taxes,
        $currency_converter_callback = 'fn_format_price_by_currency'
    )
    {
        $this->primary_currency = $primary_currency;
        $this->taxes_map = $taxes_map;
        $this->currency_converter_callback = $currency_converter_callback;
        $this->prices_with_taxes = $prices_with_taxes;

        if (!is_callable($currency_converter_callback)) {
            throw new Exception('Argument currency_converter_callback must be a callable');
        }
    }

    /**
     * Creates receipt from order.
     *
     * @param array  $order                             Order data
     * @param string $currency                          Currency code (RUB, USD),
     *                                                  If currency code different from primary currency code then prices will be converted.
     * @param bool   $allocate_discount_by_unit         Whether to allocate item discount by unit
     * @param array  $total_discount_item_types_filter  If is set than the discount will be divided between items with these types
     * @param string $priority_item_type                If total of priority items are more than order discount, discount will be applied only on them
     *
     * @return Receipt|null Returns the receipt instance on success otherwise null
     */
    public function createReceiptFromOrder(
        array $order,
        $currency,
        $allocate_discount_by_unit = true,
        array $total_discount_item_types_filter = array(),
        $priority_item_type = Item::TYPE_PRODUCT
    )
    {
        $order = $this->prepareOrder($order);
        $subtotal_discount = $order['subtotal_discount'];
        $order_total = $order['total'];

        $items = array();

        foreach ($order['products'] as $cart_id => $product) {
            if (!empty($product['extra']['exclude_from_calculate']) || $product['price'] <= 0) {
                continue;
            }

            $items[] = new Item(
                $cart_id,
                Item::TYPE_PRODUCT,
                $product['product'],
                isset($product['product_code']) ? $product['product_code'] : '',
                $this->convertPrice($product['price'], $currency),
                $product['amount'],
                $this->getTaxTypeByTaxIds(empty($product['receipt_tax_ids']) ? array() : $product['receipt_tax_ids']),
                $this->convertPrice(
                    empty($product['receipt_tax_sum']) ? 0 : $product['receipt_tax_sum'],
                    $currency
                )
            );
        }

        if (!empty($order['payment_surcharge'])) {
            $name = empty($order['payment_method']['surcharge_title'])
                ? __('payment_surcharge')
                : $order['payment_method']['surcharge_title'];

            $items[] = new Item(
                0,
                Item::TYPE_SURCHARGE,
                $name,
                'PS',
                $this->convertPrice($order['payment_surcharge'], $currency),
                1,
                $this->getTaxTypeByTaxIds(
                    empty($order['receipt_payment_surcharge_tax_ids'])
                        ? array()
                        : $order['receipt_payment_surcharge_tax_ids']
                ),
                $this->convertPrice(
                    empty($order['receipt_payment_surcharge_tax_sum']) ? 0 : $order['receipt_payment_surcharge_tax_sum'],
                    $currency
                )
            );
        }

        if (!empty($order['shipping_cost'])) {
            $items[] = new Item(
                0,
                Item::TYPE_SHIPPING,
                __('shipping'),
                'SHIPPING',
                $this->convertPrice($order['shipping_cost'], $currency),
                1,
                $this->getTaxTypeByTaxIds(
                    empty($order['receipt_shipping_tax_ids'])
                        ? array()
                        : $order['receipt_shipping_tax_ids']
                ),
                $this->convertPrice(
                    empty($order['receipt_shipping_tax_sum']) ? 0 : $order['receipt_shipping_tax_sum'],
                    $currency
                )
            );
        }

        if (!empty($order['gift_certificates'])) {
            foreach ($order['gift_certificates'] as $cart_id => $certificate) {
                if ($certificate['amount'] > 0) {
                    $items[] = new Item(
                        $cart_id,
                        Item::TYPE_GIFT_CERTIFICATE,
                        __('gift_certificate'),
                        isset($certificate['gift_cert_code']) ? $certificate['gift_cert_code'] : '',
                        $this->convertPrice($certificate['amount'], $currency),
                        1,
                        TaxType::NONE,
                        0
                    );
                }
            }
        }

        /**
         * Executes after receipt items are populated from order, allows to modify receipt items.
         *
         * @param self    $this                          Receipt factory instance
         * @param array   $order                         Info of the order to build receipt for
         * @param string  $currency                      Currency code
         * @param bool    $allocate_discount_by_unit     Whether to allocate item discount by unit
         * @param Item[]  $items                         Receipt items
         */
        fn_set_hook('create_receipt_from_order', $this, $order, $currency, $allocate_discount_by_unit, $items);

        $receipt = new Receipt(
            isset($order['email']) ? $order['email'] : '',
            isset($order['phone']) ? $order['phone'] : '',
            $items
        );

        $discount_value = $this->convertPrice($subtotal_discount, $currency);
        $order_total = $this->convertPrice($order_total, $currency);
        $receipt_total = $receipt->getTotal();

        $diff = $this->roundPrice($receipt_total - $discount_value - $order_total);

        if (!empty($diff)) {
            $discount_value += $diff;
        }

        $receipt->setTotalDiscount($discount_value, $total_discount_item_types_filter, $priority_item_type);

        if ($allocate_discount_by_unit) {
            $receipt->allocateDiscountByUnit($total_discount_item_types_filter);
        }

        /**
         * Executes after receipt is created from order, allows to modify the receipt.
         *
         * @param self    $this                          Receipt factory instance
         * @param array   $order                         Info of the order to build receipt for
         * @param string  $currency                      Currency code
         * @param bool    $allocate_discount_by_unit     Whether to allocate item discount by unit
         * @param Receipt $receipt                       Receipt instance
         */
        fn_set_hook('create_receipt_from_order_post', $this, $order, $currency, $allocate_discount_by_unit, $receipt);

        if ($receipt->getItems()) {
            return $receipt;
        }

        return null;
    }

    /**
     * Prepares order.
     *
     * @param array $order Order data
     *
     * @return array Prepared order data
     */
    protected function prepareOrder($order)
    {
        if (!isset($order['subtotal_discount'])) {
            $order['subtotal_discount'] = 0;
        } else {
            $order['subtotal_discount'] = (float) $order['subtotal_discount'];
        }

        if (!isset($order['total'])) {
            $order['total'] = 0;
        } else {
            $order['total'] = (float) $order['total'];
        }

        if (!isset($order['shipping_cost'])) {
            $order['shipping_cost'] = 0;
        } else {
            $order['shipping_cost'] = (float) $order['shipping_cost'];
        }

        if (!isset($order['payment_surcharge'])) {
            $order['payment_surcharge'] = 0;
        } else {
            $order['payment_surcharge'] = (float) $order['payment_surcharge'];
        }

        if (empty($order['taxes'])) {
            return $order;
        }

        foreach ($order['taxes'] as $tax_id => $tax) {
            $is_included_tax = $tax['price_includes_tax'] != 'N';

            if (isset($tax['applies']['items'])) { // calculate tax on subtotal
                foreach ($tax['applies']['items'] as $item_type => $items) {
                    if (empty($items)) {
                        continue;
                    }

                    if (!isset($tax['applies'][$item_type])) {
                        $tax['applies'][$item_type] = 0;
                    }

                    switch ($item_type) {
                        case self::TAX_TYPE_PRODUCT:
                            $cart_ids = array_keys($items);
                            $products_total = 0;

                            foreach ($cart_ids as $cart_id) {
                                if (!isset($order['products'][$cart_id])) {
                                    continue;
                                }

                                if (!isset($order['products'][$cart_id]['receipt_tax_ids'])) {
                                    $order['products'][$cart_id]['receipt_tax_sum'] = 0;
                                    $order['products'][$cart_id]['receipt_tax_ids'] = array();
                                }

                                $order['products'][$cart_id]['receipt_tax_ids'][] = $tax_id;
                                $products_total += $order['products'][$cart_id]['price'] * $order['products'][$cart_id]['amount'];
                            }

                            foreach ($cart_ids as $cart_id) {
                                if (!isset($order['products'][$cart_id])) {
                                    continue;
                                }

                                $product = & $order['products'][$cart_id];

                                $tax_sum = $this->roundPrice($tax['applies'][$item_type] * $product['price'] / $products_total);
                                $product['receipt_tax_sum'] += $tax_sum;

                                if (!$is_included_tax) {
                                    $product['price'] += $tax_sum;
                                }

                                unset($product);
                            }

                            break;
                        case self::TAX_TYPE_SHIPPING:
                            if (!isset($order['receipt_shipping_tax_ids'])) {
                                $order['receipt_shipping_tax_sum'] = 0;
                                $order['receipt_shipping_tax_ids'] = array();
                            }

                            $order['receipt_shipping_tax_ids'][] = $tax_id;
                            $order['receipt_shipping_tax_sum'] += $tax['applies'][$item_type];

                            if (!$is_included_tax) {
                                $order['shipping_cost'] += $tax['applies'][$item_type];
                            }
                            break;
                        case self::TAX_TYPE_PAYMENT_SURCHARGE:
                            if (!isset($order['receipt_payment_surcharge_tax_ids'])) {
                                $order['receipt_payment_surcharge_tax_sum'] = 0;
                                $order['receipt_payment_surcharge_tax_ids'] = array();
                            }

                            $order['receipt_payment_surcharge_tax_ids'][] = $tax_id;
                            $order['receipt_payment_surcharge_tax_sum'] += $tax['applies'][$item_type];

                            if (!$is_included_tax) {
                                $order['payment_surcharge'] += $tax['applies'][$item_type];
                            }
                            break;
                    }
                }
            } elseif (!empty($tax['applies'])) { // calculate tax on unit price
                foreach ($tax['applies'] as $key => $tax_sum) {
                    list($item_type, $cart_id) = explode('_', $key, 2);

                    switch ($item_type) {
                        case self::TAX_TYPE_PRODUCT:
                            if (!isset($order['products'][$cart_id])) {
                                continue 2;
                            }

                            if (!isset($order['products'][$cart_id]['receipt_tax_ids'])) {
                                $order['products'][$cart_id]['receipt_tax_sum'] = 0;
                                $order['products'][$cart_id]['receipt_tax_ids'] = array();
                            }

                            $sum = $this->roundPrice($tax_sum / $order['products'][$cart_id]['amount']);
                            $order['products'][$cart_id]['receipt_tax_sum'] += $sum;
                            $order['products'][$cart_id]['receipt_tax_ids'][] = $tax_id;

                            if (!$is_included_tax) {
                                $order['products'][$cart_id]['price'] += $sum;
                            }
                            break;
                        case self::TAX_TYPE_SHIPPING:
                            if (!isset($order['receipt_shipping_tax_ids'])) {
                                $order['receipt_shipping_tax_sum'] = 0;
                                $order['receipt_shipping_tax_ids'] = array();
                            }

                            $order['receipt_shipping_tax_sum'] += $tax_sum;
                            $order['receipt_shipping_tax_ids'][] = $tax_id;
                            break;
                        case self::TAX_TYPE_PAYMENT_SURCHARGE:
                            if (!isset($order['receipt_payment_surcharge_tax_ids'])) {
                                $order['receipt_payment_surcharge_tax_sum'] = 0;
                                $order['receipt_payment_surcharge_tax_ids'] = array();
                            }

                            $order['receipt_payment_surcharge_tax_ids'][] = $tax_id;
                            $order['receipt_payment_surcharge_tax_sum'] += $tax_sum;

                            if (!$is_included_tax && !$this->prices_with_taxes) {
                                $order['payment_surcharge'] += $tax_sum;
                            }
                            break;

                    }
                }
            }
        }

        return $order;
    }

    /**
     * Convert price.
     *
     * @param float     $price      Base price
     * @param string    $currency   Currency code
     *
     * @return float
     */
    protected function convertPrice($price, $currency)
    {
        if ($this->primary_currency !== $currency) {
            $price = call_user_func($this->currency_converter_callback, $price, $this->primary_currency, $currency);
        }

        $price = $this->roundPrice($price);

        return $price;
    }

    /**
     * Gets tax type by tax identifier.
     *
     * @param array $tax_ids Tax identifiers
     *
     * @return string
     */
    protected function getTaxTypeByTaxIds(array $tax_ids)
    {
        $result = TaxType::NONE;

        foreach ($tax_ids as $tax_id) {
            if (isset($this->taxes_map[$tax_id])) {
                $result = $this->taxes_map[$tax_id];
            }

            if ($result !== TaxType::NONE) {
                break;
            }
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
    protected function roundPrice($price)
    {
       return Receipt::roundPrice($price);
    }
}
