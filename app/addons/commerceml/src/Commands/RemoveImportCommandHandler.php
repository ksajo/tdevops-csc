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


namespace Tygh\Addons\CommerceML\Commands;

use Tygh\Addons\CommerceML\Repository\ImportRepository;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Common\OperationResult;
use Tygh\Enum\SyncDataStatuses;
use Tygh\Exceptions\DeveloperException;

/**
 * Class RemoveImportCommandHandler
 *
 * @package Tygh\Addons\CommerceML\Commands
 */
class RemoveImportCommandHandler
{
    /**
     * @var \Tygh\Addons\CommerceML\Repository\ImportRepository
     */
    private $import_repository;

    /**
     * @var callable
     */
    private $import_storage_factory;

    /**
     * RemoveImportCommandHandler constructor.
     *
     * @param \Tygh\Addons\CommerceML\Repository\ImportRepository $import_repository      Import repostiry
     * @param callable                                            $import_storage_factory Import storage factory
     */
    public function __construct(
        ImportRepository $import_repository,
        callable $import_storage_factory
    ) {
        $this->import_repository = $import_repository;
        $this->import_storage_factory = $import_storage_factory;
    }

    /**
     * @param \Tygh\Addons\CommerceML\Commands\RemoveImportCommand $command Command instance
     *
     * @return \Tygh\Common\OperationResult
     */
    public function handle(RemoveImportCommand $command)
    {
        $import = $this->import_repository->findById($command->getImportId());

        if (!$import) {
            $result = new OperationResult(false);
            $result->addError('import', 'Import not found');
            return $result;
        }

        /** @var \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage */
        $import_storage = call_user_func($this->import_storage_factory, $import);

        if (!$import_storage instanceof ImportStorage) {
            throw new DeveloperException();
        }

        $import = $import_storage->getImport();
        $import->status = SyncDataStatuses::STATUS_SUCCESS;

        $result = new OperationResult(true);
        $result->setData($import, 'import');

        $import_storage->removeImport();

        return $result;
    }
}
