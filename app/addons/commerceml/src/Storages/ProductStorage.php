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


namespace Tygh\Addons\CommerceML\Storages;


use Tygh\Addons\CommerceML\Tools\RuntimeCacheStorage;
use Tygh\Common\OperationResult;
use Tygh\Registry;

/**
 * Class ProductStorage
 *
 * @package Tygh\Addons\CommerceML\Storages
 *
 * phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
 */
class ProductStorage
{
    /**
     * @var string
     */
    private $default_language_code;

    /**
     * @var \Tygh\Addons\CommerceML\Tools\RuntimeCacheStorage
     */
    private $product_features_cache;

    /**
     * ProductStorage constructor.
     *
     * @param string $default_language_code Default language code
     */
    public function __construct($default_language_code)
    {
        $this->default_language_code = $default_language_code;
        $this->product_features_cache = new RuntimeCacheStorage(100);
    }

    /**
     * Creates/updates product
     *
     * @param array<string, int|string|float|null|bool|array> $product_data  Product data
     * @param int                                             $product_id    Product ID
     * @param string|null                                     $lang_code     Language code
     * @param string|null                                     $error_message Error message
     *
     * @return \Tygh\Common\OperationResult
     */
    public function updateProduct($product_data, $product_id, $lang_code = null, $error_message = null)
    {
        $lang_code = $this->getLangCode($lang_code);

        return OperationResult::wrap(static function () use ($product_data, $product_id, $lang_code) {
            return fn_update_product($product_data, $product_id, $lang_code);
        }, $error_message);
    }

    /**
     * Updates product features values
     *
     * @param int                           $product_id              Product ID
     * @param array<int|string, int|string> $product_features_values Product features values
     * @param string|null                   $error_message           Error message
     *
     * @return \Tygh\Common\OperationResult
     */
    public function updateProductFeaturesValues($product_id, array $product_features_values, $error_message = null)
    {
        $lang_code = $this->getLangCode();

        return OperationResult::wrap(static function () use ($product_features_values, $product_id, $lang_code) {
            return fn_update_product_features_value($product_id, $product_features_values, [], $lang_code);
        }, $error_message);
    }

    /**
     * Creates/updates product category
     *
     * @param array<string, int|string|array> $category_data Category data
     * @param int                             $category_id   Category ID
     * @param string|null                     $lang_code     Language code
     * @param string|null                     $error_message Error message
     *
     * @return \Tygh\Common\OperationResult
     */
    public function updateCategory($category_data, $category_id, $lang_code = null, $error_message = null)
    {
        $lang_code = $this->getLangCode($lang_code);

        return OperationResult::wrap(static function () use ($category_data, $category_id, $lang_code) {
            return fn_update_category($category_data, $category_id, $lang_code);
        }, $error_message);
    }

    /**
     * Updates/creates product feature
     *
     * @param array<string, string|array|int> $product_feature_data Product feature data
     * @param int                             $feature_id           Feature ID
     * @param string|null                     $lang_code            Language code
     * @param string|null                     $error_message        Error message
     *
     * @return \Tygh\Common\OperationResult
     */
    public function updateProductFeature($product_feature_data, $feature_id, $lang_code, $error_message = null)
    {
        $lang_code = $this->getLangCode($lang_code);

        return OperationResult::wrap(static function () use ($product_feature_data, $feature_id, $lang_code) {
            return fn_update_product_feature($product_feature_data, $feature_id, $lang_code);
        }, $error_message);
    }

    /**
     * Upates/create product feature variants
     *
     * @param array<string, string|array> $product_feature_data Product feature data
     * @param int                         $feature_id           Feature ID
     * @param string|null                 $lang_code            Language code
     * @param string|null                 $error_message        Error message
     *
     * @return \Tygh\Common\OperationResult
     */
    public function updateProductFeatureVariants($product_feature_data, $feature_id, $lang_code = null, $error_message = null)
    {
        $lang_code = $this->getLangCode($lang_code);

        return OperationResult::wrap(static function () use ($product_feature_data, $feature_id, $lang_code) {
            fn_update_product_feature_variants($feature_id, $product_feature_data, $lang_code);
            return $product_feature_data;
        }, $error_message);
    }


    /**
     * Updates product images
     *
     * @param int          $product_id               Product ID
     * @param array<mixed> $pair_data_list           Pair data list
     * @param array<mixed> $detailed_image_data_list Detailed image data list
     *
     * @return \Tygh\Common\OperationResult
     */
    public function updateProductImages($product_id, array $pair_data_list, array $detailed_image_data_list)
    {
        $lang_code = $this->default_language_code;

        return OperationResult::wrap(static function () use ($product_id, $pair_data_list, $detailed_image_data_list, $lang_code) {
            return fn_update_image_pairs([], $detailed_image_data_list, $pair_data_list, $product_id, 'product', [], true, $lang_code);
        });
    }

    /**
     * Removes product
     *
     * @param int         $product_id    Product ID
     * @param string|null $error_message Error message
     *
     * @return \Tygh\Common\OperationResult
     */
    public function removeProduct($product_id, $error_message = null)
    {
        return OperationResult::wrap(static function () use ($product_id) {
            return fn_delete_product($product_id);
        }, $error_message);
    }

    /**
     * Remove image pairs
     *
     * @param array<int> $pair_ids Pair ids
     */
    public function removeImagePairs(array $pair_ids)
    {
        foreach ($pair_ids as $pair_id) {
            fn_delete_image_pair($pair_id);
        }
    }

    /**
     * Finds product features by IDs
     *
     * @param array<int> $product_feautre_ids Product feature IDs
     *
     * @return array<int, mixed>
     */
    public function findProductFeatures(array $product_feautre_ids = [])
    {
        $result = [];

        foreach ($product_feautre_ids as $key => $feautre_id) {
            if (!$this->product_features_cache->has($feautre_id)) {
                continue;
            }
            $result[$feautre_id] = $this->product_features_cache->get($feautre_id);
            unset($product_feautre_ids[$key]);
        }

        if ($product_feautre_ids) {
            $sharing_backup = Registry::get('runtime.skip_sharing_selection');
            Registry::set('runtime.skip_sharing_selection', true);

            $product_feautres = db_get_hash_array(
                'SELECT feature_id, purpose, feature_type, feature_code FROM ?:product_features WHERE feature_id IN (?n)',
                'feature_id',
                $product_feautre_ids
            );

            Registry::set('runtime.skip_sharing_selection', $sharing_backup);

            foreach ($product_feautres as $feautre_id => $feautre) {
                $result[$feautre_id] = $feautre;
                $this->product_features_cache->add($feautre_id, $feautre);
            }
        }

        return $result;
    }

    /**
     * Finds product feature by ID
     *
     * @param int $product_feautre_id Product feature ID
     *
     * @return array<mixed>
     */
    public function findProductFeature($product_feautre_id)
    {
        $features = $this->findProductFeatures([$product_feautre_id]);

        return isset($features[$product_feautre_id]) ? $features[$product_feautre_id] : [];
    }

    /**
     * Gets raw product data
     *
     * @param int $product_id Product ID
     *
     * @return array<string, mixed>
     */
    public function getRawProductData($product_id)
    {
        $product_id = (int) $product_id;

        $product_data = db_get_row('SELECT * FROM ?:products WHERE product_id = ?i', $product_id);

        if ($product_data) {
            $product_data['category_ids'] = [];
            $product_data['main_category'] = null;

            $category_links = db_get_array(
                'SELECT category_id, link_type FROM ?:products_categories WHERE product_id = ?i',
                $product_id
            );

            foreach ($category_links as $link) {
                $product_data['category_ids'][] = (int) $link['category_id'];

                if ($link['link_type'] !== 'M' || $product_data['main_category']) {
                    continue;
                }

                $product_data['main_category'] = (int) $link['category_id'];
            }

            $product_data['images'] = db_get_hash_array(
                'SELECT images.*, links.*, images.image_id AS images_image_id'
                . ' FROM ?:images_links AS links'
                . ' LEFT JOIN ?:images AS images ON images.image_id = links.detailed_id'
                . ' WHERE links.object_type = ?s AND links.object_id = ?i',
                'pair_id',
                'product',
                $product_id
            );
        }

        return $product_data;
    }

    /**
     * Finds product ID by product code and company ID
     *
     * @param string $product_code Product code
     * @param int    $company_id   Company ID
     *
     * @return int
     */
    public function findProductIdByProductCode($product_code, $company_id)
    {
        return (int) db_get_field(
            'SELECT product_id FROM ?:products WHERE product_code = ?s AND company_id = ?i',
            $product_code,
            $company_id
        );
    }

    /**
     * Finds category ID by category name and company ID
     *
     * @param string $name       Category name
     * @param int    $company_id Company ID
     *
     * @return int
     */
    public function findCategoryIdByName($name, $company_id)
    {
        $conditions = [
            'descriptions.category' => trim($name)
        ];

        if (fn_allowed_for('ULTIMATE')) {
            $conditions['company_id'] = $company_id;
        }

        return (int) db_get_field(
            'SELECT categories.category_id FROM ?:categories AS categories'
            . ' INNER JOIN ?:category_descriptions AS descriptions'
            .   ' ON descriptions.category_id = categories.category_id AND descriptions.lang_code = ?s'
            . ' WHERE ?w'
            . ' LIMIT 1',
            $this->default_language_code,
            $conditions
        );
    }

    /**
     * Finds currency data by code
     *
     * @param string $currency_code Currency code
     *
     * @return array{currency_code: string, description: string, coefficient: float}|null
     */
    public function findCurrency($currency_code)
    {
        /** @var array<string, array{currency_code: string, description: string, coefficient: float}> $currencies */
        $currencies = fn_get_currencies_list(['currency_code' => $currency_code], 'A');

        return isset($currencies[$currency_code]) ? (array) $currencies[$currency_code] : null;
    }

    /**
     * Gets language code
     *
     * @param string|null $lang_code Languge code
     *
     * @return string
     */
    private function getLangCode($lang_code = null)
    {
        return $lang_code === null ? $this->default_language_code : $lang_code;
    }
}
