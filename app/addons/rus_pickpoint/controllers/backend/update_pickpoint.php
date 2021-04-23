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

use Tygh\Shippings\RusPickpoint;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode === 'postamat_update') {
    $url = RusPickpoint::url();
    RusPickpoint::postamatPickpoint($url . 'postamatlist');

    return [CONTROLLER_STATUS_REDIRECT, 'addons.update?addon=rus_pickpoint'];
}
