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


use Tygh\Addons\CommerceML\Dto\ImportItemDto;
use Tygh\Addons\CommerceML\Repository\ImportRepository;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Tools\Logger;
use Tygh\Common\OperationResult;
use Tygh\Enum\SyncDataStatuses;
use Tygh\Exceptions\DeveloperException;

abstract class AImportCommandHandler
{
    /**
     * @var \Tygh\Addons\CommerceML\Repository\ImportRepository
     */
    protected $import_repository;

    /**
     * @var callable
     */
    protected $import_storage_factory;

    /**
     * @var \Tygh\Addons\CommerceML\Tools\Logger
     */
    protected $logger;

    /**
     * AImportCommandHandler constructor.
     *
     * @param \Tygh\Addons\CommerceML\Repository\ImportRepository $import_repository      Import repository
     * @param callable                                            $import_storage_factory Import storage factory
     * @param \Tygh\Addons\CommerceML\Tools\Logger                $logger                 Logger instance
     */
    public function __construct(
        ImportRepository $import_repository,
        callable $import_storage_factory,
        Logger $logger
    ) {
        $this->import_repository = $import_repository;
        $this->import_storage_factory = $import_storage_factory;
        $this->logger = $logger;
    }

    /**
     * Executes importing
     *
     * @param \Tygh\Addons\CommerceML\Commands\AImportCommand $command Command instance
     *
     * @return \Tygh\Common\OperationResult
     */
    public function handle(AImportCommand $command)
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

        $this->setImportStatusToInProgress($import_storage);

        $result = new OperationResult(true);
        $result->setData($import_storage->getImport(), 'import');

        $time_start = time();

        foreach ($import_storage->getQueue($command->getEntityType(), $this->getProcessId()) as $import_item) {
            $this->importItem($import_item, $import_storage, $command);

            if ($command->hasTimeLimit() && $command->getTimeLimit() < time() - $time_start) {
                return $result;
            }
        }

        $this->setImportStatusToSucessfullyFinished($import_storage);

        $import_storage->removeAllEntites();

        return $result;
    }

    /**
     * Imports item
     *
     * @param \Tygh\Addons\CommerceML\Dto\ImportItemDto       $import_item    Import item
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage  $import_storage Import storage
     * @param \Tygh\Addons\CommerceML\Commands\AImportCommand $command        Command instance
     *
     * @return void
     */
    abstract protected function importItem(ImportItemDto $import_item, ImportStorage $import_storage, AImportCommand $command);

    /**
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     */
    protected function setImportStatusToSucessfullyFinished(ImportStorage $import_storage)
    {
        $import_storage->getImport()->status = SyncDataStatuses::STATUS_SUCCESS;
        $import_storage->saveImport();
    }

    /**
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     */
    protected function setImportStatusToInProgress(ImportStorage $import_storage)
    {
        $import_storage->getImport()->status = SyncDataStatuses::STATUS_PROGRESS;
        $import_storage->saveImport();
    }

    /**
     * Gets process ID
     *
     * @return string
     */
    protected function getProcessId()
    {
        return uniqid('', true);
    }
}
