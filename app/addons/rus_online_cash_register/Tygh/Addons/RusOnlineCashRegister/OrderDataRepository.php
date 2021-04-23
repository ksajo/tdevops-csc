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


namespace Tygh\Addons\RusOnlineCashRegister;

use Tygh\Database\Connection;

/**
 * Provides methods to access storage of the additional order data.
 *
 * @package Tygh\Addons\RusOnlineCashRegister
 */
class OrderDataRepository
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * OrderDataRepository constructor.
     *
     * @param Connection $connection Instance of connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Finds order data by order identifier.
     *
     * @param int $order_id Order identifier.
     *
     * @return OrderData|null
     */
    public function findByOrderId($order_id)
    {
        $data = $this->connection->getRow(
            'SELECT * FROM ?:rus_online_cash_register_order_data WHERE order_id = ?i',
            $order_id
        );

        if ($data) {
            return $this->createOrderData($data);
        }

        return null;
    }

    /**
     * Save order data.
     *
     * @param OrderData $order_data
     */
    public function save(OrderData $order_data)
    {
        $this->connection->replaceInto('rus_online_cash_register_order_data', $order_data->toArray());
    }

    /**
     * Remove order data by order identifier.
     *
     * @param int $order_id Order identifier.
     */
    public function removeById($order_id)
    {
        $this->connection->query('DELETE FROM ?:rus_online_cash_register_order_data WHERE order_id = ?i', $order_id);
    }

    /**
     * Create instance by data.
     *
     * @param array $data
     *
     * @return OrderData
     */
    protected function createOrderData(array $data)
    {
        return OrderData::fromArray($data);
    }
}