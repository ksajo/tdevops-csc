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

namespace Tygh\Ym;

use Tygh\Registry;

class Logs
{
    const INFO = 1;
    const SKIP_PRODUCT = 2;
    const SKIP_PRODUCT_COMBINATION = 3;

    const MAX_LOGS_FILE = 10;

    protected $path = '';
    protected $filename = 'log_tmp.txt';
    protected $file = null;
    protected $format = '';
    protected $save_name = '';
    protected $price_id = 0;

    public function __construct($format = 'csv', $price_id = 0)
    {
        $this->format = $format;
        $this->price_id = $price_id;
        $this->filename = 'log_tmp_' . $price_id . '.' . $this->format;
        $this->path = fn_get_files_dir_path() . 'yml/logs/';
        fn_mkdir($this->path);

        $file_is_new = !file_exists($this->path . $this->filename);

        $this->file = fopen($this->path . $this->filename, 'ab');

        if ($file_is_new && $this->format == 'csv') {
            fwrite($this->file, 'Type; Object ID; Message;' . PHP_EOL);
        }
    }

    public function __destruct() {
        fclose($this->file);
    }

    public function getHashFile($path = 'yml/logs')
    {
        if ($path == '')
            $path = DIRECTORY_SEPARATOR;

        $hash = strtr(base64_encode($path), '+/=', '-_.');
        $hash = rtrim($hash, '.');

        return "l1_". $hash;
    }

    public function getPathFile()
    {
        return $this->path;
    }

    public function getFileName()
    {
        if (empty($this->save_name)) {
            $this->save_name = date('ymd_His', TIME) . '_' . $this->price_id . '.' . $this->format;
        }

        return $this->save_name;
    }

    public function write($type, $object, $message = '')
    {
        $data = array(
            0 => '',
            1 => '',
            2 => ''
        );

        if ($type == self::SKIP_PRODUCT) {
            $product_name = fn_substr($object['product'], 0, 20);
            if (strlen($object['product']) > 20) {
                $product_name .= "...";
            }
            $data[0] = '[SKIP PRODUCT]';
            $data[1] = $object['product_id'] . " (" . $product_name . ") - ";
            $data[2] = $message;

        } elseif ($type == self::INFO) {

            $data[0] ='[INFO]';

            if (!is_array($object)) {
                $data[1] = $object;
            }

            if (!empty($message)) {
                $data[2] = $message;
            }

        } elseif ($type == self::SKIP_PRODUCT_COMBINATION) {

            $product_name = fn_substr($object['product'], 0, 20);
            if (strlen($object['product']) > 20) {
                $product_name .= "...";
            }
            $data[0] = '[SKIP COMBINATION]';
            $data[1] = $object['product_id'] . " (" . $product_name . ") - ";
            $data[2] = $message;
        }

        if (!fn_is_empty($data)) {
            if ($this->format == 'csv') {
                fwrite($this->file, $this->csv($data) . PHP_EOL);
            } else {
                fwrite($this->file, implode(' ', $data) . PHP_EOL);
            }
        }
    }

    public function rotate()
    {
        if (file_exists($this->path . $this->filename)) {

            if (empty($this->save_name)) {
                $this->save_name = date('ymd_His', TIME) . '_' . $this->price_id .'.' . $this->format;
            }

            fclose($this->file);
            fn_rename($this->path . $this->filename, $this->path . $this->save_name);

            $this->file = fopen($this->path . $this->save_name, 'ab');

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

    protected function csv($data)
    {
        return implode('; ', $data);
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
