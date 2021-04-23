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


namespace Tygh\Addons\CommerceML\Storages;


use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\LocalIdDto;
use Tygh\Addons\CommerceML\Dto\ImportDto;
use Tygh\Addons\CommerceML\Dto\ImportItemDto;
use Tygh\Addons\CommerceML\Dto\RepresentSubEntityDto;
use Tygh\Addons\CommerceML\Dto\RepresentEntityDto;
use Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository;
use Tygh\Addons\CommerceML\Repository\ImportEntityRepository;
use Tygh\Addons\CommerceML\Repository\ImportRemovedEntityRepository;
use Tygh\Addons\CommerceML\Repository\ImportRepository;
use Tygh\Addons\CommerceML\Tools\RuntimeCacheStorage;

/**
 * Class ImportStorage
 *
 * @package Tygh\Addons\CommerceML
 *
 * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
 */
class ImportStorage
{
    /**
     * @var \Tygh\Addons\CommerceML\Dto\ImportDto
     */
    private $import;

    /**
     * @var \Tygh\Addons\CommerceML\Repository\ImportRepository
     */
    private $import_repository;

    /**
     * @var \Tygh\Addons\CommerceML\Repository\ImportEntityRepository
     */
    private $import_entity_repository;

    /**
     * @var \Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository
     */
    private $import_entity_map_repository;

    /**
     * @var \Tygh\Addons\CommerceML\Repository\ImportRemovedEntityRepository
     */
    private $import_removed_entity_repository;

    /**
     * @var array<string, string>
     */
    private $mappable_entity_types;

    /**
     * @var array<string, mixed>
     */
    private $settings;

    /**
     * @var \Tygh\Addons\CommerceML\Tools\RuntimeCacheStorage
     */
    private $cache;

    /**
     * ImportStorage constructor.
     *
     * @param \Tygh\Addons\CommerceML\Dto\ImportDto                            $import                           Import dto instance
     * @param \Tygh\Addons\CommerceML\Repository\ImportRepository              $import_repository                Import repository
     * @param \Tygh\Addons\CommerceML\Repository\ImportEntityRepository        $import_entity_repository         Import entity repository
     * @param \Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository     $import_entity_map_repository     Import entity map repository
     * @param \Tygh\Addons\CommerceML\Repository\ImportRemovedEntityRepository $import_removed_entity_repository Import removed entity repository
     * @param array<array-key, string>                                         $mappable_entity_types            List of entity type
     * @param array<string, mixed>                                             $settings                         Import settings
     */
    public function __construct(
        ImportDto $import,
        ImportRepository $import_repository,
        ImportEntityRepository $import_entity_repository,
        ImportEntityMapRepository $import_entity_map_repository,
        ImportRemovedEntityRepository $import_removed_entity_repository,
        array $mappable_entity_types,
        array $settings = []
    ) {
        $this->import = $import;
        $this->import_repository = $import_repository;
        $this->import_entity_repository = $import_entity_repository;
        $this->import_entity_map_repository = $import_entity_map_repository;
        $this->import_removed_entity_repository = $import_removed_entity_repository;
        $this->mappable_entity_types = array_combine($mappable_entity_types, $mappable_entity_types);
        $this->settings = $settings;
        $this->cache = new RuntimeCacheStorage();
    }

    /**
     * Saves import to DB
     */
    public function saveImport()
    {
        $this->import_repository->save($this->import);
    }

    /**
     * Saves entites to DB
     *
     * @param array<\Tygh\Addons\CommerceML\Dto\RepresentEntityDto> $entities EntityDTO instances
     */
    public function saveEntities(array $entities)
    {
        $this->import_entity_repository->batchSave(
            ImportItemDto::createBatchByEntities($entities, $this->import)
        );

        /** @var RepresentEntityDto $entity */
        foreach ($entities as $entity) {
            if (
                isset($this->mappable_entity_types[$entity->getEntityType()])
                && $this->findEntityLocalId($entity->getEntityType(), $entity->getEntityId())->isNullValue()
            ) {
                $this->mapEntityId($entity);
            }

            $key = $this->getEntityCacheKey($entity->getEntityType(), $entity->getEntityId()->getId());

            if (!$this->cache->has($key)) {
                continue;
            }

            $this->cache->add($key, $entity);
        }
    }

    /**
     * Finds entity
     *
     * @param string $entity_type Entity type
     * @param string $entity_id   Entity ID
     *
     * @return \Tygh\Addons\CommerceML\Dto\RepresentEntityDto|null
     */
    public function findEntity($entity_type, $entity_id)
    {
        $key = $this->getEntityCacheKey($entity_type, $entity_id);

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $data = $this->import_entity_repository->findEntityData(
            $this->import->import_id,
            $entity_type,
            $entity_id
        );

        if ($data && $data->entity instanceof RepresentEntityDto) {
            $this->cache->add($key, $data->entity);

            return $data->entity;
        }

        return null;
    }

    /**
     * Finds local ID for entity
     *
     * @param string                            $entity_type   Entity type
     * @param \Tygh\Addons\CommerceML\Dto\IdDto $id_dto        ID instnace
     * @param string|int|null                   $default_value Default value
     *
     * @return \Tygh\Addons\CommerceML\Dto\LocalIdDto
     */
    public function findEntityLocalId($entity_type, IdDto $id_dto, $default_value = null)
    {
        if ($id_dto->hasLocalId()) {
            return LocalIdDto::create($id_dto->local_id);
        }

        $value = $this->import_entity_map_repository->findLocalId(
            $this->import->company_id,
            $entity_type,
            $id_dto->external_id
        );

        if ($value === null) {
            $value = $default_value;
        }

        return LocalIdDto::create($value);
    }

    /**
     * Maps entity ID to local ID by entity instance
     *
     * @param \Tygh\Addons\CommerceML\Dto\RepresentEntityDto $entity Entity DTO
     */
    public function mapEntityId(RepresentEntityDto $entity)
    {
        $this->mapEntityIdByParams(
            $entity->getEntityType(),
            $entity->getEntityId()->external_id,
            $entity->getEntityId()->local_id,
            $entity->getEntityName()
        );

        if (
            $entity instanceof RepresentSubEntityDto
            && !$entity->getEntityId()->local_id
            && $entity->getParentExternalId()
        ) {
            $this->import_entity_map_repository->updateTimestamp(
                $this->import->company_id,
                $entity::getParentEntityType(),
                $entity->getParentExternalId()
            );
        }

        if (!$entity->getEntityId()->local_id) {
            return;
        }

        $this->unmarkEntityAsRemoved(
            $entity->getEntityType(),
            $entity->getEntityId()->external_id
        );
    }

    /**
     * Maps entity ID to local ID by params
     *
     * @param string      $entity_type Entity type
     * @param string      $external_id External ID
     * @param string|int  $local_id    Local ID
     * @param string|null $entity_name Entity name
     */
    public function mapEntityIdByParams($entity_type, $external_id, $local_id, $entity_name = null)
    {
        $this->import_entity_map_repository->add(
            $this->import->company_id,
            $entity_type,
            $external_id,
            $local_id,
            $entity_name
        );
    }

    /**
     * Removes mapping by external ID
     *
     * @param string $entity_type Entity type
     * @param string $external_id External ID
     *
     * @return void
     */
    public function removeMappingByExternalId($entity_type, $external_id)
    {
        $this->import_entity_map_repository->removeByExternalId(
            $entity_type,
            $external_id
        );
    }

    /**
     * Removes entity
     *
     * @param \Tygh\Addons\CommerceML\Dto\RepresentEntityDto $entity Entity DTO
     */
    public function removeEntity(RepresentEntityDto $entity)
    {
        $this->import_entity_repository->remove(
            $this->import->import_id,
            $entity->getEntityType(),
            $entity->getEntityId()->getId()
        );
        $this->cache->remove($this->getEntityCacheKey($entity->getEntityType(), $entity->getEntityId()->getId()));
    }

    /**
     * Removes import with all related records
     */
    public function removeImport()
    {
        $this->import_repository->remove($this->import->import_id);
        $this->removeAllEntites();
    }

    /**
     * Removes all entites
     */
    public function removeAllEntites()
    {
        $this->import_entity_repository->removeByImportId($this->import->import_id);
        $this->cache->clear();
    }

    /**
     * Marks entity as removed
     *
     * @param string $entity_type Entity type
     * @param string $entity_id   Entity ID
     */
    public function markEntityAsRemoved($entity_type, $entity_id)
    {
        $this->import_removed_entity_repository->add($this->import->company_id, $entity_type, $entity_id);
    }

    /**
     * Unmarks entity as removed
     *
     * @param string $entity_type Entity type
     * @param string $entity_id   Entity ID
     */
    public function unmarkEntityAsRemoved($entity_type, $entity_id)
    {
        $this->import_removed_entity_repository->remove($this->import->company_id, $entity_type, $entity_id);
    }

    /**
     * Checks if entity marks as deleted
     *
     * @param string $entity_type Entity type
     * @param string $entity_id   Entity ID
     *
     * @return bool
     */
    public function isEntityMarkedAsRemoved($entity_type, $entity_id)
    {
        return $this->import_removed_entity_repository->exists($this->import->company_id, $entity_type, $entity_id);
    }

    /**
     * Gets import instance
     *
     * @return \Tygh\Addons\CommerceML\Dto\ImportDto
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * Gets queue
     *
     * @param string $entity_type Entity type
     * @param string $process_id  Process ID
     *
     * @return \Generator<int, \Tygh\Addons\CommerceML\Dto\ImportItemDto>
     */
    public function getQueue($entity_type, $process_id)
    {
        while (true) {
            $import_entity = $this->import_entity_repository->findNextRecord(
                $this->import->import_id,
                $entity_type,
                $process_id
            );

            if ($import_entity === null) {
                break;
            }

            yield $import_entity;
        }
    }

    /**
     * Gets setting value
     *
     * @param string $name    Setting name
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public function getSetting($name, $default = null)
    {
        return array_key_exists($name, $this->settings) ? $this->settings[$name] : $default;
    }

    /**
     * Gets entity cache key
     *
     * @param string $entity_type Entity type
     * @param string $entity_id   Entity ID
     *
     * @return string
     */
    private function getEntityCacheKey($entity_type, $entity_id)
    {
        return sprintf('%s_%s', $entity_type, $entity_id);
    }
}
