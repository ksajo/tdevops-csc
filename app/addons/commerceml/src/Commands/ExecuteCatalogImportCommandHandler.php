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
use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Importers\ProductImporter;
use Tygh\Addons\CommerceML\Importers\ProductVariationAsProductImporter;
use Tygh\Addons\CommerceML\Repository\ImportRepository;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Tools\Logger;

/**
 * Class ExecuteImportCommandHandler
 *
 * @package Tygh\Addons\CommerceML\Commands
 */
class ExecuteCatalogImportCommandHandler extends AImportCommandHandler
{
    /**
     * @var \Tygh\Addons\CommerceML\Importers\ProductImporter
     */
    private $product_importer;

    /**
     * @var \Tygh\Addons\CommerceML\Importers\ProductVariationAsProductImporter
     */
    private $product_variations_importer;

    /**
     * ExecuteImportCommandHandler constructor.
     *
     * @param \Tygh\Addons\CommerceML\Repository\ImportRepository                 $import_repository           Import repostiry
     * @param callable                                                            $import_storage_factory      Import storage factory
     * @param \Tygh\Addons\CommerceML\Tools\Logger                                $logger                      Import process logger
     * @param \Tygh\Addons\CommerceML\Importers\ProductImporter                   $product_importer            Product importer instnace
     * @param \Tygh\Addons\CommerceML\Importers\ProductVariationAsProductImporter $product_variations_importer Product importer instnace
     */
    public function __construct(
        ImportRepository $import_repository,
        callable $import_storage_factory,
        Logger $logger,
        ProductImporter $product_importer,
        ProductVariationAsProductImporter $product_variations_importer
    ) {
        parent::__construct($import_repository, $import_storage_factory, $logger);

        $this->product_importer = $product_importer;
        $this->product_variations_importer = $product_variations_importer;
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
        if (!isset($import_item->entity) || !$import_item->entity instanceof ProductDto) {
            return;
        }

        /** @var \Tygh\Addons\CommerceML\Dto\ProductDto $import_item->entity */
        $product_item = $import_item->entity;

        $this->logger->info(__('commerceml.import.message.start_import_product', [
            '[id]' => $product_item->id->getId()
        ]));

        if ($product_item->is_variation) {
            $import_result = $this->product_variations_importer->import($product_item, $import_storage);
        } else {
            $import_result = $this->product_importer->import($product_item, $import_storage);
        }

        $this->logger->logResult($import_result);
        $this->logger->info(__('commerceml.import.message.end_import_product', [
            '[id]' => $product_item->id->getId()
        ]));
    }
}
