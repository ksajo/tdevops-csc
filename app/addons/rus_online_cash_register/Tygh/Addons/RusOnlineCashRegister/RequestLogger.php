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
 * Provides methods for logging requests.
 *
 * @package Tygh\Addons\RusOnlineCashRegister
 */
class RequestLogger
{
    /** Maximum life time for requests record */
    const LOG_MAX_LIFE_TIME = 7776000; //three months

    const STATUS_SEND = 1;

    const STATUS_SUCCESS = 2;

    const STATUS_FAIL = 3;

    /** @var Connection */
    protected $connection;

    /**
     * RequestsRepository constructor.
     *
     * @param Connection $connection    Instance of connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Searches logs.
     *
     * @param array|null    $conditions List of the search conditions
     * @param int           $limit      Limit
     * @param int           $offset     Offset
     *
     * @return array
     */
    public function search(array $conditions = array(), $limit = 20, $offset = 0)
    {
        $sql_condition = '1 = 1';

        if ($conditions) {
            $sql_condition = db_quote('?w', $conditions);
        }

        $rows = $this->connection->getArray(
            'SELECT * FROM ?:rus_online_cash_register_request_logs WHERE ?p ORDER BY id DESC LIMIT ?i OFFSET ?i',
            $sql_condition, $limit, $offset
        );

        return $rows;
    }

    /**
     * Gets count logs by conditions.
     *
     * @param array|null $conditions List of the search conditions
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
            'SELECT COUNT(*) FROM ?:rus_online_cash_register_request_logs WHERE ?p',
            $sql_condition
        );

        return $cnt;
    }

    /**
     * Logs the request.
     *
     * @param string        $url            URL
     * @param string        $request        Serialized request data
     * @param string        $response       Serialized response data
     * @param int           $status         Status
     * @param null|string   $message        Message
     *
     * @return int Log identifier of the inserted data.
     */
    public function log($url, $request, $response, $status, $message = null)
    {
        $this->deleteOld();

        $data = array(
            'timestamp' => time(),
            'url' => $url,
            'status' => $status,
            'request' => $request,
            'response' => $response,
            'message' => $message
        );
        return $this->connection->query('INSERT INTO ?:rus_online_cash_register_request_logs ?e', $data);
    }

    /**
     * Logs the start request.
     *
     * @param string    $url        Url
     * @param string    $request    Serialized request data
     *
     * @return int Log identifier of the inserted data.
     */
    public function startRequest($url, $request)
    {
        return $this->log($url, $request, null, self::STATUS_SEND);
    }

    /**
     * Updates log and sets status as failed.
     *
     * @param int           $log_id     Log identifier
     * @param string        $response   Serialized response data.
     * @param null|string   $message    Message
     */
    public function failRequest($log_id, $response, $message = null)
    {
        $data = array(
            'status' => self::STATUS_FAIL,
            'response' => $response,
            'message' => $message
        );

        $this->update($log_id, $data);
    }

    /**
     * Updates log and sets status as success.
     *
     * @param int           $log_id     Log identifier
     * @param string        $response   Serialized response data.
     * @param null|string   $message    Message
     */
    public function successRequest($log_id, $response, $message = null)
    {
        $data = array(
            'status' => self::STATUS_SUCCESS,
            'response' => $response,
            'message' => $message
        );

        $this->update($log_id, $data);
    }

    /**
     * Deletes old records.
     */
    public function deleteOld()
    {
        $timestamp = time() - self::LOG_MAX_LIFE_TIME;

        $this->connection->query('DELETE FROM ?:rus_online_cash_register_request_logs WHERE timestamp <= ?i', $timestamp);
    }

    /**
     * Updates record.
     *
     * @param int   $log_id
     * @param array $data
     *
     * @return mixed
     */
    protected function update($log_id, $data)
    {
        return $this->connection->query('UPDATE ?:rus_online_cash_register_request_logs SET ?u WHERE id = ?s', $data, $log_id);
    }
}