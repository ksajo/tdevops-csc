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

use Tygh\Addons\CommerceML\Commands\RemoveImportCommand;
use Tygh\Addons\CommerceML\Commands\CleanUpFilesDirCommand;
use Tygh\Addons\CommerceML\Commands\CreateImportCommand;
use Tygh\Addons\CommerceML\Commands\AuthCommand;
use Tygh\Addons\CommerceML\ServiceProvider;
use Tygh\Addons\CommerceML\Commands\UploadImportFileCommand;
use Tygh\Addons\CommerceML\Commands\UnzipImportFileCommand;
use Tygh\Addons\CommerceML\Commands\ExecuteCatalogImportCommand;
use Tygh\Addons\CommerceML\Commands\ExportOrdersCommand;
use Tygh\Addons\CommerceML\Commands\ExecuteSaleImportCommand;

defined('BOOTSTRAP') or die('Access denied');

/**
 * @var array<string, array{middleware: array<callable>, handler: callable}> $schema Declares command handlers
 */
$schema = [
    CreateImportCommand::class => [
        'middleware' => [],
        'handler'    => static function (CreateImportCommand $command) {
            return ServiceProvider::getCreateImportCommandHandler()->handle($command);
        }
    ],
    AuthCommand::class => [
        'middleware' => [],
        'handler'    => static function (AuthCommand $command) {
            return ServiceProvider::getAuthCommandHandler()->handle($command);
        }
    ],
    UploadImportFileCommand::class => [
        'middleware' => [],
        'handler'    => static function (UploadImportFileCommand $command) {
            return ServiceProvider::getUploadImportFileCommandHandler()->handle($command);
        }
    ],
    UnzipImportFileCommand::class => [
        'middleware' => [],
        'handler'    => static function (UnzipImportFileCommand $command) {
            return ServiceProvider::getUnzipImportFileCommandHandler()->handle($command);
        }
    ],
    ExecuteCatalogImportCommand::class => [
        'middleware' => [],
        'handler'    => static function (ExecuteCatalogImportCommand $command) {
            return ServiceProvider::getExecuteImportCommandHandler()->handle($command);
        }
    ],
    RemoveImportCommand::class => [
        'middleware' => [],
        'handler'    => static function (RemoveImportCommand $command) {
            return ServiceProvider::getRemoveImportCommandHandler()->handle($command);
        }
    ],
    CleanUpFilesDirCommand::class => [
        'middleware' => [],
        'handler'    => static function (CleanUpFilesDirCommand $command) {
            return ServiceProvider::getCleanUpFilesDirCommandHandler()->handle($command);
        }
    ],
    ExportOrdersCommand::class => [
        'middleware' => [],
        'handler'    => static function (ExportOrdersCommand $command) {
            return ServiceProvider::getExportOrderCommandHandler()->handle($command);
        }
    ],
    ExecuteSaleImportCommand::class => [
        'middleware' => [],
        'handler'    => static function (ExecuteSaleImportCommand $command) {
            return ServiceProvider::getExecuteSaleImportCommandHandler()->handle($command);
        }
    ]
];

return $schema;
