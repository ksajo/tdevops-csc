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

namespace Tygh\Ym;

use Tygh\Enum\ProductTracking;
use Tygh\Registry;
use Tygh\Tygh;
use Tygh\Ym\Offers;
use Tygh\Tools\SecurityHelper;
use Tygh\Settings;
use Tygh\Addons\ProductVariations\Product\Manager as ProductManager;
use Tygh\Ym\Offers\ApparelSimple as ApparelSimple;
use Tygh\Ym\Offers\Apparel as Apparel;
use Tygh\Enum\YesNo;

class Yml2 implements IYml2
{

    const ITERATION_ITEMS = 100;
    const ITERATION_OFFERS = ITERATION_OFFERS;
    const IMAGES_LIMIT = 10;
    const ARCHIVES_LIMIT = 10;
    const CATEGORY_DELIMITER = '///';

    protected $company_id;
    protected $options = array();
    protected $lang_code = DESCR_SL;
    protected $offset = 0;
    protected $debug = false;
    protected $yml2_product_export = 0;
    protected $yml2_product_skip = 0;
    protected $price_id;
    protected $price_list;

    protected $offer = null;
    protected $offers = array();
    protected $log = null;

    protected $available_categories = array();
    protected $exclude_category_ids = array();
    protected $hidden_category_ids = array();
    protected $export_category_ids = array();
    protected $categories_list = array();

    protected $filename = 'ym';
    protected $filepath = '';
    protected $filepath_temp = '';

    protected $categories_current_id = 0;
    protected $yml_categories;
    protected $filepath_categories_temp;
    protected $filepath_body_temp;

    public function __construct($company_id, $price_id = 0, $lang_code = DESCR_SL, $offset = 0, $debug = false, $options = array())
    {
        $this->company_id = $company_id;
        $this->lang_code  = $lang_code;
        $this->offset     = (int) $offset;
        $this->debug      = $debug;

        if (!empty($price_id)) {
            $this->price_id = $price_id;
            $this->price_list = $this->getPriceList($price_id);

            if (!empty($options)) {
                $this->options = $options;
            } else {
                $this->options = $this->getOptions($price_id);
            }

            $this->log = $this->createLogger('csv', $price_id);

            $this->filepath = $this->getFilePath();
            $this->filepath_temp = $this->getTempFilePath();
        }

        $this->yml2_product_export = $this->getStorageData('yml2_product_export_' . $this->price_id);
        $this->yml2_product_skip = $this->getStorageData('yml2_product_skip_' . $this->price_id);

        if (!empty($this->options)) {
            $this->filename = $this->filename . '_' . $this->options['price_id'];

            $this->options['company_id'] = $this->company_id;

            if (empty($this->options['exclude_categories_not_logging'])) {
                $this->options['exclude_categories_not_logging'] = 'N';
            }

            if (!empty($this->options['export_categories'])) {
                $this->export_category_ids = explode(',', $this->options['export_categories']);
            }

            if (!empty($this->options['exclude_categories_ext'])) {
                $this->exclude_category_ids = explode(',', $this->options['exclude_categories_ext']);
            }

            if (!empty($this->options['hidden_categories'])) {
                $this->hidden_category_ids = explode(',', $this->options['hidden_categories']);
            }

            if (!empty($this->options['export_hidden_categories']) && $this->options['export_hidden_categories'] == 'Y' && !empty($this->options['hidden_categories_ext'])) {
                $hidden_category_ids_ext = explode(',', $this->options['hidden_categories_ext']);
                $this->export_category_ids = array_merge($this->export_category_ids, $hidden_category_ids_ext);
            }

            $this->options['offer_type_categories'] = $this->getYMLCategories('yml2_offer_type');
            $this->options['yml2_model_categories'] = $this->getYMLCategories('yml2_model');
            $this->options['yml2_type_prefix_categories'] = $this->getYMLCategories('yml2_type_prefix');

            $this->options['yml2_model_select'] = $this->getYMLCategories('yml2_model_select');
            foreach($this->options['yml2_model_select'] as $category_id => $select) {
                $select = explode('.', $select);

                $this->options['yml2_model_select'][$category_id] = array();
                if (!fn_is_empty($select)) {
                    $this->options['yml2_model_select'][$category_id]['type'] = $select[0];
                    $this->options['yml2_model_select'][$category_id]['value'] = $select[1];
                }
            }

            $this->options['yml2_type_prefix_select'] = $this->getYMLCategories('yml2_type_prefix_select');
            foreach($this->options['yml2_type_prefix_select'] as $category_id => $select) {
                $select = explode('.', $select);

                $this->options['yml2_type_prefix_select'][$category_id] = array();
                if (!fn_is_empty($select)) {
                    $this->options['yml2_type_prefix_select'][$category_id]['type'] = $select[0];
                    $this->options['yml2_type_prefix_select'][$category_id]['value'] = $select[1];
                }
            }
        }

        if ($this->debug) {
            fn_yml_stop_generate($this->price_id);
        }
    }

    public function get()
    {
        if ($this->debug) {
            $this->generate();
        }

        if (!file_exists($this->filepath)) {
            fn_echo(__('yml2_file_not_exist', array('[url]' => fn_url('yml.generate', 'C', 'http'))));
            return false;
        }

        $this->sendResult($this->filepath);
    }

    public function view()
    {
        $filename = $this->getCacheFileName();

        if (!file_exists($filename) || $this->debug) {
            $this->generate($filename);
        }

        $this->sendResult($filename);
    }

    protected function getPathDir()
    {
        $path = Registry::get('config.dir.files');

        if (!empty($this->company_id)) {
            $path .=  $this->company_id . '/';
        }

        fn_mkdir($path);

        return $path;
    }

    public function getFilePath()
    {
        return $this->getPathDir() . 'yml/' . $this->filename . '_' . $this->options['price_id'] . '.yml';
    }

    public function getTempFilePath()
    {
        return $this->getPathDir() . 'yml/' . $this->filename . '_' . $this->options['price_id'] . '_generation.yml';
    }

    public function getCacheFileName()
    {
        $this->options['price_id'] = isset($this->options['price_id']) ? $this->options['price_id'] : '';
        $path = sprintf('%syml/%s_' . $this->filename . '_' . $this->options['price_id'] . '.yml',
            fn_get_cache_path(false, 'C', $this->company_id),
            $this->company_id
        );

        return $path;
    }

    public function clearCache()
    {
        return fn_rm($this->getCacheFileName());
    }

    public static function clearCaches($company_ids = null)
    {

        if (is_null($company_ids)) {
            if (Registry::get('runtime.company_id') || Registry::get('runtime.simple_ultimate')) {
                $company_ids = Registry::get('runtime.company_data.company_id');
            } else {
                $company_ids = array_keys(fn_get_short_companies());
            }
        }

        $price_id = db_get_field("SELECT DISTINCT param_id FROM ?:yml_param WHERE param_type = 'price_list'");

        foreach ((array) $company_ids as $company_id) {
            $self = new self($company_id, $price_id);
            $self->clearCache();
        }
    }

    public function generate($filepath = '')
    {
        @ignore_user_abort(1);
        @set_time_limit(0);
        register_shutdown_function(array($this, 'shutdownHandler'));

        if (!empty($filepath)) {
            $this->filepath_temp = $filepath;
        }

        fn_mkdir(dirname($this->filepath_temp));

        $this->filepath_categories_temp = $this->filepath_temp . '.categories';
        $this->filepath_body_temp = $this->filepath_temp . '.body';

        $continue = false;
        if (file_exists($this->filepath_temp) && $this->offset > 0) {
            $continue = true;
        }

        if ($continue) {
            $this->log->write(Logs::INFO, '', 'Continue ' . date('d.m.Y H:i:s', time()) . '. Offset ' . $this->offset);

        } else {
            $status_generate = fn_get_storage_data('yml2_status_generate_' . $this->price_id);
            if ($status_generate == 'active' && file_exists($this->filepath_temp)) {
                fn_echo(__("yml_export.generation_was_started"));
                exit();
            }

            fn_rm($this->filepath_temp);
            fn_rm($this->filepath_categories_temp);
            fn_rm($this->filepath_body_temp);

            $this->offset = 0;

            $this->log->write(Logs::INFO, '', 'Start ' . date('d.m.Y H:i:s', time()));
            fn_set_storage_data('yml2_export_start_time_' . $this->price_id, time());
        }

        fn_set_storage_data('yml2_status_generate_' . $this->price_id, 'active');

        $file = fopen($this->filepath_temp, 'a+b');
        $file_categories = fopen($this->filepath_categories_temp, 'a+b');
        $file_body = fopen($this->filepath_body_temp, 'a+b');

        if (!$continue) {
            $this->head($file);
            if ($this->options['use_yml_categories']) {
                $this->buildYmlCategories($file_categories);
            }
            $this->available_categories = $this->getAvailableCategories();
        } else {
            $this->setCategoriesCurrentId(fn_get_storage_data('yml2_categories_current_id'));
            $this->available_categories = $this->getAvailableCategories();

            $xml_strings[] = trim(fgets($file_categories));

            while (!feof($file_categories)) {
                $xml_strings[] = trim(fgets($file_categories));
            }

            $this->setCategoriesList(array_merge($this->getCategoriesList(), $this->restoreCategoriesList($xml_strings)));
        }

        $this->body($file_body, $file_categories);

        $this->mergeExportFileParts($file, $file_categories, $file_body);

        $this->bottom($file);

        fclose($file);
        fclose($file_categories);
        fclose($file_body);

        $this->log->write(Logs::INFO, '', 'Finish ' .  date('d.m.Y H:i:s', time()));
        $this->log->write(Logs::INFO, '', 'Products exported: ' . $this->yml2_product_export . '. Products skipped: ' . $this->yml2_product_skip);

        $data = array(
            '[export]' => $this->yml2_product_export,
            '[skip]' => $this->yml2_product_skip,
            '[cron]' => defined('CONSOLE') ? 'Cron. ' : ''
        );

        fn_log_event('yml_export', 'export', array ('message' => __('text_log_action_export', $data)));

        if ($this->options['detailed_generation'] == 'Y') {

            $path = $this->log->getTempLogFile();

            if($path) {
                $log = fopen($path, 'r');
                $line = fgets($log);
                $info_line = true;

                while (!feof($log)) {
                    $line = fgets($log);

                    if (empty($line)) {
                        continue;
                    }

                    $data = explode(';', $line);

                    if ($data[0] == '[INFO]' && !$info_line) {
                        fn_echo(NEW_LINE);

                    } elseif ($data[0] != '[INFO]' && $info_line) {
                        fn_echo(NEW_LINE);
                    }

                    $data[1] = isset($data[1]) ? $data[1] : '';
                    $data[2] = isset($data[2]) ? $data[2] : '';

                    fn_echo($data[0] . $data[1] . $data[2] . NEW_LINE);

                    $info_line = ($data[0] == '[INFO]');
                }

                fclose($log);
            }
        }

        $this->log->rotate();

        if (empty($filepath)) {
            $this->backupYml();
            if (file_exists($this->filepath_temp)) {
                fn_rm($this->filepath);
                fn_rename($this->filepath_temp, $this->filepath);
            }
        }

        fn_set_storage_data('yml2_product_export_' . $this->price_id);
        fn_set_storage_data('yml2_product_skip_' . $this->price_id);

        fn_set_storage_data('yml2_export_start_time_' . $this->price_id);
        fn_set_storage_data('yml2_export_count_' . $this->price_id);
        fn_set_storage_data('yml2_export_offset_' . $this->price_id);

        fn_set_storage_data('yml2_export_time_' . $this->price_id, time());
        fn_set_storage_data('yml2_status_generate_' . $this->price_id, 'finish');
    }

    /**
     * Writes head in the file.
     *
     * @param resource $file File handler
     */
    protected function head($file)
    {
        $yml2_header = array(
            '<?xml version="1.0" encoding="' . $this->options['export_encoding'] . '"?>',
            '<!DOCTYPE yml2_catalog SYSTEM "shops.dtd">',
            '<yml_catalog date="' . date('Y-m-d H:i') . '">',
            '<shop>'
        );

        $yml2_data = $this->generateYml2Data();

        $this->buildCurrencies($yml2_data);

        if (!fn_is_empty($this->options['delivery_options'])) {
            foreach($this->options['delivery_options'] as $option) {
                $option_attr = 'option@cost=' . $option['cost'] . '@days=' . $option['days'];
                if (!empty($option['order_before'])) {
                    $option_attr .= '@order-before=' . $option['order_before'];
                }
                $yml2_data['delivery-options'][$option_attr] = '';
            }
        }

        if (!empty($this->options['enable_cpa']) && $this->options['enable_cpa'] == 'N') {
            $yml2_data['cpa'] = '0';
        }

        fwrite($file, implode(PHP_EOL, $yml2_header) . PHP_EOL);
        fwrite($file, fn_yml_array_to_yml($yml2_data));
    }

    /**
     * Generates the offers for file.
     *
     * @param resource $file_body       File body
     * @param resource $file_categories File categories
     */
    protected function body($file_body, $file_categories)
    {
        $this->generateOffers($file_body, $file_categories);
    }

    protected function bottom($file)
    {
        fwrite($file, '</shop>' . PHP_EOL);
        fwrite($file, '</yml_catalog>' . PHP_EOL);
    }

    protected function sendResult($filename)
    {
        header("Content-Type: text/xml;charset=" . $this->options['export_encoding']);

        readfile($filename);
        exit;
    }

    protected function getShopName()
    {
        $shop_name = $this->options['shop_name'];

        if (empty($shop_name)) {
            if (fn_allowed_for('ULTIMATE')) {
                $shop_name = fn_get_company_name($this->company_id);
            } else {
                $shop_name = Registry::get('settings.Company.company_name');
            }
        }



        return SecurityHelper::escapeHtml($shop_name);
    }

    protected function buildCurrencies(&$yml2_data)
    {
        $currencies = Registry::get('currencies');

        if (CART_PRIMARY_CURRENCY != "RUB" && CART_PRIMARY_CURRENCY != "UAH" && CART_PRIMARY_CURRENCY != "BYN" && CART_PRIMARY_CURRENCY != "KZT") {

            if (!empty($currencies['RUB'])) {
                $v_coefficient = $currencies['RUB']['coefficient'];
                $default_currencies = 'RUB';

            } elseif (!empty($currencies['UAH'])) {
                $v_coefficient = $currencies['UAH']['coefficient'];
                $default_currencies = 'UAH';

            } elseif (!empty($currencies['BYN'])) {
                $v_coefficient = $currencies['BYN']['coefficient'];
                $default_currencies = 'BYN';

            } elseif (!empty($currencies['KZT'])) {
                $v_coefficient = $currencies['KZT']['coefficient'];
                $default_currencies = 'KZT';

            } else {
                $v_coefficient = 1;
                $default_currencies = CART_PRIMARY_CURRENCY;
            }
            $primary_coefficient = $currencies[CART_PRIMARY_CURRENCY]['coefficient'];

            foreach ($currencies as $cur) {
                if ($this->currencyIsValid($cur['currency_code']) && $cur['status'] == 'A') {
                    if ($default_currencies == $cur['currency_code']) {
                        $coefficient = '1.0000';
                        $yml2_data['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $coefficient] = '';

                    } else {
                        $coefficient = $cur['coefficient'] * $primary_coefficient / $v_coefficient;
                        $yml2_data['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $coefficient] = '';
                    }
                }
            }

        } else {
            foreach ($currencies as $cur) {
                if ($this->currencyIsValid($cur['currency_code']) && $cur['status'] == 'A') {
                    $yml2_data['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $cur['coefficient']] = '';
                }
            }
        }
    }

    protected function currencyIsValid($currency)
    {
        $currencies = array(
            'RUR',
            'RUB',
            'UAH',
            'BYN',
            'KZT',
            'USD',
            'EUR'
        );

        return in_array($currency, $currencies);
    }

    /**
     * Builds the list categories for yml file.
     *
     * @param resource $file File categories
     *
     * @return void
     */
    protected function buildYmlCategories($file)
    {
        $categories_tree = array();
        $yml_categories = fn_get_schema('yml', 'categories');

        foreach ($yml_categories as $yml_category) {
            $yml_category = $this->convertCategoryToCategoryTreeItem($yml_category);
            $categories_tree = fn_array_merge($categories_tree, $yml_category);
        }

        $categories_tree = $this->convertToCategoriesTree($categories_tree);
        $this->setCategoriesList($this->flattenTree($categories_tree));

        foreach ($this->categories_list as $category) {
            $this->writeCategoryNode($category, $file);
        }
    }

    /**
     * Gets the categories array from string.
     *
     * @param string $string The category string.
     *
     * @return array tree.
     */
    public function convertCategoryToCategoryTreeItem($category_string)
    {
        $array = array();

        if (!$category_string) {
            return $array;
        }

        $str_parts = explode('/', $category_string);
        $head = array_shift($str_parts);
        $tail = implode('/', $str_parts);
        if (strpos($tail, '/') !== false) {
            $array[$head] = $this->convertCategoryToCategoryTreeItem($tail);
        } elseif ($tail) {
            $array[$head] = array($tail => array());
        } else {
            $array[$head] = array();
        }

        return $array;
    }

    /**
     * Converts array to categories tree.
     *
     * @param array    $categories   Array categories.
     * @param int|null $parent_id    Parent identifier.
     * @param string   $parent_path  Path to category.
     *
     * @return array categories tree.
     */
    public function convertToCategoriesTree(array $categories, &$parent_id = null, &$parent_path = '')
    {
        foreach ($categories as $category_name => $subcategories) {
            $path = $parent_path . self::CATEGORY_DELIMITER . $category_name;
            $path = ltrim($path, self::CATEGORY_DELIMITER);
            $this->categories_current_id++;
            $categories[$category_name] = array(
                'name'        => $category_name,
                'id'          => $this->categories_current_id,
                'parent_id'   => $parent_id,
                'path'        => $path,
                'parent_path' => $parent_path,
                'children'    => $subcategories,
            );

            if ($subcategories) {
                $parent_id = $categories[$category_name]['id'];
                $parent_path = $categories[$category_name]['path'];
                $categories[$category_name]['children'] = $this->convertToCategoriesTree(
                    $subcategories,
                    $parent_id,
                    $parent_path
                );
            }

            $parent_id = $categories[$category_name]['parent_id'];
            $parent_path = $categories[$category_name]['parent_path'];
        }

        return $categories;
    }

    /**
     * Gets flatten categories list.
     *
     * @param array $categories The categories tree
     *
     * @return array flatten categories list
     */
    public function flattenTree(array $categories)
    {
        $flat_list = array();

        foreach ($categories as $category) {
            $flat_list[$category['path']] = $this->formCategoryListItem($category['id'], $category['name'], $category['parent_id'], $category['path']);
            if ($category['children']) {
                $flat_list = array_merge($flat_list, $this->flattenTree($category['children']));
            }
        }

        return $flat_list;
    }

    /**
     * Adds manual category.
     *
     * @param string $category Path to category.
     *
     * @return array category list
     */
    public function addManualCategory($category)
    {
        $result = array();
        $is_categories_tree_changed = false;

        if (!empty($this->getCategoryFromCategoriesList($category))) {
            $result[] = $this->getCategoryFromCategoriesList($category);

            return array($result, $is_categories_tree_changed);
        }

        $manual_categories = explode(self::CATEGORY_DELIMITER, $category);
        $parent_path = '';
        $parent_id = null;

        foreach ($manual_categories as $category_name) {
            $path = $parent_path . self::CATEGORY_DELIMITER . $category_name;
            $path = ltrim($path, self::CATEGORY_DELIMITER);

            $parent_category = $this->getCategoryFromCategoriesList($path);
            if (!empty($parent_category)) {
                $parent_path = $path;
                $parent_id = $parent_category['id'];

            } else {
                $this->categories_current_id++;

                $result[] = $this->setCategoryToCategoriesList($this->categories_current_id, $category_name, $parent_id, $path);

                $parent_path = $path;
                $parent_id = $this->categories_current_id;

                $is_categories_tree_changed = true;
            }
        }

        return array($result, $is_categories_tree_changed);
    }

    /**
     * Adds the information about features to the array with the data of products.
     *
     * @param array $product_ids The identifiers of products.
     * @param array $products    The array with the data of products.
     *
     * @return array Array of products with additional information about features
     */
    protected function gatherProductsFeatures($products, $product_ids)
    {
        static $features = [];

        if (empty($features)) {
            $params = [
                'plain'          => true,
                'variants'       => true,
                'exclude_group'  => true,
                'variant_images' => false
            ];

            list($features, ) = fn_get_product_features($params);
        }

        $products_features_data = db_get_array(
            'SELECT * FROM ?:product_features_values WHERE product_id IN (?a) AND lang_code = ?s',
            $product_ids, CART_LANGUAGE
        );

        $products_features_values = array();
        $feature_variant_ids = array();

        foreach ($products_features_data as $feature) {
            $product_id = $feature['product_id'];
            $feature_id = $feature['feature_id'];

            $products_features_values[$product_id][$feature_id] = $feature;

            if (!empty($feature['variant_id'])) {
                $feature_variant_ids[$product_id][$feature_id][] = $feature['variant_id'];
            }
        }

        foreach ($feature_variant_ids as $product_id => $features_data) {
            foreach ($features_data as $feature_id => $variant_ids) {
                $products_features_values[$product_id][$feature_id]['variant_ids'] = $variant_ids;
            }
        }

        foreach ($products_features_values as $product_id => $features_values) {
            $products[$product_id]['product_features'] = $this->getProductFeatures($features_values, $features);
        }

        return $products;
    }

    /**
     * Gets the names and values of features for the specific product.
     *
     * @param array $product_features_data  The array with IDs of features and their variants selected for the product.
     * @param array $features               The array with the data of all existing features and their variants.
     *
     * @return array The array with the names of features and their variants for the specific product.
     */
    protected function getProductFeatures($product_features_data, $features)
    {
        $product_features = array();
        foreach ($product_features_data as $feature_id => $feature_data) {
            if (empty($features[$feature_id])) {
                continue;
            }

            $f = $features[$feature_id];

            if (in_array($this->options['price_id'], $f['yml2_exclude_prices'])) {
                continue;
            }

            $feature = array(
                'description' => $f['description'],
                'feature_id' => $f['feature_id'],
                'yml2_unit' => trim($f['yml2_variants_unit'])
            );

            $feature['is_visible'] = ($f['display_on_catalog'] == "Y" || $f['display_on_product'] == "Y" || $f['display_on_header'] == 'Y');

            $ft = $f['feature_type'];

            if ($ft == "C") {
                $feature['value'] = ($feature_data['value'] == "Y") ? __("yes") : __("no");

            } elseif (($ft == "S" || $ft == "N" || $ft == "E") && !empty($feature_data['variant_id'])) {

                $variant = $f['variants'][$feature_data['variant_id']];
                $feature['value'] = $variant['variant'];

                if (!empty($variant['yml2_unit'])) {
                    $feature['yml2_unit'] = trim($variant['yml2_unit']);
                }

            } elseif ($ft == "T" && !empty($feature_data['value'])) {
                $feature['value'] = $feature_data['value'];

            } elseif ($ft == "M") {
                if (!empty($f['variants'])) {
                    $_value = '';
                    $counter = count($f['variants']);
                    foreach ($f['variants'] as $_variant) {

                        if (!in_array($_variant['variant_id'], $feature_data['variant_ids'])) {
                            continue;
                        }

                        if ($counter > 1) {
                            $_value .= $_variant['variant'] . ', ';
                        } else {
                            $_value = $_variant['variant'];
                        }
                    }

                    $feature['value'] = ($counter > 1) ? substr($_value, 0, -2) : $_value;
                }

            } elseif ($ft == "O") {
                $feature['value'] = $feature_data['value_int'];

            } elseif ($ft == 'D') {
                $feature['value'] = $this->formatDate((int) $feature_data['value_int']);
            }

            $product_features[] = $feature;
        }

        return $product_features;
    }

    protected function escape($data)
    {
        $data = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $data);

        return strip_tags($data);
    }

    /**
     * Generates product list to body file.
     *
     * @param resource $file_body        File body.
     * @param resource $file_categories  File categories.
     *
     * @return boolean generate of products
     */
    protected function generateOffers($file_body, $file_categories)
    {
        $fields = array(
            'p.product_id',
            'p.product_code',
            'd.lang_code',
            'pc.category_id',
            'cd.category',
            'pp.price',
            'p.list_price',
            'p.status',
            'p.amount',
            'p.weight',
            'p.shipping_freight',
            'p.shipping_params',
            'p.free_shipping',
            'd.product',
            'd.short_description',
            'd.full_description',
            'p.company_id',
            'p.tracking',
            'p.list_price',
            'p.is_edp',
            'p.yml2_brand',
            'p.yml2_origin_country',
            'p.yml2_store',
            'p.yml2_pickup',
            'p.yml2_delivery',
            'p.yml2_delivery_options',
            'p.yml2_bid',
            'p.yml2_cbid',
            'p.yml2_fee',
            'p.yml2_model',
            'p.yml2_sales_notes',
            'p.yml2_type_prefix',
            'p.yml2_offer_type',
            'p.yml2_market_category',
            'p.yml2_manufacturer_warranty',
            'p.yml2_expiry',
            'p.yml2_purchase_price',
            'p.yml2_description',
            'p.yml2_cpa',
            'p.yml2_adult',
            'p.product_type'
        );

        $company_ordering = '';
        if (!empty($this->company_id)) {
            $company_ordering = db_quote('c.company_id = ?i DESC,', $this->company_id);
        }

        $fields[] = db_quote(
            '(SELECT'
            . ' GROUP_CONCAT('
                . 'IF(pc2.link_type = ?s, CONCAT(pc2.category_id, ?s), pc2.category_id)'
                . ' ORDER BY ?p (pc2.link_type = ?s) DESC,'
                . ' pc2.category_position ASC,'
                . ' pc2.category_id ASC'
            . ') as category_ids'
            . ' FROM ?:products_categories as pc2'
            . ' LEFT JOIN ?:categories as c ON pc2.category_id = c.category_id'
            . ' WHERE product_id = p.product_id AND c.status IN (?a)) as category_ids',
            'M',
            'M',
            $company_ordering,
            'M',
            ['A', 'H']
        );

        $joins = array(
            db_quote(
                "LEFT JOIN ?:product_descriptions as d ON d.product_id = p.product_id AND d.lang_code = ?s",
                $this->lang_code
            ),
            db_quote(
                "LEFT JOIN ?:product_prices as pp"
                . " ON pp.product_id = p.product_id AND pp.lower_limit = 1 AND pp.usergroup_id = 0"
            ),
            db_quote(
                "LEFT JOIN ?:products_categories as pc ON pc.product_id = p.product_id AND pc.link_type = ?s",
                'M'
            ),
            db_quote(
                "LEFT JOIN ?:category_descriptions as cd ON cd.category_id = pc.category_id AND cd.lang_code = ?s",
                $this->lang_code
            )
        );

        $exclude_products_ids = db_get_fields(
            "SELECT DISTINCT object_id FROM ?:yml_exclude_objects WHERE price_id = ?i AND object_type = 'product'",
            $this->price_id
        );

        if ($this->options['exclude_categories_not_logging'] == 'Y' && !empty($this->exclude_category_ids)) {
            $join = 'INNER JOIN ?:categories as c ON pc.category_id = c.category_id';
            $condition = db_quote(' AND pc.category_id IN (?a)', $this->exclude_category_ids);
            $condition .= db_quote(' AND link_type = ?s AND status = ?s', 'M', 'A');

            $exclude_products_ids += db_get_fields(
                'SELECT DISTINCT product_id FROM ?:products_categories as pc ?p WHERE 1 ?p', $join, $condition
            );
        }

        $condition = '';
        if ($this->company_id > 0) {
            $condition .= db_quote(' AND company_id = ?i', $this->company_id);
        }

        if (!empty($exclude_products_ids)) {
            $condition .= db_quote(' AND product_id NOT IN (?a)', $exclude_products_ids);
        }

        $product_ids = db_get_fields("SELECT product_id FROM ?:products WHERE status = ?s $condition", 'A');

        fn_set_storage_data('yml2_export_count_' . $this->price_id, count($product_ids));

        $shared_product_ids = array();
        if (isset($this->options['export_shared_products']) && $this->options['export_shared_products'] == 'Y') {
            $categories_join = db_quote('INNER JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id');
            $products_join = db_quote('INNER JOIN ?:products ON ?:products.product_id = ?:products_categories.product_id');
            $condition = '';
            if (!empty($exclude_products_ids)) {
                $condition = db_quote('AND ?:products.product_id NOT IN (?n)', $exclude_products_ids);
            }
            $shared_product_ids = db_get_fields(
                "SELECT DISTINCT ?:products_categories.product_id FROM ?:products_categories $categories_join $products_join " .
                "WHERE ?:categories.company_id = ?i AND link_type = ?s AND ?:products.status = ?s ?p",
                $this->company_id, 'A', 'A', $condition
            );

            $shared_product_ids = array_diff($shared_product_ids, $product_ids);
            $product_ids = array_merge($product_ids, $shared_product_ids);
        }

        $this->offer = new Offers($this->options, $this->log);

        $offers_count = 0;
        $this->yml2_product_skip = 0;

        while ($ids = array_slice($product_ids, $this->offset, self::ITERATION_ITEMS)) {
            $processed = 0;
            $this->offset += self::ITERATION_ITEMS;
            $products = db_get_hash_array(
                'SELECT ' . implode(', ', $fields)
                . ' FROM ?:products as p'
                . ' ' . implode(' ', $joins)
                . ' WHERE p.product_id IN (?n)'
                . ' GROUP BY p.product_id'
                , 'product_id', $ids
            );

            $products_images_main = fn_get_image_pairs($ids, 'product', 'M', false, true, $this->lang_code);
            $products_images_additional = fn_get_image_pairs($ids, 'product', 'A', false, true, $this->lang_code);

            $params = [
                'get_options'        => false,
                'get_taxed_prices'   => false,
                'detailed_params'    => false,
            ];

            /**
             * @param \Tygh\Ym\Yml2 $this
             * @param array         $products
             * @param array         $params
             */
            fn_set_hook('yml_export_generate_offers_before_gather_additional_products_data', $this, $products, $params);

            fn_gather_additional_products_data($products, $params);

            $products = $this->gatherProductsFeatures($products, $ids);

            foreach ($products as $k => &$product) {

                $processed++;
                if (in_array($product['product_id'], $shared_product_ids)) {
                    $this->prepareSharedProduct($product);
                }

                if (!$this->preBuild($product, $products_images_main, $products_images_additional)) {
                    $this->yml2_product_skip++;

                    continue;
                }

                $is_category_found = false;
                $manually_set_category = '';
                if ($product['yml2_market_category'] && $this->options['use_yml_categories']) {
                    $yml_category = $this->normalizeCategoryPath($product['yml2_market_category']);
                    $category = $this->getCategoryFromCategoriesList($yml_category);
                    if (!empty($category)) {
                        $product['category_id'] = $category['id'];
                        $is_category_found = true;
                    } else {
                        $manually_set_category = $yml_category;
                    }
                }

                if (!$is_category_found && $this->available_categories[$product['category_id']]) {
                    $yml_category = $this->normalizeCategoryPath($this->available_categories[$product['category_id']]);
                    $category = $this->getCategoryFromCategoriesList($yml_category);
                    if (!empty($category)) {
                        $product['category_id'] = $category['id'];
                        $is_category_found = true;
                    } else {
                        $manually_set_category = $yml_category;
                    }
                }

                if (!$is_category_found && !$manually_set_category) {
                    /** @var string $path */
                    $path = db_get_field(
                        'SELECT id_path FROM ?:categories WHERE category_id = ?i',
                        $product['category_id']
                    );

                    $category_parents = explode('/', $path);

                    $name_parts = fn_get_category_name($category_parents, $this->lang_code);
                    $category_name = array();
                    foreach ($category_parents as $category_id) {
                        $category_name[] = $name_parts[$category_id];
                    }

                    list($is_category_found, $manually_set_category, $category_id) = $this->getMarketCategoryFromParentCategories($category_parents);

                    $product['category_id'] = isset($category_id) ? $category_id : $product['category_id'];

                    if (!$is_category_found && !$manually_set_category) {
                        $manually_set_category = implode(self::CATEGORY_DELIMITER, $category_name);
                    }
                }

                if (!$is_category_found && $manually_set_category) {
                    list($new_categories, $is_categories_tree_changed) = $this->addManualCategory($manually_set_category);

                    if ($is_categories_tree_changed) {
                        foreach ($new_categories as $new_category) {
                            $this->writeCategoryNode($new_category, $file_categories);
                        }
                    }

                    $new_category = end($new_categories);
                    $product['category_id'] = $new_category['id'];
                }

                list($xml, $product_skip) = $this->offer->build($product);

                if (empty($product_skip)) {
                    $this->yml2_product_export++;
                } else {
                    $this->yml2_product_skip += $product_skip;
                }

                $this->stopGeneration();

                fwrite($file_body, $xml . "\n");

                if ($processed % static::ITERATION_ITEMS == 0) {
                    fn_echo(__('yml_export.products_processed', array(
                        '[items]' => $this->offset + $processed
                    )) . NEW_LINE);
                }
            }

            $offers_count += count($products);

            fn_set_storage_data('yml2_export_offset_' . $this->price_id, $this->offset);
            fn_set_storage_data('yml2_categories_current_id', $this->categories_current_id);

            if (!defined('CONSOLE') && $offers_count >= self::ITERATION_OFFERS) {
                fn_set_storage_data('yml2_product_export_' . $this->price_id, $this->yml2_product_export);
                fn_set_storage_data('yml2_product_skip_' . $this->price_id, $this->yml2_product_skip);
                fclose($file_body);
                fn_set_storage_data('yml2_status_generate_' . $this->price_id, 'redirect');
                fn_redirect(fn_yml_get_generate_link($this->price_list) . "/" . $this->offset);
            }
        }

        return true;
    }

    public function preBuild(&$product, $products_images_main, $products_images_additional)
    {
        $is_broken = false;

        if ($this->options['export_null_price'] == 'N') {
            $price = !floatval($product['price']) ? fn_parse_price($product['price']) : intval($product['price']);
            if (empty($price)) {
                $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_product_price_is_empty'));
                $is_broken = true;
            }
        }

        $product['category_id'] = $this->getProductCategory($product);
        $product['yml2_offer_type'] = $this->offer->getOfferType($product);
        if ($product['category_id'] === false) {
            $is_broken = true;
        }

        $product['product'] = $this->escape($product['product']);
        $product['full_description'] = $this->escape($product['full_description']);
        $product['short_description'] = $this->escape($product['short_description']);

        if ($this->options['export_stock'] == 'Y') {
            if ($product['tracking'] !== ProductTracking::DO_NOT_TRACK && $product['amount'] <= 0) {
                $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_product_amount_is_empty'));
                $is_broken = true;
            }
        }

        if (!empty($this->options['export_min_product_price'])) {
            if ($product['price'] < $this->options['export_min_product_price']) {
                $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_product_price_below_min'));
                $is_broken = true;
            }
        }

        if (!empty($this->options['export_max_product_price'])) {
            if ($product['price'] > $this->options['export_max_product_price']) {
                $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_product_price_above_max'));
                $is_broken = true;
            }
        }

        if (!$this->offer->preBuild($product)) {
            $is_broken = true;
        }

        if ($is_broken) {
            return false;
        }

        if (!empty($this->options['utm_link'])) {
            $product['product_url'] = $this->getUTMLink($product, $this->options['utm_link'], 'products.view?product_id=' . $product['product_id']);
        } else {
            $product['product_url'] = 'products.view?product_id=' . $product['product_id'];
        }

        // Images
        if (empty($product['images'])) {
            $images = array_merge(
                $products_images_main[$product['product_id']],
                $products_images_additional[$product['product_id']]
            );

            $product['images'] = array_slice($images, 0, self::IMAGES_LIMIT);
        }

        return true;
    }

    public function backupYml()
    {
        if (file_exists($this->filepath)) {
            $path = fn_get_files_dir_path() . 'yml/';
            $archive_path = $path . 'archives/';
            fn_mkdir($archive_path);

            $archive_name = 'ym_' . date('dmY_His', TIME) . '.tgz';
            fn_compress_files($archive_name, $this->filename . '.yml', $path);
            fn_rename($path . $archive_name, $archive_path . $archive_name);

            $archives_list = fn_get_dir_contents($archive_path, false, true);
            if (!empty($archives_list) && count($archives_list) > self::ARCHIVES_LIMIT) {
                rsort($archives_list);
                list(, $old_archives) = array_chunk($archives_list, self::ARCHIVES_LIMIT);
                foreach($old_archives as $filename) {
                    fn_rm($archive_path . $filename);
                }
            }
        }
    }

    protected function getUTMLink($product, $utm, $product_url)
    {
        preg_match_all('/\{(.+?)\}/is', $utm, $words, PREG_OFFSET_CAPTURE);

        if (!empty($words[1])) {

            $replace_words = array();

            foreach($words[1] as $index => $word_data) {
                list($word) = $word_data;
                if (isset($product[$word])) {
                    $replace_words[$word] = $product[$word];
                }
            }

            foreach($replace_words as $word => $value) {
                $utm = str_replace("{" . $word . "}", $value, $utm);
            }
        }

        return $product_url . "&" . $utm;
    }

    protected function getYMLCategories($field_name)
    {
        $offer_type_categories = array();
        $categories = db_get_hash_array("SELECT category_id, parent_id, $field_name FROM ?:categories", 'category_id');

        foreach(array_keys($categories) as $category_id) {
            if (empty($categories[$category_id][$field_name])) {
                $offer_type_categories[$category_id] = $this->getYmlField($categories[$category_id]['parent_id'], $categories, $field_name);
            } else {
                $offer_type_categories[$category_id] = $categories[$category_id][$field_name];
            }
        }

        return $offer_type_categories;
    }

    protected function getYmlField($category_id, &$categories, $field_name)
    {
        if (empty($categories[$category_id][$field_name])) {

            if (!empty($categories[$category_id]['parent_id'])) {
                $categories[$category_id][$field_name] = $this->getYmlField($categories[$category_id]['parent_id'], $categories, $field_name);
            } else {
                $categories[$category_id][$field_name] = '';
            }
        }

        return $categories[$category_id][$field_name];
    }

    protected function stopGeneration()
    {
        $status = db_get_field('SELECT `data` FROM ?:storage_data WHERE `data_key` = ?s', 'yml2_status_generate_' . $this->price_id);
        if ($status != 'active') {
            fn_set_storage_data('yml2_status_generate_' . $this->price_id, 'abort');

            if (file_exists($this->filepath_temp)) {
                fn_rm($this->filepath_temp);
            }

            fn_echo(__("yml_export.stop_generate"));
            exit();
        }
    }

    public function shutdownHandler()
    {
        $status = db_get_field('SELECT `data` FROM ?:storage_data WHERE `data_key` = ?s', 'yml2_status_generate_' . $this->price_id);
        fn_set_storage_data('yml2_export_time_' . $this->price_id, time());

        if ($status != 'redirect' || $status != 'finish') {

        }
    }

    public function prepareSharedProduct(&$product)
    {
        if (fn_allowed_for('ULTIMATE') && $this->company_id) {
            $table_name = '?:ult_product_prices';
            $condition = db_quote(' AND prices.company_id = ?i', $this->company_id);
        } else {
            $table_name = '?:product_prices';
            $condition = '';
        }

        $price_data = db_get_row("SELECT DISTINCT prices.product_id, prices.lower_limit, usergroup_id, "
                                 . " IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100) as price "
                                 . " FROM $table_name prices WHERE prices.product_id = ?i $condition AND prices.usergroup_id IN (?n) ORDER BY lower_limit",
                                 $product['product_id'], array_merge(array(USERGROUP_ALL), $_SESSION['auth']['usergroup_ids']));

        if (!empty($price_data)) {
            $product['price'] = $product['base_price'] = fn_format_price($price_data['price']);
        }

        $company_product_data = db_get_row("SELECT * FROM ?:ult_product_descriptions WHERE product_id = ?i AND company_id = ?i AND lang_code = ?s",
                                           $product['product_id'], $this->company_id, DESCR_SL);
        if (!empty($company_product_data)) {
            unset($company_product_data['company_id']);
            $product = array_merge($product, $company_product_data);
        }

        $product['category_ids'] = array_intersect($product['category_ids'], $this->export_category_ids);
        $product['category_id'] = reset($product['category_ids']);
    }

    /**
     * Gets category id from exporting product
     *
     * @param array $product Product data
     * @return int Category id
     */
    protected function getProductCategory($product)
    {
        $export_category_ids = array_intersect($product['category_ids'], array_keys($this->available_categories));
        $exclude_category_ids = array_intersect($export_category_ids, $this->exclude_category_ids);
        $export_category_ids = array_diff($export_category_ids, $this->exclude_category_ids);
        sort($export_category_ids);

        if (!empty($product['category_id'])) {
            $category_id = $product['category_id'];
        } elseif (!empty($product['main_category'])) {
            $category_id = $product['main_category'];
        } else {
            $category_id = reset($export_category_ids);
        }

        if (in_array($category_id, $exclude_category_ids)) {
            $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_category_excluded'));
            $category_id = false;

        } elseif (empty($export_category_ids)) {
            $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_category_not_visible'));
            $category_id = false;

        } elseif (empty($category_id)) {
            $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_product_category_is_empty'));
            $category_id = false;

        } elseif (!in_array($category_id, $export_category_ids)) {
            $category_id = reset($export_category_ids);
        }

        return $category_id;
    }

    /**
     * Creates logger.
     *
     * @param string $format   Log format
     * @param int    $price_id Price list ID
     *
     * @return \Tygh\Ym\Logs
     */
    protected function createLogger($format = 'csv', $price_id = 0)
    {
        return new Logs($format, $price_id);
    }

    /**
     * Wraper for ::fn_yml_get_price_list.
     *
     * @param int $price_id Price list ID
     *
     * @return array Price list
     */
    protected function getPriceList($price_id)
    {
        return fn_yml_get_price_list($price_id);
    }

    /**
     * Wrapper for ::fn_yml_get_options.
     *
     * @param int $price_id Price list ID
     *
     * @return array|bool Parameters' options
     */
    protected function getOptions($price_id)
    {
        return fn_yml_get_options($price_id);
    }

    /**
     * Wrapper for ::fn_get_storage_data.
     *
     * @param string $key Key
     *
     * @return mixed Value
     */
    protected function getStorageData($key)
    {
        return fn_get_storage_data($key);
    }

    /**
     * Formats value of a Date feature accordingly to Appearance > Date format.
     *
     * @param int $timestamp Unix timestamp
     *
     * @return string Formatted value
     */
    protected function formatDate($timestamp)
    {
        return \Tygh::$app['formatter']->asDatetime($timestamp, Registry::get('settings.Appearance.date_format'));
    }

    /**
     * Generates the parameters for the exporting products.
     *
     * @return array The array of parameters for exporting products.
     */
    protected function generateYml2Data()
    {
        $secure_storefront = Settings::instance()->getSettingDataByName('secure_storefront');

        $yml2_data = array(
            'name' => $this->getShopName(),
            'company' => SecurityHelper::escapeHtml(Registry::get('settings.Company.company_name')),
            'url' => ($secure_storefront['value'] === YesNo::YES) ? Registry::get('config.https_location') : Registry::get('config.http_location'),
            'platform' => PRODUCT_NAME,
            'version' => PRODUCT_VERSION,
            'agency' => 'Agency',
            'email' => Registry::get('settings.Company.company_orders_department'),
        );

        return $yml2_data;
    }

    /**
     * Checks if the product's offer type is apparel
     *
     * @param array $product The data of the product.
     *
     * @return boolean True - the product's offer type is apparel.
     */
    protected function isApparelProduct($product)
    {
        $offer = new Offers($this->options, $this->log);
        $offer = $offer->getOfferType($product);

        if ($offer == 'apparel_simple' || $offer == 'apparel') {
            return true;
        }

        return false;
    }

    /**
     * Gets Yandex.Market categories array.
     *
     * @param array $category_ids Categories identifiers
     *
     * @return array categories
     */
    protected function getYmlMarketCategories(array $category_ids)
    {
        $categories_to_fetch = $category_ids;

        foreach ($categories_to_fetch as $i => $category_id) {
            if (isset($this->available_categories[$category_id])) {
                unset($categories_to_fetch[$i]);
            }
        }

        if ($categories_to_fetch) {
            $fetched_categories = db_get_hash_single_array(
                'SELECT category_id, yml2_market_category'
                . ' FROM ?:categories'
                . ' WHERE category_id IN (?a)',
                array('category_id', 'yml2_market_category'),
                $categories_to_fetch
            );

            $this->available_categories = fn_array_merge(
                $this->available_categories,
                $fetched_categories
            );
        }

        $result = array();
        reset($category_ids);
        foreach ($category_ids as $category_id) {
            $result[$category_id] = $this->available_categories[$category_id];
        }

        return $result;
    }

    /**
     * Sets categories list.
     *
     * @param array $tree Categories tree
     *
     * @return void
     */
    public function setCategoriesList(array $tree)
    {
        $this->categories_list = $tree;
    }

    /**
     * Sets categories current id.
     *
     * @param int $categories_current_id Categories current id
     *
     * @return void
     */
    public function setCategoriesCurrentId($categories_current_id)
    {
        $this->categories_current_id = $categories_current_id;
    }

    /**
     * Gets categories list.
     *
     * @return array categories list
     */
    public function getCategoriesList()
    {
        return $this->categories_list;
    }

    /**
     * Merges file parts in the general file.
     *
     * @param resource $file            File for Yandex.Market
     * @param resource $file_categories File categories
     * @param resource $file_body       File body
     */
    protected function mergeExportFileParts($file, $file_categories, $file_body)
    {
        fseek($file_categories, 0);
        fseek($file_body, 0);

        fputs($file, '<categories>' . PHP_EOL);
        while($str = fgets($file_categories)) {
            fputs($file, $str);
        }
        fputs($file, '</categories>' . PHP_EOL);

        fputs($file, '<offers>' . PHP_EOL);
        while($str = fgets($file_body)) {
            fputs($file, $str);
        }
        fputs($file, '</offers>' . PHP_EOL);
    }

    /**
     * Restores categories list from xml file.
     *
     * @param array $xml_strings Categories from xml file
     *
     * @return array categories list
     */
    public function restoreCategoriesList(array $xml_strings)
    {
        $categories_list = array();

        foreach ($xml_strings as $str) {
            if (!empty($str)) {
                $node = simplexml_load_string($str);

                $category_id = (int) $node->attributes()->id;
                $parent_id = (int) $node->attributes()->parentId;
                $path = $category_name = (string) $node;

                if (!empty($parent_id) && !empty($categories_list[$parent_id])) {
                    $path = $categories_list[$parent_id]['path'] . self::CATEGORY_DELIMITER . $category_name;
                }

                $categories_list[$category_id] = $this->formCategoryListItem($category_id, $category_name, empty($parent_id) ? null : $parent_id, $path);
            }
        }

        $categories_list = array_combine(array_column($categories_list, 'path'), $categories_list);

        return $categories_list;
    }

    /**
     * Writes category to file.
     *
     * @param array    $category Category data
     * @param resource $file     File categories
     */
    protected function writeCategoryNode($category, $file)
    {
        $node_description = 'category'
            . '@id=' . $category['id']
            . ($category['parent_id']
                ? '@parentId=' . $category['parent_id']
                : ''
            );

        $node = array(
            $node_description => SecurityHelper::escapeHtml($category['name']),
        );

        $node_xml = fn_array_to_xml($node);
        fwrite($file, $node_xml . PHP_EOL);
    }

    /**
     * Gets the available categories.
     *
     * @return array the available categories in the store.
     */
    protected function getAvailableCategories()
    {
        if ($this->options['export_hidden_categories'] == 'Y') {
            $status = array('A', 'H');
        } else {
            $status = array('A');
        }

        $available_categories =  db_get_hash_single_array(
            'SELECT category_id, yml2_market_category'
            . ' FROM ?:categories'
            . ' WHERE status IN (?a)',
            array('category_id', 'yml2_market_category'),
            $status
        );

        if (!$this->options['use_yml_categories']) {
            array_walk($available_categories, function(&$category) {
                $category = '';
            });
        }

        return $available_categories;
    }

    /**
     * Gets category for Yandex.Market from parent categories.
     *
     * @param array   $category_parents      Category parents
     *
     * @return array Is the category find and the manually category value.
     */
    protected function getMarketCategoryFromParentCategories(array $category_parents)
    {
        $category_id = null;
        $is_category_found = false;
        $manually_set_category = '';

        $category_parents = array_reverse($category_parents);
        array_shift($category_parents);

        $category_parents = $this->getYmlMarketCategories($category_parents);
        foreach ($category_parents as $c_id => $market_category) {
            if (!$market_category) {
                continue;
            }
            $yml_category = $this->normalizeCategoryPath($this->available_categories[$c_id]);
            $category = $this->getCategoryFromCategoriesList($yml_category);
            if (!empty($category)) {
                $category_id = $category['id'];
                $is_category_found = true;
            } else {
                $manually_set_category = $yml_category;
            }
        }

        return array($is_category_found, $manually_set_category, $category_id);
    }

    /**
     * Change '/' category delimiter to '///'.
     *
     * @param string   $category_path      Category full path
     *
     * @return string category_path with '///' delimiter.
     */
    protected function normalizeCategoryPath($category_path)
    {
        return str_replace('/', self::CATEGORY_DELIMITER, $category_path);
    }

    /**
     * Get category data from Yandex.Maket categories list.
     *
     * @param string   $category_path      Category full path
     *
     * @return array   categories data.
     */
    protected function getCategoryFromCategoriesList($category_path)
    {
        $category = [];
        if (!empty($this->categories_list[$category_path])) {
            $category = $this->categories_list[$category_path];
        }

        return $category;
    }

    /**
     * Form array for adding to the categories list.
     *
     * @param int      $id            Added category id
     * @param string   $category_name Category name
     * @param int      $parent_id     Parent category id
     * @param string   $path          Category full path
     *
     * @return array   Element of the list.
     */
    protected function formCategoryListItem($id, $category_name, $parent_id, $path)
    {
        return [
            'name'      => $category_name,
            'path'      => $path,
            'parent_id' => $parent_id,
            'id'        => $id
        ];
    }

    /**
     * Set category data to Yandex.Maket categories list.
     *
     * @param int      $id            Added category id
     * @param string   $category_name Category name
     * @param int      $parent_id     Parent category id
     * @param string   $path          Category full path
     *
     * @return array   Category item which was added to the list.
     */
    protected function setCategoryToCategoriesList($id, $category_name, $parent_id, $path)
    {
        return $this->categories_list[$path] = $this->formCategoryListItem($id, $category_name, $parent_id, $path);
    }
}
