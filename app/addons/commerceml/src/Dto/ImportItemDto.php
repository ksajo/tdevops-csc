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

use Tygh\Enum\ObjectStatuses;

/**
 * Class ImportItemDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class ImportItemDto
{
    /**
     * @var int
     */
    public $import_id;

    /**
     * @var string
     */
    public $entity_id;

    /**
     * @var string
     */
    public $entity_type;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\RepresentEntityDto|null
     */
    public $entity;

    /**
     * @var string
     */
    public $status;

    /**
     * @var int
     */
    public $created_at;

    /**
     * @var int
     */
    public $updated_at;

    /**
     * Converts object to array
     *
     * @return array{import_id: int, entity_id: string, entity_type: string, entity: string, status: string, created_at: int, updated_at: int}
     */
    public function toArray()
    {
        return [
            'import_id'   => $this->import_id,
            'entity_id'   => $this->entity_id,
            'entity_type' => $this->entity_type,
            'entity'      => serialize($this->entity),
            'status'      => $this->status,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }

    /**
     * Creates ImportEntityDto instance from array
     *
     * @param array{import_id: int, entity_id: string, entity_type: string, status?: string, created_at?: int, updated_at?: int, entity: string|RepresentEntityDto} $data Data
     *
     * @return \Tygh\Addons\CommerceML\Dto\ImportItemDto
     */
    public static function fromArray(array $data)
    {
        $self = new self();

        $self->import_id = isset($data['import_id']) ? (int) $data['import_id'] : 0;
        $self->entity_id = isset($data['entity_id']) ? (string) $data['entity_id'] : '';
        $self->entity_type = isset($data['entity_type']) ? (string) $data['entity_type'] : '';
        $self->status = isset($data['status']) ? (string) $data['status'] : '';
        $self->created_at = isset($data['created_at']) ? (int) $data['created_at'] : time();
        $self->updated_at = isset($data['updated_at']) ? (int) $data['updated_at'] : time();

        if (isset($data['entity']) && is_string($data['entity'])) {
            $self->entity = unserialize($data['entity']);
        } elseif (isset($data['entity']) && $data['entity'] instanceof RepresentEntityDto) {
            $self->entity = $data['entity'];
        }

        return $self;
    }

    /**
     * Create ImportEntityDto instance by EntityDTO and ImportDTO instances
     *
     * @param \Tygh\Addons\CommerceML\Dto\RepresentEntityDto $entity EntityDTO instance
     * @param \Tygh\Addons\CommerceML\Dto\ImportDto          $import ImportDTO instance
     *
     * @return \Tygh\Addons\CommerceML\Dto\ImportItemDto
     */
    public static function fromEntityAndImport(RepresentEntityDto $entity, ImportDto $import)
    {
        return self::fromArray([
            'import_id'   => $import->import_id,
            'entity_id'   => $entity->getEntityId()->getId(),
            'entity_type' => $entity->getEntityType(),
            'status'      => ObjectStatuses::PENDING,
            'entity'      => $entity
        ]);
    }

    /**
     * Create list of ImportEntityDto instance by EntityDTO and ImportDTO instances
     *
     * @param array<\Tygh\Addons\CommerceML\Dto\RepresentEntityDto> $entities EntityDTO instances
     * @param \Tygh\Addons\CommerceML\Dto\ImportDto                 $import   ImportDTO instance
     *
     * @return array<\Tygh\Addons\CommerceML\Dto\ImportItemDto>
     */
    public static function createBatchByEntities(array $entities, ImportDto $import)
    {
        return array_map(static function (RepresentEntityDto $entity) use ($import) {
            return self::fromEntityAndImport($entity, $import);
        }, $entities);
    }
}
