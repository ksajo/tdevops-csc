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
use Tygh\Addons\CommerceML\Dto\OrderDto;
use Tygh\Addons\CommerceML\Importers\OrderImporter;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Tools\Logger;
use Tygh\Addons\CommerceML\Repository\ImportRepository;

class ExecuteSaleImportCommandHandler extends AImportCommandHandler
{
    /**
     * @var \Tygh\Addons\CommerceML\Importers\OrderImporter
     */
    private $order_importer;

    /**
     * ExecuteOrdersImportCommandHandler constructor.
     *
     * @param \Tygh\Addons\CommerceML\Repository\ImportRepository $import_repository      Import repostiry
     * @param callable                                            $import_storage_factory Import storage factory
     * @param \Tygh\Addons\CommerceML\Tools\Logger                $logger                 Import process logger
     * @param \Tygh\Addons\CommerceML\Importers\OrderImporter     $order_importer         Order importer
     */
    public function __construct(
        ImportRepository $import_repository,
        callable $import_storage_factory,
        Logger $logger,
        OrderImporter $order_importer
    ) {
        parent::__construct($import_repository, $import_storage_factory, $logger);

        $this->order_importer = $order_importer;
    }

    /**
     * Imports item
     *
     * @param \Tygh\Addons\CommerceML\Dto\ImportItemDto       $import_item    Import item
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage  $import_storage Import storage
     * @param \Tygh\Addons\CommerceML\Commands\AImportCommand $command        Command instance
     */
    protected function importItem(ImportItemDto $import_item, ImportStorage $import_storage, AImportCommand $command)
    {
        if (
            !isset($import_item->entity)
            || !$import_item->entity instanceof OrderDto
            || !$command instanceof ExecuteSaleImportCommand
        ) {
            return;
        }

        /** @var \Tygh\Addons\CommerceML\Dto\OrderDto $import_item->entity */
        $order_id = $import_item->entity->id->getId();

        $this->logger->info(__('commerceml.import.message.start_update_order', [
            '[id]' => $order_id
        ]));

        /** @var \Tygh\Addons\CommerceML\Dto\OrderDto $import_item->entity */
        $import_result = $this->order_importer->import(
            $import_item->entity,
            $import_storage,
            $command->is_change_status_allowed,
            $command->is_edit_order_allowed
        );

        $this->logger->logResult($import_result);
        $this->logger->info(__('commerceml.import.message.end_update_order', [
            '[id]' => $order_id
        ]));
    }
}
