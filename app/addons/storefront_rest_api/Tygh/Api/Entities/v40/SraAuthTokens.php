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

namespace Tygh\Api\Entities\v40;

use Tygh\Api\Entities\AuthTokens;
use Tygh\Api\Response;

/**
 * Class SraAuthTokens
 *
 * @package Tygh\Api\Entities
 */
class SraAuthTokens extends AuthTokens
{
    /** @inheritdoc */
    public function create($params)
    {
        $ekey = $this->safeGet($params, 'ekey', null);

        if (!$ekey) {
            return parent::create($params);
        }

        $status = Response::STATUS_NOT_FOUND;
        $data = [];
        $user_id = fn_get_object_by_ekey($ekey, 'U');

        if ($user_id) {
            list($token, $expiry_time) = fn_get_user_auth_token($user_id);

            $status = Response::STATUS_CREATED;
            $data = [
                'token' => $token,
                'ttl'   => $expiry_time - TIME
            ];
        }

        return [
            'status' => $status,
            'data'   => $data
        ];
    }
}
