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
 * Class UnzipImportFileCommand
 *
 * @package Tygh\Addons\CommerceML\Commands
 *
 * @see \Tygh\Addons\CommerceML\Commands\UnzipImportFileCommandHandler
 */
class UnzipImportFileCommand
{
    /**
     * @var string
     */
    public $file_path;

    /**
     * @var string
     */
    public $dir_path;

    /**
     * @var bool
     */
    public $remove_file = true;

    /**
     * Creates unzip command instance
     *
     * @param string $file_path   Archive file path
     * @param string $dir_path    Destination directory path
     * @param bool   $remove_file Whether to remove archive after unpack
     *
     * @return \Tygh\Addons\CommerceML\Commands\UnzipImportFileCommand
     */
    public static function create($file_path, $dir_path, $remove_file = true)
    {
        $self = new self();
        $self->file_path = (string) $file_path;
        $self->dir_path = (string) $dir_path;
        $self->remove_file = (bool) $remove_file;

        return $self;
    }
}
