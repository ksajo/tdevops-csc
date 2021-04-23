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

namespace Tygh\Api\Entities;

use Tygh\Api\Entities\v40\SraCartContent;
use Tygh\Api\Response;
use Tygh\Common\OperationResult;

class SraWishList extends SraCartContent
{
    /**
     * @var string $cart_type Wishlist cart type
     */
    protected $cart_type = 'W';

    /** @inheritdoc */
    public function index($id = '', $params = array())
    {
        $response = parent::index($id);

        if ($response['status'] == Response::STATUS_OK) {
            $response['data'] = array(
                'products' => $response['data']['products'],
            );
        }

        return $response;
    }

    /** @inheritdoc */
    public function addProducts(array $cart, array $cart_products, $is_update = false)
    {
        if ($is_update) {
            return parent::addProducts($cart, $cart_products, $is_update);
        }

        foreach ($cart_products as $product_cart_id => $product) {
            $product_id = isset($product['product_id'])
                ? (int) $product['product_id']
                : (int) $product_cart_id;

            $extra = [
                'product_options' => isset($product['product_options'])
                    ? $product['product_options']
                    : [],
            ];

            $cart_id = fn_generate_cart_id($product_id, $extra);

            if (isset($cart['products'][$cart_id])) {
                $result = new OperationResult(false);
                $result->addError(0, __('product_in_wishlist'));
                return $result;
            }
        }

        return parent::addProducts($cart, $cart_products, $is_update);
    }

    public function privilegesCustomer()
    {
        return [
            'index'  => $this->auth['is_token_auth'],
            'create' => $this->auth['is_token_auth'],
            'update' => $this->auth['is_token_auth'],
            'delete' => $this->auth['is_token_auth'],
        ];
    }
}