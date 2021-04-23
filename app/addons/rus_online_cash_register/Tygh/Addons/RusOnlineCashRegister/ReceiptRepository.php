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

use Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt;
use Tygh\Addons\RusOnlineCashRegister\Receipt\Requisites;
use Tygh\Database\Connection;

/**
 * Provides methods to access storage of the receipt.
 *
 * @package Tygh\Addons\RusOnlineCashRegister
 */
class ReceiptRepository
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * ReceiptRepository constructor.
     *
     * @param Connection $connection    Instance of connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Searches receipts.
     *
     * @param array|null    $conditions List of search conditions
     * @param int           $limit      Limit
     * @param int           $offset     Offset
     *
     * @return Receipt[]
     */
    public function search(array $conditions = array(), $limit = 20, $offset = 0)
    {
        $result = array();
        $sql_condition = '1 = 1';

        if ($conditions) {
            $sql_condition = db_quote('?w', $conditions);
        }

        $rows = $this->connection->getArray(
            'SELECT * FROM ?:rus_online_cash_register_receipts WHERE ?p ORDER BY id DESC LIMIT ?i OFFSET ?i',
            $sql_condition, $limit, $offset
        );

        foreach ($rows as $item) {
            $result[] = $this->createReceipt($item);
        }

        return $result;
    }

    /**
     * Gets count receipt by conditions.
     *
     * @param array|null $conditions  List of search conditions
     *
     * @return int
     */
    public function count(array $conditions = array())
    {
        $sql_condition = '1 = 1';

        if ($conditions) {
            $sql_condition = db_quote('?w', $conditions);
        }

        $cnt = $this->connection->getField(
            'SELECT COUNT(*) FROM ?:rus_online_cash_register_receipts WHERE ?p',
            $sql_condition
        );

        return $cnt;
    }

    /**
     * Save receipt.
     *
     * @param Receipt $receipt Instance of receipt.
     */
    public function save(Receipt $receipt)
    {
        $data = $receipt->toArray();

        if (!empty($data['requisites'])) {
            $data = array_merge($data, $data['requisites']);
        }

        unset($data['requisites']);

        $data['items'] = json_encode($data['items']);
        $data['payments'] = json_encode($data['payments']);

        if ($receipt->getId()) {
            $data['updated_timestamp'] = time();
            $this->connection->query(
                'UPDATE ?:rus_online_cash_register_receipts SET ?u WHERE id = ?s',
                $data,
                $receipt->getId()
            );
        } else {
            $data['created_timestamp'] = $data['updated_timestamp'] = time();
            $id = $this->connection->query('INSERT INTO ?:rus_online_cash_register_receipts ?e', $data);

            $receipt->setId($id);
        }
    }

    /**
     * Finds receipts by object.
     *
     * @param string    $object_type    Object type.
     * @param int       $object_id      Object identifier.
     *
     * @return Receipt[]
     */
    public function findAllByObject($object_type, $object_id)
    {
        $result = array();
        $items = $this->connection->getArray(
            'SELECT * FROM ?:rus_online_cash_register_receipts WHERE object_type = ?s AND object_id = ?i ORDER BY created_timestamp',
            $object_type, $object_id
        );

        foreach ($items as $item) {
            $result[] = $this->createReceipt($item);
        }

        return $result;
    }

    /**
     * Finds receipt by identifier.
     *
     * @param string $id Receipt identifier.
     *
     * @return null|Receipt
     */
    public function findById($id)
    {
        $data = $this->connection->getRow(
            'SELECT * FROM ?:rus_online_cash_register_receipts WHERE id = ?s',
            $id
        );

        if ($data) {
            return $this->createReceipt($data);
        }

        return null;
    }

    /**
     * Finds receipt by uuid.
     *
     * @param string $uuid Receipt uuid.
     *
     * @return null|Receipt
     */
    public function findByUUID($uuid)
    {
        $data = $this->connection->getRow(
            'SELECT * FROM ?:rus_online_cash_register_receipts WHERE uuid = ?s',
            $uuid
        );

        if ($data) {
            return $this->createReceipt($data);
        }

        return null;
    }

    /**
     * Remove receipt by object type and object identifier.
     *
     * @param string    $object_type    Object type.
     * @param int       $object_id      Object identifier.
     */
    public function removeByObject($object_type, $object_id)
    {
        $this->connection->query(
            'DELETE FROM ?:rus_online_cash_register_receipts WHERE object_type = ?s AND object_id = ?i',
            $object_type,
            $object_id
        );
    }

    /**
     * Create instance an receipt by data.
     *
     * @param array $data
     *
     * @return Receipt
     */
    protected function createReceipt(array $data)
    {
        $data['items'] = @json_decode($data['items'], true);
        $data['payments'] = @json_decode($data['payments'], true);

        if (!is_array($data['items'])) {
            $data['items'] = array();
        }

        if (!is_array($data['payments'])) {
            $data['payments'] = array();
        }

        $data['requisites'] = Requisites::fromArray($data);

        return Receipt::fromArray($data);
    }
}