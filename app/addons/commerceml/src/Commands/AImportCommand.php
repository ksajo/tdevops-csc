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


namespace Tygh\Addons\CommerceML\Commands;


/**
 * Class AImportCommand
 *
 * @package Tygh\Addons\CommerceML\Commands
 */
abstract class AImportCommand
{
    /**
     * @var int
     */
    protected $import_id;

    /**
     * @var int
     */
    protected $time_limit = 0;

    /**
     * @var string
     */
    protected $entity_type;

    /**
     * @return int
     */
    public function getImportId()
    {
        return $this->import_id;
    }

    /**
     * @return bool
     */
    public function hasTimeLimit()
    {
        return $this->time_limit > 0;
    }

    /**
     * @return int
     */
    public function getTimeLimit()
    {
        return $this->time_limit;
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->entity_type;
    }
}
