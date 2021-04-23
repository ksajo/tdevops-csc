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
 * Class ImageDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class ImageDto
{
    /**
     * @var string
     */
    public $path;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @param string $path        Image patch
     * @param string $description Image description
     *
     * @return \Tygh\Addons\CommerceML\Dto\ImageDto
     */
    public static function create($path, $description = '')
    {
        $object = new self();
        $object->path = (string) $path;
        $object->description = (string) $description;

        return $object;
    }
}
