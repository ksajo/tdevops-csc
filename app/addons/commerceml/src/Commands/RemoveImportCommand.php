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
 * Class RemoveImportCommand
 *
 * @package Tygh\Addons\CommerceML\Commands
 *
 * @see \Tygh\Addons\CommerceML\Commands\RemoveImportCommandHandler
 */
class RemoveImportCommand
{
    /**
     * @var int
     */
    private $import_id;

    /**
     * Creates command instance
     *
     * @param int $import_id Import ID
     *
     * @return \Tygh\Addons\CommerceML\Commands\RemoveImportCommand
     */
    public static function create($import_id)
    {
        $self = new self();
        $self->import_id = (int) $import_id;

        return $self;
    }

    /**
     * @return int
     */
    public function getImportId()
    {
        return $this->import_id;
    }
}
