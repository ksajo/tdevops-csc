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
 * Class ImportRemovedEntityRepository
 *
 * @package Tygh\Addons\CommerceML\Repository
 */
class ImportRemovedEntityRepository
{
    const TABLE_NAME = 'commerceml_import_removed_entities';

    /**
     * @var \Tygh\Database\Connection
     */
    private $db;

    /**
     * ImportRemovedEntityRepository constructor.
     *
     * @param \Tygh\Database\Connection $db Database connection instance
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Adds record
     *
     * @param int    $company_id  Company ID
     * @param string $entity_type Entity type
     * @param string $entity_id   Entity id
     */
    public function add($company_id, $entity_type, $entity_id)
    {
        $this->db->replaceInto(self::TABLE_NAME, [
            'company_id'  => (int) $company_id,
            'entity_type' => (string) $entity_type,
            'entity_id'   => (string) $entity_id
        ]);
    }

    /**
     * Removes record
     *
     * @param int    $company_id  Company ID
     * @param string $entity_type Entity type
     * @param string $entity_id   Entity id
     */
    public function remove($company_id, $entity_type, $entity_id)
    {
        $this->db->query(
            'DELETE FROM ?:?p WHERE ?w',
            self::TABLE_NAME,
            [
                'company_id'  => (int) $company_id,
                'entity_type' => (string) $entity_type,
                'entity_id'   => (string) $entity_id
            ]
        );
    }

    /**
     * Checks if record exists
     *
     * @param int    $company_id  Company ID
     * @param string $entity_type Entity type
     * @param string $entity_id   Entity id
     *
     * @return bool
     */
    public function exists($company_id, $entity_type, $entity_id)
    {
        return (bool) $this->db->getField(
            'SELECT 1 FROM ?:?p WHERE ?w',
            self::TABLE_NAME,
            [
                'company_id'  => (int) $company_id,
                'entity_type' => (string) $entity_type,
                'entity_id'   => (string) $entity_id
            ]
        );
    }
}
