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
 * Class LocalIdDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class LocalIdDto
{
    const VALUE_CREATE = '__create__';

    const VALUE_SKIP = '__skip__';

    const VALUE_USE_DEFAULT = '__use_default__';

    /**
     * @var string|int|null
     */
    private $value;

    /**
     * IdMapDto constructor.
     *
     * @param string|int|null $value Value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function hasValue()
    {
        if (empty($this->value)) {
            return false;
        }

        return is_int($this->value) || strpos($this->value, '__') !== 0;
    }

    /**
     * @return bool
     */
    public function hasNotValue()
    {
        return !$this->hasValue();
    }

    /**
     * @return bool
     */
    public function isNullValue()
    {
        return $this->value === null;
    }

    /**
     * @return bool
     */
    public function isSkipValue()
    {
        return $this->value === self::VALUE_SKIP;
    }

    /**
     * @return bool
     */
    public function isUseDefaultValue()
    {
        return $this->value === self::VALUE_USE_DEFAULT;
    }

    /**
     * @return bool
     */
    public function isCreateValue()
    {
        return $this->value === self::VALUE_CREATE;
    }

    /**
     * @return int
     */
    public function asInt()
    {
        return (int) $this->value;
    }

    /**
     * @return string
     */
    public function asString()
    {
        return (string) $this->value;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * @param string|int|null $value Value
     *
     * @return \Tygh\Addons\CommerceML\Dto\LocalIdDto
     */
    public static function create($value)
    {
        return new self($value);
    }
}
