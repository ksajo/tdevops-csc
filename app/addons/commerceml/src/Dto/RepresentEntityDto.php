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
 * Interface RepresentEntityDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
interface RepresentEntityDto
{
    /**
     * Gets represented type of entity (product, product_feature, category, etc)
     *
     * @return string
     */
    public function getEntityType();

    /**
     * Gets entity ID
     *
     * @return \Tygh\Addons\CommerceML\Dto\IdDto
     */
    public function getEntityId();

    /**
     * Gets entity name
     *
     * @return string
     */
    public function getEntityName();
}
