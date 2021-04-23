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

$schema['/exim_1c'] = array (
    'dispatch' => 'exim_1c'
);

$schema['/exim_cml'] = array (
    'dispatch' => 'exim_1c',
    'service_exchange' => 'exim_cml'
);

$schema['/exim_moysklad'] = array (
    'dispatch' => 'exim_1c',
    'service_exchange' => 'exim_moysklad'
);

$schema['/exim_class'] = array (
    'dispatch' => 'exim_1c',
    'service_exchange' => 'exim_class'
);

return $schema;
