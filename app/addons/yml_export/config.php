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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

define('YML_CATEGORIES_MAX_COUNT', 25);
define('ITERATION_OFFERS', 5000);

define('YML_REQUEST_ERROR_REPEATS', 5);
define('YML_REQUEST_ERROR_SLEEP_SECONDS', 10);

define('YML_MIN_FEE', 200);

if (defined('CONSOLE')) {
    define('NEW_LINE', PHP_EOL);
} else {
    define('NEW_LINE', "<br />");
}