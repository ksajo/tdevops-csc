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


use Tygh\Common\OperationResult;

/**
 * Class UploadImportFileCommandHandler
 *
 * @package Tygh\Addons\CommerceML\Commands
 */
class UploadImportFileCommandHandler
{
    /**
     * Executes upload
     *
     * @param \Tygh\Addons\CommerceML\Commands\UploadImportFileCommand $command Command instance
     *
     * @return OperationResult
     */
    public function handle(UploadImportFileCommand $command)
    {
        $uplaod_dir = $this->getUploadDir($command);
        $file_path = sprintf('%s/%s', $uplaod_dir, $command->file_name);

        $this->createUploadDirIfNotExists($uplaod_dir);

        if ($this->saveFile($file_path, $command) === false) {
            $result = new OperationResult(false);
            $result->addError('open_file_error', sprintf('The file %s could not be saved.', $command->file_name));
            return $result;
        }

        $result = new OperationResult(true);
        $result->setData($file_path, 'file_path');

        return $result;
    }

    /**
     * @param \Tygh\Addons\CommerceML\Commands\UploadImportFileCommand $command Command instance
     *
     * @return string
     */
    private function getUploadDir(UploadImportFileCommand $command)
    {
        $upload_dir = rtrim($command->dir_path, '/');

        if ($this->isContentFile($command->file_name)) {
            $upload_dir .= '/import_files';
        }

        return $upload_dir;
    }

    /**
     * Checks if file is product content
     *
     * @param string $filename File name
     *
     * @return bool
     */
    private function isContentFile($filename)
    {
        return !in_array(fn_strtolower(fn_get_file_ext($filename)), ['zip', 'xml'], true);
    }

    /**
     * Creates upload directory if not exists
     *
     * @param string $uplaod_dir Upload dir
     */
    private function createUploadDirIfNotExists($uplaod_dir)
    {
        if (is_dir($uplaod_dir)) {
            return;
        }

        fn_mkdir($uplaod_dir);
        @chmod($uplaod_dir, DEFAULT_DIR_PERMISSIONS);
    }

    /**
     * Saves content to file
     *
     * @param string                                                   $file_path File path
     * @param \Tygh\Addons\CommerceML\Commands\UploadImportFileCommand $command   Command instance
     *
     * @return bool
     */
    private function saveFile($file_path, UploadImportFileCommand $command)
    {
        $file = @fopen($file_path, 'a');

        if ($file === false) {
            return false;
        }

        fwrite($file, $command->getFileContent());
        fclose($file);
        @chmod($file_path, DEFAULT_FILE_PERMISSIONS);

        return true;
    }
}
