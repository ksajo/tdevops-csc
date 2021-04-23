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
 * Class CleanUpFilesDirCommandHandler
 *
 * @package Tygh\Addons\CommerceML\Commands
 */
class CleanUpFilesDirCommandHandler
{
    /**
     * Executes clean up directory
     *
     * @param \Tygh\Addons\CommerceML\Commands\CleanUpFilesDirCommand $command Clean Up command
     *
     * @return \Tygh\Common\OperationResult
     */
    public function handle(CleanUpFilesDirCommand $command)
    {
        $result = new OperationResult(true);

        if (!$command->rotate) {
            fn_rm($command->dir, false);
            return $result;
        }

        if (!is_dir($command->dir) || $this->isDirEmpty($command->dir)) {
            return $result;
        }

        for ($i = $command->max_dirs_count; $i >= 0; --$i) {
            $dir_path = rtrim($command->dir, '/') . ($i === 0 ? '' : '.' . $i);

            if (!is_dir($dir_path)) {
                continue;
            }

            if ($i === $command->max_dirs_count) {
                fn_rm($dir_path);
            } else {
                fn_rename($dir_path, rtrim($command->dir, '/') . '.' . ($i + 1));
            }
        }

        fn_mkdir($command->dir);

        return $result;
    }

    /**
     * Checks if dir is empty
     *
     * @param string $dir Dir path
     *
     * @return bool
     */
    private function isDirEmpty($dir)
    {
        $dh = opendir($dir);

        if ($dh === false) {
            return false;
        }

        while (($item = readdir($dh)) !== false) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            closedir($dh);
            return false;
        }

        closedir($dh);
        return true;
    }
}
