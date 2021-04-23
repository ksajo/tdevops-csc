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
use Tygh\Tools\Archiver;
use Exception;

class UnzipImportFileCommandHandler
{
    /**
     * @var \Tygh\Tools\Archiver
     */
    private $archiver;

    /**
     * UnzipImportFileCommandHandler constructor.
     *
     * @param \Tygh\Tools\Archiver $archiver Archiver instance
     */
    public function __construct(Archiver $archiver)
    {
        $this->archiver = $archiver;
    }

    /**
     * Executes unziping import file
     *
     * @param \Tygh\Addons\CommerceML\Commands\UnzipImportFileCommand $command Command instance
     *
     * @return \Tygh\Common\OperationResult
     */
    public function handle(UnzipImportFileCommand $command)
    {
        $result = new OperationResult();

        try {
            $this->archiver->extractTo($command->file_path, $command->dir_path);
            $result->setSuccess(true);
        } catch (Exception $e) {
            $result->setErrors([$e->getMessage()]);
        }

        if ($command->remove_file && $result->isSuccess()) {
            fn_rm($command->file_path);
        }

        return $result;
    }
}
