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


namespace Tygh\Addons\CommerceML;


use Tygh;
use Tygh\Addons\CommerceML\Dto\CategoryDto;
use Tygh\Addons\CommerceML\Dto\PriceTypeDto;
use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto;
use Tygh\Addons\CommerceML\Dto\TaxDto;
use Tygh\Core\ApplicationInterface;
use Tygh\Core\BootstrapInterface;
use Tygh\Core\HookHandlerProviderInterface;

/**
 * This class describes instructions for loading the commerceML add-on
 *
 * @package Tygh\Addons\CommerceML
 */
class Bootstrap implements BootstrapInterface, HookHandlerProviderInterface
{
    /**
     * @inheritDoc
     */
    public function boot(ApplicationInterface $app)
    {
        $app->register(new ServiceProvider());
    }

    /**
     * @inheritDoc
     */
    public function getHookHandlerMap()
    {
        return [
            'delete_product_post' => static function ($product_id, $product_deleted) {
                if (!$product_deleted) {
                    return;
                }
                ServiceProvider::getImportEntityMapRepository()->removeByLocalId(ProductDto::REPRESENT_ENTITY_TYPE, $product_id);
            },
            'delete_category_after' => static function ($category_id) {
                ServiceProvider::getImportEntityMapRepository()->removeByLocalId(CategoryDto::REPRESENT_ENTITY_TYPE, $category_id);
            },
            'delete_feature_post' => static function ($feature_id) {
                ServiceProvider::getImportEntityMapRepository()->removeByLocalId(ProductFeatureDto::REPRESENT_ENTITY_TYPE, $feature_id);
            },
            'delete_product_feature_variants_post' => static function ($feature_id, $variant_ids) {
                ServiceProvider::getImportEntityMapRepository()->removeByLocalIds(ProductFeatureVariantDto::REPRESENT_ENTITY_TYPE, $variant_ids);
            },
            'delete_usergroups' => static function ($usergroup_ids) {
                $loca_ids = array_map(static function ($usergroup_id) {
                    return PriceTypeDto::createLocalIdByUsergroupId($usergroup_id);
                }, $usergroup_ids);

                ServiceProvider::getImportEntityMapRepository()->removeByLocalIds(PriceTypeDto::REPRESENT_ENTITY_TYPE, $loca_ids);
            },
            'delete_tax_pre' => static function ($tax_id) {
                ServiceProvider::getImportEntityMapRepository()->removeByLocalId(TaxDto::REPRESENT_ENTITY_TYPE, $tax_id);
            },
            'variation_group_mark_product_as_main_post' => static function ($service, $group, $from_group_product, $to_group_product) {
                /** @var \Tygh\Addons\ProductVariations\Product\Group\GroupProduct $to_group_product */

                $external_ids = ServiceProvider::getImportEntityMapRepository()->findEntityIds(
                    ProductDto::REPRESENT_ENTITY_TYPE,
                    $to_group_product->getProductId(),
                    $to_group_product->getCompanyId()
                );

                foreach ($external_ids as $external_id) {
                    if (strpos($external_id, '#') === false) {
                        continue;
                    }

                    $parent_external_id = explode('#', $external_id)[0];

                    ServiceProvider::getImportEntityMapRepository()->add(
                        $to_group_product->getCompanyId(),
                        ProductDto::REPRESENT_ENTITY_TYPE,
                        $parent_external_id,
                        $to_group_product->getProductId()
                    );
                }
            },
            'get_route' => static function (&$req, $result, $area, $is_allowed_url) {
                if (!isset($req['dispatch']) || $req['dispatch'] !== 'commerceml') {
                    return;
                }

                /** @var \Tygh\Web\Session $session */
                $session = Tygh::$app['session'];
                unset($req[$session->getName()]);
            },
            'get_orders' => static function ($params, $fields, $sortings, &$condition) {
                if (!isset($params['from_order_id'])) {
                    return;
                }

                $condition .= db_quote(' AND ?:orders.order_id >= ?i', (int) $params['from_order_id']);
            },
            'get_feedback_data' => [
                'addons.commerceml.hook_handlers.feedback',
                /** @see \Tygh\Addons\CommerceML\HookHandlers\FeedbackHookHandler::onGetFeedbackData() */
                'onGetFeedbackData'
            ]
        ];
    }
}
