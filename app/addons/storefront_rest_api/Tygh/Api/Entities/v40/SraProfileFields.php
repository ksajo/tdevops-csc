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

use Tygh\Addons\StorefrontRestApi\ASraEntity;
use Tygh\Addons\StorefrontRestApi\ProfileFields\Manager as ProfileFieldsManager;
use Tygh\Api\Response;
use Tygh\Enum\ProfileFieldAreas;
use Tygh\Tygh;

/**
 * Class SraProfileFields provides means to get profile fields via RESTful API.
 *
 * @package Tygh\Api\Entities\v40
 */
class SraProfileFields extends ASraEntity
{
    /** @var \Tygh\Addons\StorefrontRestApi\ProfileFields\Manager $manager */
    protected $manager;

    /** @inheritdoc */
    public function __construct(array $auth = [], $area = '')
    {
        parent::__construct($auth, $area);

        /** @var \Tygh\Addons\StorefrontRestApi\ProfileFields\Manager manager */
        $this->manager = Tygh::$app['addons.storefront_rest_api.profile_fields.manager'];

        $this->manager->setFieldFilter('fn_storefront_rest_api_filter_profile_fields');
    }

    /** @inheritdoc */
    public function index($id = 0, $params = [])
    {
        $params = array_merge([
            'location'  => ProfileFieldAreas::PROFILE,
            'action'    => ProfileFieldsManager::ACTION_UPDATE,
        ], $params);

        $lang_code = $this->getLanguageCode($params);

        $sections = $this->manager->get(
            $params['location'],
            $params['action'],
            $this->auth,
            $lang_code
        );

        return [
            'status' => Response::STATUS_OK,
            'data'   => $sections,
        ];
    }

    /** @inheritdoc */
    public function privileges()
    {
        return [
            'index' => true,
        ];
    }

    /** @inheritdoc */
    public function privilegesCustomer()
    {
        return [
            'index' => true,
        ];
    }

    /** @inheritdoc */
    public function create($params)
    {
        return [
            'status' => Response::STATUS_METHOD_NOT_ALLOWED,
        ];
    }

    /** @inheritdoc */
    public function update($id, $params)
    {
        return [
            'status' => Response::STATUS_METHOD_NOT_ALLOWED,
        ];
    }

    /** @inheritdoc */
    public function delete($id)
    {
        return [
            'status' => Response::STATUS_METHOD_NOT_ALLOWED,
        ];
    }
}
