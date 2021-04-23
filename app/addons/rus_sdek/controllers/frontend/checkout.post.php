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

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (isset($_REQUEST['drop_select_office'])) {
    $_REQUEST['select_office'] = Tygh::$app['session']['cart']['select_office'] = false;
}

if(isset(Tygh::$app['session']['cart']['select_office'])) {
    Tygh::$app['view']->assign('select_office', Tygh::$app['session']['cart']['select_office']);
}
