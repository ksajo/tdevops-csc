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


namespace Tygh\Addons\CommerceML\Repository;


use Tygh\Database\Connection;

/**
 * Class ImportEntityMapRepository
 *
 * @package Tygh\Addons\CommerceML\Repository
 */
class ImportEntityMapRepository
{
    const TABLE_NAME = 'commerceml_import_entity_map';

    /**
     * @var \Tygh\Database\Connection
     */
    private $db;

    /**
     * ImportEntityMapRepository constructor.
     *
     * @param \Tygh\Database\Connection $db Database connection instance
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Finds records
     *
     * @param array $params Search parameters
     *
     * @return array{
     *  array<array-key, array{company_id: int, entity_type: string, entity_id: string, entity_name: string, local_id: string}>,
     *  array{
     *      items_per_page: int,
     *      page: int,
     *      company_id: int,
     *      query: ?string,
     *      entity_type: ?string,
     *      limit: ?int,
     *      has_local_id: ?bool,
     *      order_by: ?string,
     *      local_id: null|string|int,
     *      local_ids: ?array<string|int>,
     *      entity_id_like: ?string,
     *      entity_types: ?array<string>
     *  }
     * }
     *
     * @psalm-param array{
     *  items_per_page?: int,
     *  page?: int,
     *  company_id?: int,
     *  query?: string,
     *  entity_type?: string,
     *  limit?: int,
     *  has_local_id?: bool,
     *  order_by?: string,
     *  local_id?: string|int,
     *  local_ids?: array<string|int>,
     *  entity_id_like?: string,
     *  entity_types?: array<string>
     * } $params
     */
    public function findAll(array $params)
    {
        $params = array_merge([
            'items_per_page' => 0,
            'page'           => 1,
            'company_id'     => 0,
            'limit'          => null,
            'has_local_id'   => null,
            'local_id'       => null,
            'local_ids'      => [],
            'entity_type'    => null,
            'entity_types'   => null,
            'query'          => null,
            'order_by'       => null,
            'entity_id_like' => null,
        ], $params);

        $conditions = [
            'company_id' => (int) $params['company_id'],
        ];

        if ($params['entity_type']) {
            $conditions['entity_type'] = (string) $params['entity_type'];
        }

        if ($params['entity_types']) {
            $conditions['entity_type'] = $params['entity_types'];
        }

        if ($params['query']) {
            $conditions[] = ['entity_name', 'LIKE', sprintf('%%%s%%', $params['query'])];
        }

        if ($params['entity_id_like']) {
            $conditions[] = ['entity_id', 'LIKE', sprintf('%%%s%%', $params['entity_id_like'])];
        }

        if (isset($params['has_local_id'])) {
            $conditions[] = [
                'local_id',
                $params['has_local_id'] ? '!=' : '=',
                ''
            ];
        }

        if (isset($params['local_id'])) {
            $conditions['local_id'] = (string) $params['local_id'];
        }

        if ($params['local_ids']) {
            $conditions['local_id'] = $params['local_ids'];
        }

        if ($params['limit']) {
            $limit = $this->db->quote(' LIMIT ?i', $params['limit']);
        } elseif (!empty($params['items_per_page'])) {
            $params['total_items'] = (int) $this->db->getField(
                'SELECT COUNT(*) FROM ?:?p WHERE ?w',
                self::TABLE_NAME,
                $conditions
            );

            $params['page'] = (int) $params['page'];
            $params['items_per_page'] = (int) $params['items_per_page'];

            $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
        } else {
            $limit = '';
        }

        if ($params['order_by'] === 'timestamp') {
            $order_by = $this->db->quote(' ORDER BY timestamp DESC, entity_name ASC');
        } else {
            $order_by = '';
        }

        /**
         * @var array<array-key, array{company_id: int, entity_type: string, entity_id: string, entity_name: string, local_id: string}> $records
         */
        $records = $this->db->getArray(
            'SELECT * FROM ?:?p WHERE ?w?p?p',
            self::TABLE_NAME,
            $conditions,
            $order_by,
            $limit
        );

        return [$records, $params];
    }

    /**
     * Finds entity ID by local ID
     *
     * @param string     $entity_type Entity type
     * @param string|int $local_id    Local ID
     * @param int|null   $company_id  Company ID
     *
     * @return array<string>
     */
    public function findEntityIds($entity_type, $local_id, $company_id = null)
    {
        $conditions = [
            'entity_type' => (string) $entity_type,
            'local_id'    => (string) $local_id,
        ];

        if ($company_id !== null) {
            $conditions['company_id'] = $company_id;
        }

        /** @var array<string> $entity_ids */
        $entity_ids = $this->db->getColumn(
            'SELECT entity_id FROM ?:?p WHERE ?w',
            self::TABLE_NAME,
            $conditions
        );

        return $entity_ids;
    }

    /**
     * Finds local ID for entity
     *
     * @param int    $company_id  Company ID
     * @param string $entity_type Entity type
     * @param string $entity_id   Entity ID
     *
     * @return string|null
     */
    public function findLocalId($company_id, $entity_type, $entity_id)
    {
        $id = $this->db->getField(
            'SELECT local_id FROM ?:?p WHERE ?w',
            self::TABLE_NAME,
            [
                'company_id'  => (int) $company_id,
                'entity_type' => (string) $entity_type,
                'entity_id'   => (string) $entity_id,
            ]
        );

        return $id ?: null;
    }

    /**
     * Adds
     *
     * @param int         $company_id  Company ID
     * @param string      $entity_type Entity type
     * @param string      $entity_id   Entity ID
     * @param string|int  $local_id    Local ID
     * @param string|null $entity_name Entity name
     */
    public function add($company_id, $entity_type, $entity_id, $local_id, $entity_name = null)
    {
        $data = [
            'company_id'  => (int) $company_id,
            'entity_type' => (string) $entity_type,
            'entity_id'   => (string) $entity_id,
            'local_id'    => (string) $local_id,
            'timestamp'   => time(),
        ];

        if ($entity_name) {
            $data['entity_name'] = (string) $entity_name;
        }

        $this->db->replaceInto(self::TABLE_NAME, $data, false, ['local_id', 'entity_name']);
    }

    /**
     * Updates timestamp
     *
     * @param int      $company_id  Company ID
     * @param string   $entity_type Entity type
     * @param string   $entity_id   Entity ID
     * @param null|int $timestamp   Timestamp
     */
    public function updateTimestamp($company_id, $entity_type, $entity_id, $timestamp = null)
    {
        if ($timestamp === null) {
            $timestamp = time();
        }

        $this->db->query(
            'UPDATE ?:?p SET ?u WHERE ?w',
            self::TABLE_NAME,
            ['timestamp' => $timestamp],
            [
                'company_id'  => (int) $company_id,
                'entity_type' => (string) $entity_type,
                'entity_id'   => (string) $entity_id,
            ]
        );
    }

    /**
     * Batch adds local id
     *
     * @param array<array{company_id: int, entity_type: string, entity_id: string, local_id: string|int}> $records Records
     */
    public function batchAdd(array $records)
    {
        $this->db->replaceInto(self::TABLE_NAME, $records, true, ['local_id', 'entity_name']);
    }

    /**
     * Removes map by local ID
     *
     * @param string     $entity_type Entity type
     * @param string|int $local_id    Local ID
     */
    public function removeByLocalId($entity_type, $local_id)
    {
        $this->removeByLocalIds($entity_type, [$local_id]);
    }

    /**
     * Removes map by local IDs
     *
     * @param string            $entity_type Entity type
     * @param array<string|int> $local_ids   Local IDs
     */
    public function removeByLocalIds($entity_type, array $local_ids)
    {
        $local_ids = array_filter($local_ids);

        if (empty($local_ids)) {
            return;
        }

        $this->db->query(
            'DELETE FROM ?:?p WHERE entity_type = ?s AND local_id IN (?a)',
            self::TABLE_NAME,
            $entity_type,
            (array) $local_ids
        );
    }

    /**
     * Removes map by external ID
     *
     * @param string     $entity_type Entity type
     * @param string|int $external_id External ID
     *
     * @return void
     */
    public function removeByExternalId($entity_type, $external_id)
    {
        $this->removeByExternalIds($entity_type, [$external_id]);
    }

    /**
     * Removes map by external IDs
     *
     * @param string            $entity_type  Entity type
     * @param array<string|int> $external_ids External IDs
     *
     * @return void
     */
    public function removeByExternalIds($entity_type, array $external_ids)
    {
        $external_ids = array_filter($external_ids);

        if (empty($external_ids)) {
            return;
        }

        $this->db->query(
            'DELETE FROM ?:?p WHERE entity_type = ?s AND entity_id IN (?a)',
            self::TABLE_NAME,
            $entity_type,
            $external_ids
        );
    }

    /**
     * Gets summary count of matched entities
     *
     * @param int           $company_id   Company ID
     * @param array<string> $entity_types List of entity types
     *
     * @return array<string, array{
     *   cnt: int,
     *   matched_cnt: int,
     *   unmatched_cnt: int,
     * }>
     */
    public function getCountSummary($company_id, array $entity_types = [])
    {
        $result = [];
        $data = $this->db->getArray(
            'SELECT entity_type, COUNT(local_id) AS cnt, local_id != ?s AS matched'
            . ' FROM ?:?p WHERE company_id = ?i AND entity_type IN (?a)'
            . ' GROUP BY entity_type, local_id != ?s',
            '',
            self::TABLE_NAME,
            $company_id,
            $entity_types,
            ''
        );

        foreach ($data as $datum) {
            if (!isset($result[$datum['entity_type']])) {
                $result[$datum['entity_type']] = [
                    'cnt'           => 0,
                    'matched_cnt'   => 0,
                    'unmatched_cnt' => 0
                ];
            }

            if ($datum['matched']) {
                $result[$datum['entity_type']]['matched_cnt'] = (int) $datum['cnt'];
            } else {
                $result[$datum['entity_type']]['unmatched_cnt'] = (int) $datum['cnt'];
            }

            $result[$datum['entity_type']]['cnt'] += (int) $datum['cnt'];
        }

        return $result;
    }
}
