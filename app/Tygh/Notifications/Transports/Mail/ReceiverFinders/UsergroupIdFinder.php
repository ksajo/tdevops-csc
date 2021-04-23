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

namespace Tygh\Notifications\Transports\Mail\ReceiverFinders;

use Tygh\Database\Connection;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\UserTypes;
use Tygh\Notifications\Transports\BaseMessageSchema;

class UsergroupIdFinder implements ReceiverFinderInterface
{
    /**
     * @var \Tygh\Database\Connection
     */
    protected $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function find($criterion, BaseMessageSchema $message_schema)
    {
        $conditions = [
            'users.status'            => ObjectStatuses::ACTIVE,
            'usergroups.status'       => ObjectStatuses::ACTIVE,
            'usergroups.usergroup_id' => (int) $criterion,
        ];

        if ($message_schema->company_id) {
            $conditions['users.company_id'] = [0, (int) $message_schema->company_id];
        }

        return $this->db->getColumn(
            'SELECT users.email'
            . ' FROM ?:users AS users'
            . ' LEFT JOIN ?:usergroup_links AS usergroups ON usergroups.user_id = users.user_id'
            . ' WHERE ?w'
            . ' GROUP BY users.user_id',
            $conditions
        );
    }
}
