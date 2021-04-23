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


use Tygh\Addons\CommerceML\Dto\ImportDto;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Xml\Exceptions\XmlParserException;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;
use Tygh\Addons\CommerceML\Xml\XmlParser;
use Tygh\Common\OperationResult;
use Tygh\Enum\ObjectStatuses;
use Tygh\Exceptions\DeveloperException;

/**
 * Class CreateImportCommandHandler
 *
 * @package Tygh\Addons\CommerceML\Commands
 */
class CreateImportCommandHandler
{
    /**
     * @var callable
     */
    private $import_storage_factory;

    /**
     * @var \Tygh\Addons\CommerceML\Xml\XmlParser
     */
    private $xml_parser;

    /**
     * @var callable
     */
    private $xml_parser_callbacks_factory;

    /**
     * CreateImportCommandHandler constructor.
     *
     * @param callable                              $import_storage_factory       Import storage factory
     * @param \Tygh\Addons\CommerceML\Xml\XmlParser $xml_parser                   XML parser
     * @param callable                              $xml_parser_callbacks_factory Xml parser callbacks factory
     */
    public function __construct(
        callable $import_storage_factory,
        XmlParser $xml_parser,
        callable $xml_parser_callbacks_factory
    ) {
        $this->import_storage_factory = $import_storage_factory;
        $this->xml_parser = $xml_parser;
        $this->xml_parser_callbacks_factory = $xml_parser_callbacks_factory;
    }

    /**
     * Executes creating import
     *
     * @param \Tygh\Addons\CommerceML\Commands\CreateImportCommand $command Command instance
     *
     * @return \Tygh\Common\OperationResult
     */
    public function handle(CreateImportCommand $command)
    {
        $result = new OperationResult();

        $file_paths = $this->sortFiles($command->xml_file_paths);
        $import_storage = $this->createImportStorage($command);
        $callbacks = $this->getCallbacks($import_storage, $command->import_type);

        try {
            foreach ($file_paths as $file_path) {
                $this->xml_parser->parse($file_path, $callbacks);
            }

            $import_storage->saveImport();

            $result->setSuccess(true);
            $result->setData($import_storage->getImport(), 'import');
            $result->setData($import_storage, 'import_storage');
        } catch (XmlParserException $exception) {
            $import_storage->removeImport();
            $result->addError('xml', $exception->getMessage());
        }

        return $result;
    }

    /**
     * @param array<array-key, string> $file_paths Xml file paths
     *
     * @return array<array-key, string>
     */
    private function sortFiles(array $file_paths)
    {
        usort($file_paths, static function ($a_file_path, $b_file_path) {
            if (strpos($a_file_path, 'import') !== false) {
                return -1;
            }
            if (strpos($b_file_path, 'import') !== false) {
                return 1;
            }
            if (strpos($a_file_path, 'offer') !== false) {
                return -1;
            }
            if (strpos($b_file_path, 'offer') !== false) {
                return 1;
            }

            return 0;
        });

        return $file_paths;
    }

    /**
     * Gets xml parser callbacks
     *
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     * @param string                                         $import_type    Import type
     *
     * @return array<string, callable>
     */
    private function getCallbacks(ImportStorage $import_storage, $import_type)
    {
        /** @var array<string, callable> $xml_parser_callbacks */
        $xml_parser_callbacks = call_user_func($this->xml_parser_callbacks_factory, $import_type);

        foreach ($xml_parser_callbacks as $key => $callback) {
            $xml_parser_callbacks[$key] = static function (SimpleXmlElement $xml) use ($callback, $import_storage) {
                $callback($xml, $import_storage);
            };
        }

        return $xml_parser_callbacks;
    }

    /**
     * Creates import storage
     *
     * @param \Tygh\Addons\CommerceML\Commands\CreateImportCommand $command Command instance
     *
     * @return \Tygh\Addons\CommerceML\Storages\ImportStorage
     */
    private function createImportStorage(CreateImportCommand $command)
    {
        $import = new ImportDto();
        $import->company_id = $command->company_id;
        $import->user_id = $command->user_id;
        $import->import_key = $command->import_key;
        $import->status = ObjectStatuses::NEW_OBJECT;
        $import->type = $command->import_type;
        $import->created_at = time();
        $import->updated_at = time();

        /** @var ImportStorage $import_storage */
        $import_storage = call_user_func($this->import_storage_factory, $import);

        if (!$import_storage instanceof ImportStorage) {
            throw new DeveloperException();
        }

        $import_storage->saveImport();

        return $import_storage;
    }
}
