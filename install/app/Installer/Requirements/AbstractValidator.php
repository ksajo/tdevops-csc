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

namespace Installer\Requirements;

/**
 * Class AbstractValidator provides the general PHP extensions requirement validator behavior.
 *
 * @package Installer\Requirements
 */
abstract class AbstractValidator implements ValidatorInteface
{
    /** @var string[] $extensions Required extensions */
    protected $extensions = [];

    /** @var int $extensions_mode Extensions requirement conditions */
    protected $extensions_mode = self::REQUIRE_ANY;

    /** @var string[] $errors Validation errors */
    protected $errors = [];

    /** @var string[] $warnings Validation warnings */
    protected $warnings = [];

    /** @var string[] $installed_extensions Installed extensions cache */
    protected static $installed_extensions;

    /** @inheritdoc */
    public function __construct()
    {
        if (static::$installed_extensions === null) {
            static::$installed_extensions = get_loaded_extensions();
        }
    }

    /** @inheritdoc */
    public function getRequirements()
    {
        return $this->extensions;
    }

    /** @inheritdoc */
    public function getRequirementsMode()
    {
        return $this->extensions_mode;
    }

    /** @inheritdoc */
    public function validate()
    {
        $match = array_intersect($this->extensions, static::$installed_extensions);

        $result = (bool) $match;
        if ($this->extensions_mode === self::REQUIRE_ALL) {
            $result = $match === $this->extensions;
        }

        if (!$result) {
            $this->errors[] = self::EXTENSION_MISSING;
        }

        return $result;
    }

    /** @inheritdoc */
    public function getErrors()
    {
        return $this->errors;
    }

    /** @inheritdoc */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    /** @inheritdoc */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /** @inheritdoc */
    public function setWarnings(array $warnings)
    {
        $this->warnings = $warnings;
    }
}
