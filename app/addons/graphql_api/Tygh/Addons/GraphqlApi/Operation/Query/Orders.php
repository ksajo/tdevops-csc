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

namespace Tygh\Addons\GraphqlApi\Operation\Query;

use GraphQL\Deferred;
use Tygh\Addons\GraphqlApi\Context;
use Tygh\Addons\GraphqlApi\Operation\OperationInterface;

class Orders implements OperationInterface
{
    /**
     * @var mixed
     */
    protected $source;

    /**
     * @var array
     */
    protected $args;

    /**
     * @var \Tygh\Addons\GraphqlApi\Context
     */
    protected $context;

    public function __construct($source, array $args, Context $context)
    {
        $this->source = $source;
        $this->args = $args;
        $this->context = $context;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        $args = $this->args;
        $args['company_id'] = $this->context->getCompanyId();

        list($orders,) = fn_get_orders(
            $args,
            $this->args['items_per_page'],
            true,
            $this->context->getLanguageCode()
        );

        foreach ($orders as &$order_info) {
            $order_info = new Deferred(function () use ($order_info) {
                return fn_get_order_info(
                    $order_info['order_id'],
                    $this->args['native_language'],
                    true,
                    true,
                    false,
                    $this->context->getLanguageCode()
                );
            });
        }
        unset($order_info);

        return $orders;
    }

    /**
     * @return string|bool
     */
    public function getPrivilege()
    {
        return 'view_orders';
    }

    /**
     * @return string|bool
     */
    public function getCustomerPrivilege()
    {
        return false;
    }
}
