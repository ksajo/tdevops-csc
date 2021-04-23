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


namespace Tygh\Addons\CommerceML\Dto;

/**
 * Class TranslatableValueDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class TranslatableValueDto implements ProductPropertyValue
{
    /**
     * @var string
     */
    public $default_value;

    /**
     * @var array<string, string>
     */
    public $translates = [];

    /**
     * TranslatableValueDto constructor.
     *
     * @param string                $default_value Default value
     * @param array<string, string> $translates    Value for all languages
     */
    public function __construct($default_value, array $translates = [])
    {
        $this->default_value = (string) $default_value;
        $this->translates = $translates;
    }

    /**
     * Checks if translate exists
     *
     * @param string $lang_code Langugage code (en, ru, etc)
     *
     * @return bool
     */
    public function hasTraslate($lang_code)
    {
        return isset($this->translates[$lang_code]);
    }

    /**
     * Gets translate
     *
     * @param string $lang_code Langugage code (en, ru, etc)
     *
     * @return string
     */
    public function getTranslate($lang_code)
    {
        if (!$this->hasTraslate($lang_code)) {
            return '';
        }

        return (string) $this->translates[$lang_code];
    }

    /**
     * Adds translation
     *
     * @param string $lang_code Language code
     * @param string $value     Translate
     */
    public function addTranslate($lang_code, $value)
    {
        $this->translates[$lang_code] = $value;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->default_value;
    }

    /**
     * @param string|null           $default_value Default value
     * @param array<string, string> $translates    Value for all languages
     *
     * @return \Tygh\Addons\CommerceML\Dto\TranslatableValueDto
     */
    public static function create($default_value, array $translates = [])
    {
        return new self((string) $default_value, $translates);
    }

    /**
     * @return string Default value
     */
    public function getPropertyValue()
    {
        return (string) $this->default_value;
    }
}
