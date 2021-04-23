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

namespace Tygh\Commerceml;

use Tygh\Registry;
use Tygh\Commerceml\RusEximCommerceml;

class Logs
{
    const MAX_LOGS_FILE = 10;

    protected $path = '';
    protected $filename = 'log_commerceml.txt';
    protected $file = null;
    protected $save_name = '';

    public function __construct($data_path, $path)
    {
        $this->filename = 'log_commerceml.txt';
        $this->path = $path;

        if (!is_dir($this->path)) {
            fn_mkdir($this->path);
        }

        $file_is_new = !file_exists($this->path . '/' . $this->filename);
        $this->file = fopen($this->path . '/' . $this->filename, 'ab');

        if ($file_is_new) {
            fwrite($this->file, 'Message: ' . PHP_EOL);
        }
    }

    public function __destruct() {
        fclose($this->file);
    }

    public function getPathFile()
    {
        return $this->path;
    }

    public function getFileName()
    {
        if (empty($this->save_name)) {
            $this->save_name = date('log_commerceml.txt', TIME);
        }

        return $this->save_name;
    }

    public function write($message = '')
    {
        if (!fn_is_empty($message)) {
            fwrite($this->file, $message . PHP_EOL);
        }
    }

    public function rotate()
    {
        if (file_exists($this->path . $this->filename)) {

            if (empty($this->save_name)) {
                $this->save_name = date('log_commerceml.txt', TIME);
            }

            fn_rename($this->path . $this->filename, $this->path . $this->save_name);

            $logs_list = fn_get_dir_contents($this->path, false, true);
            if (!empty($logs_list) && count($logs_list) > self::MAX_LOGS_FILE) {
                rsort($logs_list);
                list(, $old_logs) = array_chunk($logs_list, self::MAX_LOGS_FILE);
                foreach($old_logs as $filename) {
                    fn_rm($this->path . $filename);
                }
            }
        }
    }

    public function getTempLogFile()
    {
        if (file_exists($this->path . $this->filename)) {
            return $this->path . $this->filename;
        } else {
            return false;
        }
    }
}
