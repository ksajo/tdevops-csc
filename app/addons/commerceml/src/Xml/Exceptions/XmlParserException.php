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


namespace Tygh\Addons\CommerceML\Xml\Exceptions;


use RuntimeException;

/**
 * Class XmlParserException
 *
 * @package Tygh\Addons\CommerceML\Xml\Exceptions
 */
class XmlParserException extends RuntimeException
{
    /**
     * Throws exception by libxml errors
     *
     * @throws \Tygh\Addons\CommerceML\Xml\Exceptions\XmlParserException If parsing failed.
     */
    public static function libxmlErrors()
    {
        $errors = libxml_get_errors();

        if (empty($errors)) {
            throw new self('Undefined error');
        }

        $messages = [];

        foreach ($errors as $error) {
            $message = '';

            switch ($error->level) {
                case LIBXML_ERR_WARNING:
                    $message .= "Warning {$error->code}: ";
                    break;
                case LIBXML_ERR_ERROR:
                    $message .= "Error {$error->code}: ";
                    break;
                case LIBXML_ERR_FATAL:
                    $message .= "Fatal Error {$error->code}: ";
                    break;
            }

            $message .= trim($error->message);
            $message .= "\n  Line: {$error->line}";
            $message .= "\n  Column: {$error->column}";

            if ($error->file) {
                $message .= "\n  File: {$error->file}";
            }

            $messages[] = $message;
        }

        throw new self(implode(';' . PHP_EOL, $messages));
    }
}
