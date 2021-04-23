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


namespace Tygh\Addons\Retailcrm;

/**
 * The class logs all messages in the file.
 *
 * @package Tygh\Addons\Retailcrm
 */
class Logger
{
    /**
     * Error message level.
     */
    const LEVEL_ERROR = 'error';

    /**
     * Warning message level.
     */
    const LEVEL_WARNING = 'warning';

    /**
     * Informational message level.
     */
    const LEVEL_INFO = 'info';

    /**
     * @var int number of log files used for rotation.
     */
    private $max_log_files;

    /**
     * @var int maximum log file size, in kilo-bytes.
     */
    private $max_file_size;

    /**
     * @var string Log file path
     */
    private $file_path;

    /**
     * @var int the permission to be set for newly created log files.
     */
    private $dir_mode;

    /**
     * @var int the permission to be set for newly created directories.
     */
    private $file_mode;

    /**
     * Initializes the logger.
     *
     * @param string    $file_path      Log file path
     * @param int       $file_mode      The permission to be set for newly created directories.
     * @param int       $dir_mode       The permission to be set for newly created log files.
     * @param int       $max_file_size  Maximum log file size, in kilo-bytes.
     * @param int       $max_log_files  Number of log files used for rotation.
     */
    public function __construct($file_path, $file_mode = 0664, $dir_mode = 0775, $max_file_size = 10240, $max_log_files = 5)
    {
        $this->file_path = $file_path;
        $this->max_file_size = $max_file_size;
        $this->max_log_files = $max_log_files;
        $this->file_mode = $file_mode;
        $this->dir_mode = $dir_mode;

        $dir = dirname($file_path);

        if (!is_dir($dir)) {
            @mkdir($dir, $this->dir_mode);
            chmod($dir, $this->dir_mode);
        }

        if (@filesize($this->file_path) > $this->max_file_size * 1024) {
            $this->rotateFiles();
        }

        if (!file_exists($this->file_path)) {
            @touch($this->file_path);
            chmod($this->file_path, $this->file_mode);
        }
    }

    /**
     * Logs a message
     *
     * @param string $message   The message to be logged.
     * @param string $level     The level of the message.
     * @param string $category  The category of the message.
     */
    public function log($message, $level, $category = null)
    {
        $line = sprintf('%s [%s] [%s]: %s', date('Y-m-d H:i:s'), $level, $category, $message);
        $line .= "\n";

        file_put_contents($this->file_path, $line, FILE_APPEND);
    }

    /**
     * Logs a warning message
     *
     * @param string $message   The message to be logged.
     * @param string $category  The category of the message.
     */
    public function waning($message, $category = null)
    {
        $this->log($message, self::LEVEL_WARNING, $category);
    }

    /**
     * Logs a info message
     *
     * @param string $message   The message to be logged.
     * @param string $category  The category of the message.
     */
    public function info($message, $category = null)
    {
        $this->log($message, self::LEVEL_INFO, $category);
    }

    /**
     * Logs a error message
     *
     * @param string $message   The message to be logged.
     * @param string $category  The category of the message.
     */
    public function error($message, $category = null)
    {
        $this->log($message, self::LEVEL_ERROR, $category);
    }

    /**
     * Rotates log files.
     */
    protected function rotateFiles()
    {
        $file = $this->file_path;

        for ($i = $this->max_log_files; $i >= 0; --$i) {
            $rotate_file = $file . ($i === 0 ? '' : '.' . $i);

            if (is_file($rotate_file)) {
                if ($i === $this->max_log_files) {
                    @unlink($rotate_file);
                } else {
                    @rename($rotate_file, $file . '.' . ($i + 1));
                }
            }
        }
    }
}