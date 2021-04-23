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
 * Class IdDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class IdDto
{
    /**
     * @var string|int Local object ID
     */
    public $local_id;

    /**
     * @var string External object ID
     */
    public $external_id;

    /**
     * @var string ID which should be used as entity ID at storage
     */
    public $id;

    /**
     * UuidDto constructor.
     *
     * @param string $external_id External object ID
     * @param string $local_id    Local object ID
     */
    public function __construct($external_id = '', $local_id = '')
    {
        $this->external_id = (string) $external_id;
        $this->local_id = (string) $local_id;

        if ($this->external_id) {
            $this->id = $this->external_id;
        } else {
            $this->id = $this->local_id;
        }
    }

    /**
     * Gets external id if it exists otherwise local id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Checks if local ID specified
     *
     * @return bool
     */
    public function hasLocalId()
    {
        return !empty($this->local_id);
    }

    /**
     * Removes local ID
     */
    public function removeLocalId()
    {
        $this->local_id = '';
    }

    /**
     * @param string|null $external_id External object ID
     *
     * @return \Tygh\Addons\CommerceML\Dto\IdDto
     */
    public static function createByExternalId($external_id)
    {
        return new self((string) $external_id);
    }

    /**
     * @param string|null $local_id Local object ID
     *
     * @return \Tygh\Addons\CommerceML\Dto\IdDto
     */
    public static function createByLocalId($local_id)
    {
        return new self('', (string) $local_id);
    }
}
