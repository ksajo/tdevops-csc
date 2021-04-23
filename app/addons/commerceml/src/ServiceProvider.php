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


namespace Tygh\Addons\CommerceML;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Addons\CommerceML\Commands\ExportOrdersCommandHandler;
use Tygh\Addons\CommerceML\Commands\RemoveImportCommandHandler;
use Tygh\Addons\CommerceML\Commands\AuthCommandHandler;
use Tygh\Addons\CommerceML\Commands\CleanUpFilesDirCommandHandler;
use Tygh\Addons\CommerceML\Commands\CreateImportCommandHandler;
use Tygh\Addons\CommerceML\Commands\ExecuteCatalogImportCommandHandler;
use Tygh\Addons\CommerceML\Commands\UnzipImportFileCommandHandler;
use Tygh\Addons\CommerceML\Commands\UploadImportFileCommandHandler;
use Tygh\Addons\CommerceML\Commands\ExecuteSaleImportCommandHandler;
use Tygh\Addons\CommerceML\Convertors\CategoryConvertor;
use Tygh\Addons\CommerceML\Convertors\OrderConvertor;
use Tygh\Addons\CommerceML\Convertors\PriceTypeConvertor;
use Tygh\Addons\CommerceML\Convertors\ProductConvertor;
use Tygh\Addons\CommerceML\Convertors\ProductFeatureConvertor;
use Tygh\Addons\CommerceML\Dto\ImportDto;
use Tygh\Addons\CommerceML\Dto\PriceTypeDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Formators\OrderFormator;
use Tygh\Addons\CommerceML\HookHandlers\FeedbackHookHandler;
use Tygh\Addons\CommerceML\Importers\CategoryImporter;
use Tygh\Addons\CommerceML\Importers\OrderImporter;
use Tygh\Addons\CommerceML\Importers\ProductFeatureImporter;
use Tygh\Addons\CommerceML\Importers\ProductImporter;
use Tygh\Addons\CommerceML\Importers\ProductVariationAsProductImporter;
use Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository;
use Tygh\Addons\CommerceML\Repository\ImportEntityRepository;
use Tygh\Addons\CommerceML\Repository\ImportRemovedEntityRepository;
use Tygh\Addons\CommerceML\Repository\ImportRepository;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Storages\OrderStorage;
use Tygh\Addons\CommerceML\Storages\ProductStorage;
use Tygh\Addons\CommerceML\Tools\Logger;
use Tygh\Addons\CommerceML\Xml\XmlParser;
use Tygh\Addons\ProductVariations\ServiceProvider as VariationsServiceProvider;
use Tygh\Bootstrap as CoreBootstrap;
use Tygh\Enum\YesNo;
use Tygh\Tygh;
use Tygh\Registry;

/**
 * Class ServiceProvider is intended to register services and components of the "CommerceML" add-on to the application
 * container.
 *
 * @package Tygh\Addons\CommerceML
 *
 * @phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        $app['addons.commerceml.repository.import_repository'] = static function (Container $app) {
            return new ImportRepository($app['db']);
        };

        $app['addons.commerceml.repository.import_entity_repository'] = static function (Container $app) {
            return new ImportEntityRepository($app['db']);
        };

        $app['addons.commerceml.repository.import_entity_map_repository'] = static function (Container $app) {
            return new ImportEntityMapRepository($app['db']);
        };

        $app['addons.commerceml.repository.import_removed_entity_repository'] = static function (Container $app) {
            return new ImportRemovedEntityRepository($app['db']);
        };

        $app['addons.commerceml.convertors.product_convertor'] = static function () {
            return new ProductConvertor(
                TranslatableValueDto::create(__('commerceml.variation_default_product_feature_name')),
                TranslatableValueDto::create(__('commerceml.brand_manufacturer_default_product_feature_name')),
                ProductFeatureDto::BRAND_EXTERNAL_ID
            );
        };

        $app['addons.commerceml.storages.product_storage'] = static function () {
            return new ProductStorage(self::getDefaultSyncLanguage());
        };

        $app['addons.commerceml.storages.order_storage'] = static function () {
            return new OrderStorage(self::getDefaultSyncLanguage());
        };

        $app['addons.commerceml.convertors.product_feature_convertor'] = static function () {
            return new ProductFeatureConvertor();
        };

        $app['addons.commerceml.convertors.price_type_convertor'] = static function () {
            return new PriceTypeConvertor();
        };

        $app['addons.commerceml.convertors.category_confertor'] = static function () {
            return new CategoryConvertor(self::getProductFeatureConvertor());
        };

        $app['addons.commerceml.importers.category_importer'] = static function (Container $app) {
            return new CategoryImporter($app['addons.commerceml.storages.product_storage']);
        };

        $app['addons.commerceml.convertors.order_formator'] = static function (Container $app) {
            return new OrderFormator(
                $app['addons.commerceml.storages.order_storage'],
                self::getImportEntityMapRepository(),
                self::getImportSettings(),
                Registry::get('currencies'),
                CART_PRIMARY_CURRENCY,
                SHIPPING_ADDRESS_PREFIX,
                BILLING_ADDRESS_PREFIX
            );
        };

        $app['addons.commerceml.convertors.order_convertor'] = static function () {
            return new OrderConvertor();
        };


        $app['addons.commerceml.importers.product_feature_importer'] = static function (Container $app) {
            return new ProductFeatureImporter($app['addons.commerceml.storages.product_storage']);
        };

        $app['addons.commerceml.importers.product_importer'] = static function (Container $app) {
            return new ProductImporter(
                $app['addons.commerceml.importers.category_importer'],
                $app['addons.commerceml.importers.product_feature_importer'],
                $app['addons.commerceml.storages.product_storage']
            );
        };

        $app['addons.commerceml.importers.product_variation_importer'] = static function (Container $app) {
            return new ProductVariationAsProductImporter(
                $app['addons.commerceml.importers.product_importer'],
                $app['addons.commerceml.importers.product_feature_importer'],
                VariationsServiceProvider::getGroupRepository(),
                VariationsServiceProvider::getService(),
                $app['addons.commerceml.storages.product_storage']
            );
        };

        $app['addons.commerceml.importers.orders_importer'] = static function (Container $app) {
            return new OrderImporter(
                $app['addons.commerceml.storages.order_storage']
            );
        };

        $app['addons.commerceml.xml.xml_parser'] = static function () {
            return new XmlParser();
        };

        $app['addons.commerceml.import_storage_factory'] = static function () {
            return static function (ImportDto $import) {
                return new ImportStorage(
                    $import,
                    self::getImportRepository(),
                    self::getImportEntityRepository(),
                    self::getImportEntityMapRepository(),
                    self::getImportRemovedEntityRepository(),
                    array_keys(self::getManualMappableEntitiesSchema()),
                    self::getImportSettings($import->company_id)
                );
            };
        };

        $app['addons.commerceml.xml_parser_callbacks_factory'] = static function () {
            return static function ($type) {
                return $type === ImportDto::IMPORT_TYPE_CATALOG
                    ? (array) fn_get_schema('cml', 'callbacks_catalog')
                    : (array) fn_get_schema('cml', 'callbacks_sale');
            };
        };

        $app['addons.commerceml.command_bus'] = static function () {
            return new CommandBus((array) fn_get_schema('cml', 'commands'));
        };

        $app['addons.commerceml.commands.create_import_command_handler'] = static function () {
            return new CreateImportCommandHandler(
                self::getImportStorageFactory(),
                self::getXmlParser(),
                self::getXmlParserCallbacksFactory()
            );
        };

        $app['addons.commerceml.commands.auth_command_handler'] = static function (Container $app) {
            return new AuthCommandHandler(
                fn_get_runtime_company_id(),
                fn_allowed_for('ULTIMATE')
            );
        };

        $app['addons.commerceml.commands.upload_import_file_command_handler'] = static function () {
            return new UploadImportFileCommandHandler();
        };

        $app['addons.commerceml.commands.unzip_import_file_command_handler'] = static function (Container $app) {
            return new UnzipImportFileCommandHandler(
                $app['archiver']
            );
        };

        $app['addons.commerceml.commands.execute_import_command_handler'] = static function (Container $app) {
            return new ExecuteCatalogImportCommandHandler(
                self::getImportRepository(),
                self::getImportStorageFactory(),
                self::getLogger(),
                $app['addons.commerceml.importers.product_importer'],
                $app['addons.commerceml.importers.product_variation_importer']
            );
        };

        $app['addons.commerceml.commands.remove_import_command_handler'] = static function () {
            return new RemoveImportCommandHandler(
                self::getImportRepository(),
                self::getImportStorageFactory()
            );
        };

        $app['addons.commerceml.commands.clean_up_files_dir_command_handler'] = static function () {
            return new CleanUpFilesDirCommandHandler();
        };

        $app['addons.commerceml.commands.export_orders_command_handler'] = static function (Container $app) {
            return new ExportOrdersCommandHandler(
                new \XMLWriter(),
                $app['addons.commerceml.storages.order_storage'],
                self::getOrderFormator(),
                self::getOrdersStatusesToExport(),
                self::getImportSettings()
            );
        };

        $app['addons.commerceml.commands.execute_import_sale_command_handler'] = static function (Container $app) {
            return new ExecuteSaleImportCommandHandler(
                self::getImportRepository(),
                self::getImportStorageFactory(),
                self::getLogger(),
                $app['addons.commerceml.importers.orders_importer']
            );
        };

        $app['addons.commerceml.hook_handlers.feedback'] = static function () {
            return new FeedbackHookHandler();
        };

        $app['addons.commerceml.log_file'] = 'commerceml.log';
        $app['addons.commerceml.log_file_path'] = static function () {
            return sprintf('%s/exim/commerceml.log', rtrim(fn_get_files_dir_path(), '/'));
        };

        $app['addons.commerceml.logger'] = static function () use ($app) {
            return new Logger(
                $app['addons.commerceml.log_file_path'],
                DEFAULT_FILE_PERMISSIONS,
                DEFAULT_DIR_PERMISSIONS,
                Registry::ifGet('config.commerceml.max_log_file_size', 10240), //10mb
                Registry::ifGet('config.commerceml.max_log_files', 10)
            );
        };
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportRepository
     */
    public static function getImportRepository()
    {
        return Tygh::$app['addons.commerceml.repository.import_repository'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportEntityRepository
     */
    public static function getImportEntityRepository()
    {
        return Tygh::$app['addons.commerceml.repository.import_entity_repository'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository
     */
    public static function getImportEntityMapRepository()
    {
        return Tygh::$app['addons.commerceml.repository.import_entity_map_repository'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportRemovedEntityRepository
     */
    public static function getImportRemovedEntityRepository()
    {
        return Tygh::$app['addons.commerceml.repository.import_removed_entity_repository'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Convertors\ProductConvertor
     */
    public static function getProductConvetor()
    {
        return Tygh::$app['addons.commerceml.convertors.product_convertor'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Convertors\ProductFeatureConvertor
     */
    public static function getProductFeatureConvertor()
    {
        return Tygh::$app['addons.commerceml.convertors.product_feature_convertor'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Convertors\PriceTypeConvertor
     */
    public static function getPriceTypeConvertor()
    {
        return Tygh::$app['addons.commerceml.convertors.price_type_convertor'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Convertors\CategoryConvertor
     */
    public static function getCategoryConvertor()
    {
        return Tygh::$app['addons.commerceml.convertors.category_confertor'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Formators\OrderFormator
     */
    public static function getOrderFormator()
    {
        return Tygh::$app['addons.commerceml.convertors.order_formator'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Convertors\OrderConvertor
     */
    public static function getOrderConvertor()
    {
        return Tygh::$app['addons.commerceml.convertors.order_convertor'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Xml\XmlParser
     */
    public static function getXmlParser()
    {
        return Tygh::$app['addons.commerceml.xml.xml_parser'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\CommandBus
     */
    public static function getCommandBus()
    {
        return Tygh::$app['addons.commerceml.command_bus'];
    }

    /**
     * @return callable
     */
    public static function getImportStorageFactory()
    {
        return Tygh::$app['addons.commerceml.import_storage_factory'];
    }

    /**
     * @return callable
     */
    public static function getXmlParserCallbacksFactory()
    {
        return Tygh::$app['addons.commerceml.xml_parser_callbacks_factory'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\CreateImportCommandHandler
     */
    public static function getCreateImportCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.create_import_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\AuthCommandHandler
     */
    public static function getAuthCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.auth_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\UploadImportFileCommandHandler
     */
    public static function getUploadImportFileCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.upload_import_file_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\UnzipImportFileCommandHandler
     */
    public static function getUnzipImportFileCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.unzip_import_file_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\ExecuteCatalogImportCommandHandler
     */
    public static function getExecuteImportCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.execute_import_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\RemoveImportCommandHandler
     */
    public static function getRemoveImportCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.remove_import_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\CleanUpFilesDirCommandHandler
     */
    public static function getCleanUpFilesDirCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.clean_up_files_dir_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\ExportOrdersCommandHandler
     */
    public static function getExportOrderCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.export_orders_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\ExecuteSaleImportCommandHandler
     */
    public static function getExecuteSaleImportCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.execute_import_sale_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Tools\Logger
     */
    public static function getLogger()
    {
        return Tygh::$app['addons.commerceml.logger'];
    }

    /**
     * Gets schema for manual mappable entities
     *
     * @return array<string, array{items_provider: callable}>
     */
    public static function getManualMappableEntitiesSchema()
    {
        return (array) fn_get_schema('cml', 'mappable');
    }

    /**
     * Checks if company requires to manual mapping entities
     *
     * @param int $company_id Company ID
     *
     * @return bool
     */
    public static function isManualMappingRequired($company_id)
    {
        $map_repository = self::getImportEntityMapRepository();

        list($records) = $map_repository->findAll([
            'company_id'   => $company_id,
            'entity_type'  => PriceTypeDto::REPRESENT_ENTITY_TYPE,
            'has_local_id' => true,
            'limit'        => 1
        ]);

        return empty($records);
    }

    /**
     * Checks if catalog import enabled
     *
     * @param int|null $company_id Company ID
     *
     * @return bool
     */
    public static function isCatalogImportEnabled($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        $settings = self::getImportSettings($company_id);

        return isset($settings['catalog_importer.import_mode']) ? ($settings['catalog_importer.import_mode'] !== 'none') : true;
    }

    /**
     * Checks if offers import enabled
     *
     * @param int|null $company_id Company ID
     *
     * @return bool
     */
    public static function isOffersImportEnabled($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        $settings = self::getImportSettings($company_id);

        /** @var bool $settings['allow_import_offers'] */
        return isset($settings['catalog_importer.allow_import_offers']) ? (bool) $settings['catalog_importer.allow_import_offers'] : true;
    }

    /**
     * Checks if import changes to order is enabled
     *
     * @param int|null $company_id Company ID
     *
     * @return bool
     */
    public static function isImportChangesToOrderEnabled($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        $settings = self::getImportSettings($company_id);

        /** @var bool $settings['allow_import_offers'] */
        return isset($settings['orders_importer.import_changes']) ? (bool) $settings['orders_importer.import_changes'] : true;
    }

    /**
     * Checks if export orders statuses enabled
     *
     * @param int|null $company_id Company ID
     *
     * @return bool
     */
    public static function isExportOrdersStatusesEnabled($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        $settings = self::getImportSettings($company_id);

        /** @var bool $settings['orders_exporter.export_order_statuses'] */
        return isset($settings['orders_exporter.export_order_statuses']) ? (bool) $settings['orders_exporter.export_order_statuses'] : true;
    }

    /**
     * Checks if manage categories allowed
     *
     * @param int|null $company_id Company ID
     *
     * @return bool
     */
    public static function isCategoriesManageAllowed($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        if (!fn_allowed_for('MULTIVENDOR')) {
            return true;
        }

        return empty($company_id);
    }

    /**
     * Checks if manage product features allowed
     *
     * @param int|null $company_id Company ID
     *
     * @return bool
     */
    public static function isProductFeaturesManageAllowed($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        if (!fn_allowed_for('MULTIVENDOR') || YesNo::toBool(Registry::get('settings.Vendors.allow_vendor_manage_features'))) {
            return true;
        }

        return empty($company_id);
    }

    /**
     * Gets default sync language
     *
     * @param int|null $company_id Company ID
     *
     * @return string
     */
    public static function getDefaultSyncLanguage($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        $settings = self::getImportSettings($company_id);

        /** @var string $settings['default_lang'] */
        return isset($settings['default_lang']) ? (string) $settings['default_lang'] : Registry::get('settings.Appearance.backend_default_language');
    }

    /**
     * Gets orders statuses to export
     *
     * @param int|null $company_id Company ID
     *
     * @return array<string>
     */
    public static function getOrdersStatusesToExport($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        $settings = self::getImportSettings($company_id);

        if (empty($settings['orders_exporter.statuses_filter'])) {
            return [];
        }

        $statuses = $settings['orders_exporter.statuses_filter'];

        if (isset($statuses['all'])) {
            unset($statuses['all']);
        }

        if (empty($statuses) || $statuses === 'all') {
            return [];
        }

        return (array) $statuses;
    }

    /**
     * Gets catalog settings
     *
     * @param int|null $company_id Company ID
     *
     * @return array<string, int|string|bool|array>
     */
    public static function getImportSettings($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        $settings = fn_get_sync_data_settings('commerceml', $company_id);
        $result = self::normalizeSettings($company_id, $settings);

        if (!fn_category_exists((int) $result['catalog_importer.default_category_id'])) {
            $default_category_company_id = fn_allowed_for('MULTIVENDOR') ? 0 : $company_id;
            $result['catalog_importer.default_category_id'] = fn_get_or_create_default_category_id($default_category_company_id);
        }

        $result['lang_codes'] = array_keys(Tygh::$app['languages']);
        $result['upload_dir_path'] = self::getUploadDirPath();
        $result['allow_negative_amount'] = YesNo::toBool(Registry::get('settings.General.allow_negative_amount'));
        $result['allow_manage_categories'] = self::isCategoriesManageAllowed($company_id);
        $result['allow_manage_features'] = self::isProductFeaturesManageAllowed($company_id);

        return $result;
    }

    /**
     * Gets upload directory path
     *
     * @param int|null $company_id Company identifier
     *
     * @return string
     */
    public static function getUploadDirPath($company_id = null)
    {
        return sprintf('%s/exim/1C/', rtrim(fn_get_files_dir_path($company_id), '/'));
    }

    /**
     * Gets file limit for uploading
     *
     * @return int
     */
    public static function getUploadFileLimit()
    {
        $upload_max_filesize = CoreBootstrap::getIniParam('upload_max_filesize', true);
        $post_max_size = CoreBootstrap::getIniParam('post_max_size', true);

        $file_limit = min(
            fn_return_bytes($upload_max_filesize),
            fn_return_bytes($post_max_size)
        );

        $config_file_limit = Registry::ifGet('config.commerceml.file_limit', false);

        $file_limit = empty($config_file_limit)
            ? $file_limit
            : min(fn_return_bytes($config_file_limit), $file_limit);

        return $file_limit;
    }

    /**
     * Checks if zip allowed
     *
     * @return bool
     */
    public static function isZipAllowed()
    {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        return YesNo::toBool(Registry::ifGet('config.commerceml.allow_zip', true));
    }

    /**
     * Gets settings schema
     *
     * @param int|null $company_id Company identifier
     *
     * @return array<string, array{
     *    type: string,
     *    variants?: array<string>,
     *    variants_labels?: array<string, string>,
     *    default: string|int|string[]|null,
     *    editable: bool,
     *    value?: callable
     * }>
     *
     * @phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
     */
    public static function getImportSettingsSchema($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        $schema = fn_get_schema('cml', 'settings');

        foreach ($schema as &$item) {
            if (isset($item['variants']) && is_callable($item['variants'])) {
                $item['variants'] = call_user_func($item['variants'], $company_id);
            }

            if (isset($item['variants_labels']) && is_callable($item['variants_labels'])) {
                $item['variants_labels'] = call_user_func($item['variants_labels'], $company_id);
            }

            if (isset($item['default']) && is_callable($item['default'])) {
                $item['default'] = call_user_func($item['default'], $company_id);
            }

            if (isset($item['editable']) && is_callable($item['editable'])) {
                $item['editable'] = call_user_func($item['editable'], $company_id);
            }

            if (!isset($item['default']) && !empty($item['variants'])) {
                $item['default'] = reset($item['variants']);
            }

            if (!isset($item['editable'])) {
                $item['editable'] = true;
            }

            if (!isset($item['default'])) {
                $item['default'] = null;
            }
        }
        unset($item);

        return $schema;
    }

    /**
     * Normalizes settings
     *
     * @param int|null              $company_id Company identifier
     * @param array<string, string> $settings   Settings
     *
     * @return array<string, array<array-key, string>|string|bool>
     */
    public static function normalizeSettings($company_id = null, array $settings = [])
    {
        $result = [];

        /** @var array<string, array<string, array<array-key, string>|string|bool>> $schema */
        $schema = self::getImportSettingsSchema($company_id);

        foreach ($schema as $key => $item) {
            $result[$key] = $item['default'];
        }

        foreach ($settings as $key => $value) {
            if (!isset($schema[$key])) {
                $result[$key] = $value;
                continue;
            }

            if (!$schema[$key]['editable']) {
                $result[$key] = $schema[$key]['default'];
                continue;
            }

            $value = self::normalizeValue($schema[$key], $value);

            $result[$key] = $value;
        }

        foreach ($schema as $key => $item) {
            if (!isset($item['value']) || !is_callable($item['value'])) {
                continue;
            }
            $result[$key] = call_user_func($item['value'], $company_id, $result);
        }

        return $result;
    }

    /**
     * Normalize value
     *
     * phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
     *
     * @param array<string, mixed> $schema CML setting schema
     * @param mixed                $value  Raw setting value
     *
     * @return mixed
     */
    public static function normalizeValue(array $schema, $value)
    {
        switch ($schema['type']) {
            case 'bool':
                $value = is_bool($value) ? $value : YesNo::toBool((string) $value);
                break;
            case 'string[]':
                $value = is_array($value) ? $value : fn_explode("\n", (string) $value);
                $value = array_filter($value);
                break;
            case 'enum':
                $value = isset($schema['variants']) && in_array($value, $schema['variants'], true)
                    ? $value
                    : $schema['default'];
                break;
            case 'int':
                $value = (int) $value;
                break;
            case 'string':
            default:
                $value = trim((string) $value);
                break;
        }
        return $value;
    }

    /**
     * Gets last synchronization info
     *
     * @param array<string, string|int> $params Params
     *
     * @return array{status: string, last_sync_timestamp: int, log_file_url: string, status_code?: string}
     */
    public static function getLastSyncInfo($params = [])
    {
        $company_id = empty($params['company_id']) ? fn_get_runtime_company_id() : $params['company_id'];

        $result = [
            'last_sync_timestamp' => 0,
            'status'              => '',
            'log_file_url'        => '',
            'log_file_name'       => (string) fn_basename(Tygh::$app['addons.commerceml.log_file_path']),
        ];

        $condition = [
            db_quote('company_id = ?i', $company_id)
        ];

        if (!empty($params['type'])) {
            $condition[] = db_quote('type = ?i', $params['type']);
        }

        $last_sync = db_get_row(
            'SELECT * FROM ?:commerceml_imports WHERE 1=1 AND ?p ORDER BY updated_at DESC LIMIT 1',
            implode(' AND ', $condition)
        );

        if (empty($last_sync)) {
            return $result;
        }

        $result['status'] = __('commerceml.last_status.' . $last_sync['status']);
        $result['status_code'] = (string) $last_sync['status'];
        $result['last_sync_timestamp'] = (int) $last_sync['updated_at'];
        $result['log_file_url']  = fn_url('commerceml.get_log?company_id=' . $company_id);

        return $result;
    }
}
