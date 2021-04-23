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
 * Class UploadImportFileCommand
 *
 * @package Tygh\Addons\CommerceML\Commands
 *
 * @see \Tygh\Addons\CommerceML\Commands\UploadImportFileCommandHandler
 */
class UploadImportFileCommand
{
    /**
     * @var string
     */
    public $file_name;

    /**
     * @var string
     */
    public $dir_path;

    /**
     * Returns file content from the POST data
     *
     * @return string
     */
    public function getFileContent()
    {
        return file_get_contents('php://input');
    }

    /**
     * Creates upload command
     *
     * @param string $filename File name
     * @param string $dir_path Upload dir
     *
     * @return \Tygh\Addons\CommerceML\Commands\UploadImportFileCommand
     */
    public static function create($filename, $dir_path = null)
    {
        if ($dir_path === null) {
            $dir_path = sprintf('%s/exim/1C_%s/', rtrim(fn_get_files_dir_path(), '/'), date('dmY'));
        }

        $self = new self();

        $self->file_name = (string) $filename;
        $self->dir_path = (string) $dir_path;

        return $self;
    }
}
