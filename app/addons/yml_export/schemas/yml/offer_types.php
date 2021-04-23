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

$schema = array (
    'common' => 'yml2_offer_type_common', // Class not exist, just for common settings of offer
    'simple' => 'yml2_offer_type_simple',
    'vendor' => 'yml2_offer_type_vendor_model',
    'apparel_simple' => 'yml2_offer_type_apparel_simple',
    'apparel' => 'yml2_offer_type_apparel',
    'book' => 'yml2_offer_type_book',
    'video' => 'yml2_offer_type_video',
    'audio' => 'yml2_offer_type_audio'
);

return $schema;