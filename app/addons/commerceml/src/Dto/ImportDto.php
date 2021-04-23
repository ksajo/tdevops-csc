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


namespace Tygh\Addons\CommerceML\Dto;

use Tygh\Enum\YesNo;
use Tygh\Enum\SyncDataStatuses;

/**
 * Class ImportDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class ImportDto
{
    /**
     * Orders import
     */
    const IMPORT_TYPE_ORDERS = 'sale';

    /**
     * Products import
     */
    const IMPORT_TYPE_CATALOG = 'catalog';

    /**
     * @var int
     */
    public $import_id;

    /**
     * @var string
     */
    public $import_key;

    /**
     * @var int
     */
    public $company_id;

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $type;

    /**
     * @var bool
     */
    public $has_only_changes = false;

    /**
     * @var int
     */
    public $created_at;

    /**
     * @var int
     */
    public $updated_at;

    /**
     * @return bool
     */
    public function isStatusFinished()
    {
        return in_array($this->status, [SyncDataStatuses::STATUS_SUCCESS, SyncDataStatuses::STATUS_UNSUCCESS], true);
    }

    /**
     * Converts object to array
     *
     * @return array{import_id: int, import_key: string, company_id: int, user_id: int, status: string, has_only_changes: string, created_at: int, updated_at: int}
     */
    public function toArray()
    {
        return [
            'import_id'        => $this->import_id,
            'import_key'       => $this->import_key,
            'company_id'       => $this->company_id,
            'user_id'          => $this->user_id,
            'status'           => $this->status,
            'type'             => $this->type,
            'has_only_changes' => YesNo::toId($this->has_only_changes),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }

    /**
     * Creates self instance from array
     *
     * @param array<string, string|int|null> $data Data
     *
     * @return \Tygh\Addons\CommerceML\Dto\ImportDto
     */
    public static function fromArray(array $data)
    {
        $self = new self();

        $self->import_id = isset($data['import_id']) ? (int) $data['import_id'] : 0;
        $self->import_key = isset($data['import_key']) ? (string) $data['import_key'] : '';
        $self->company_id = isset($data['company_id']) ? (int) $data['company_id'] : 0;
        $self->user_id = isset($data['user_id']) ? (int) $data['user_id'] : 0;
        $self->status = isset($data['status']) ? (string) $data['status'] : '';
        $self->type = isset($data['type']) ? (string) $data['type'] : '';
        $self->has_only_changes = isset($data['has_only_changes']) ? YesNo::toBool($data['has_only_changes']) : false;
        $self->created_at = isset($data['created_at']) ? (int) $data['created_at'] : time();
        $self->updated_at = isset($data['updated_at']) ? (int) $data['updated_at'] : time();

        return $self;
    }
}
