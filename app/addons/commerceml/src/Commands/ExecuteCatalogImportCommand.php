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
 * Class ExecuteImportCommand
 *
 * @package Tygh\Addons\CommerceML\Commands
 *
 * @see \Tygh\Addons\CommerceML\Commands\ExecuteCatalogImportCommandHandler
 */
class ExecuteCatalogImportCommand extends AImportCommand
{
    /**
     * Creates command instance
     *
     * @param int    $import_id   Import ID
     * @param int    $time_limit  Executing time limit
     * @param string $entity_type Entity type
     *
     * @return \Tygh\Addons\CommerceML\Commands\ExecuteCatalogImportCommand
     */
    public static function create($import_id, $time_limit, $entity_type)
    {
        $self = new self();
        $self->import_id = (int) $import_id;
        $self->time_limit = (int) $time_limit;
        $self->entity_type = $entity_type;

        return $self;
    }
}
