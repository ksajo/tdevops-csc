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


/**
 * Class CreateImportCommand
 *
 * @package Tygh\Addons\CommerceML\Commands
 *
 * @see \Tygh\Addons\CommerceML\Commands\CreateImportCommandHandler
 */
class CreateImportCommand
{
    /**
     * @var array<array-key, string>
     */
    public $xml_file_paths = [];

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
    public $import_key;

    /**
     * @var string
     */
    public $import_type;

    /**
     * Create command instance
     *
     * @param array<string>             $xml_file_paths File path list
     * @param array<string, string|int> $auth           Auth data
     * @param string                    $import_key     Import key
     * @param string                    $import_type    Import type
     *
     * @return \Tygh\Addons\CommerceML\Commands\CreateImportCommand
     */
    public static function create(array $xml_file_paths, array $auth, $import_key, $import_type)
    {
        $self = new self();

        $self->xml_file_paths = array_filter($xml_file_paths);
        $self->company_id = isset($auth['company_id']) ? (int) $auth['company_id'] : 0;
        $self->user_id = isset($auth['user_id']) ? (int) $auth['user_id'] : 1;
        $self->import_key = $import_key;
        $self->import_type = $import_type;

        return $self;
    }
}
