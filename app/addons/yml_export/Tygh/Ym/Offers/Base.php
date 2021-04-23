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

namespace Tygh\Ym\Offers;

use Tygh\Tools\SecurityHelper;
use Tygh\Storage;
use Tygh\Registry;
use Tygh\Enum\ProductTracking;

class Base
{
    protected $options = array();
    protected $schema = array();
    protected $features = array();
    protected $offer = array();
    protected $offer_type = '';
    protected $log = null;

    protected $common_features = array(
        'vendor' => array(
            'product_fields' => array('yml2_brand'),
            'feature_types' => array('E'),
        ),
        'description' => array(
            'product_fields' => array(
                'yml2_description',
                'full_description',
                'short_description'
            ),
        ),
        'vendorCode' => array(
            'product_fields' => array('product_code'),
        ),
        'model' => array(
            'product_fields' => array(
                'yml2_model',
                'product_code',
                'product'
            ),
        ),
        'typePrefix' => array(
            'product_fields' => array(
                'yml2_type_prefix',
                'category',
                'short_description',
                'product'
            ),
        ),
        'age',
        'barcode',
    );

    public function __construct($options = array(), $log = null)
    {
        $this->options = $options;
        $this->log = $log;
    }

    public function xml($product)
    {
        $yml2_product_skip = 0;
        $this->offer = $this->build($product);
        $this->gatherAdditional($product);

        $xml = $this->offerToXml($this->offer);

        if (!$this->postBuild($xml, $product, $this->offer)) {
            $yml2_product_skip++;
        }

        return array($xml, $yml2_product_skip);
    }

    public function gatherAdditional($product)
    {
        $this->gatherAdditionalExt($product);

        return true;
    }

    public function gatherAdditionalExt($product)
    {
        return true;
    }

    public function preBuild($product)
    {
        return true;
    }

    public function postBuild($xml, $product, $offer_data)
    {
        return true;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function getFeatures()
    {
        return $this->features;
    }

    public function getCommonFeatures()
    {
        return $this->common_features;
    }

    public function build($product)
    {
        $category_id = $product['category_id'];

        $available = 'true';
        if ($product['tracking'] !== ProductTracking::DO_NOT_TRACK && $product['amount'] <= 0) {
            $available = 'false';
        }

        $offer = array(
            'attr' => array(
                'id' => $product['product_id'],
                'available' => $available
            ),
            'items' => array(
                'url' => $this->escapeUrl($product['product_url']),
                'price' => !empty($product['price']) ? fn_format_price($product['price']) : '0',
                'currencyId' => !empty($currency) ? $currency['currency_code'] : CART_PRIMARY_CURRENCY,
                'categoryId' => $category_id
            )
        );

        if (!empty($product['yml2_store'])) {
            $offer['items']['store'] = $product['yml2_store'] == 'Y' ? 'true' : 'false';

        } elseif (!empty($this->options['store'])) {
            $offer['items']['store'] = $this->options['store'] == 'Y' ? 'true' : 'false';
        }

        if (!empty($product['yml2_pickup'])) {
            $offer['items']['pickup'] = $product['yml2_pickup'] == 'Y' ? 'true' : 'false';

        } elseif (!empty($this->options['pickup'])) {
            $offer['items']['pickup'] = $this->options['pickup'] == 'Y' ? 'true' : 'false';
        }

        if (!empty($product['yml2_delivery'])) {
            $offer['items']['delivery'] = $product['yml2_delivery'] == 'Y' ? 'true' : 'false';

        } elseif (!empty($this->options['delivery'])) {
            $offer['items']['delivery'] = $this->options['delivery'] == 'Y' ? 'true' : 'false';
        }

        if (!empty($product['base_price']) && $product['price'] < $product['base_price']) {
            $offer['items']['oldprice'] = $product['base_price'];
        } elseif (!empty($product['list_price']) && $product['price'] < $product['list_price']) {
            $offer['items']['oldprice'] = $product['list_price'];
        }

        if (!empty($offer['items']['oldprice'])) {
            $percent_discount = 100 / $offer['items']['oldprice'] * ($offer['items']['oldprice'] - $offer['items']['price']);

            if ($percent_discount < $this->options['minimal_discount']) {
                unset($offer['items']['oldprice']);
            }
        }

        // Images
        while ($image = array_shift($product['images'])) {
            $url = $this->getImageUrl($image);
            if (!empty($url)) {
                $offer['items']['picture'][] = $url;
            }
        }

        if (!empty($product['yml2_delivery_options'])) {
            $product['yml2_delivery_options'] = unserialize($product['yml2_delivery_options']);

            $options = array();
            if (!empty($product['yml2_delivery_options'])) {
                foreach($product['yml2_delivery_options'] as $delivery_option) {
                    $option = array(
                        'attr' => array(
                            'cost' => $delivery_option['cost'],
                            'days' => $delivery_option['days']
                        )
                    );

                    if (!empty($delivery_option['order_before'])) {
                        $option['attr']['order-before'] = $delivery_option['order_before'];
                    }

                    $options[] = $option;
                }
            }

            if (!empty($options)) {
                $offer['items']['delivery-options']['option'] = $options;
            }
        }

        if (!empty($product['yml2_sales_notes'])) {
            $offer['items']['sales_notes'] = $product['yml2_sales_notes'];
        }

        if (!empty($product['yml2_manufacturer_warranty'])) {
            if ($product['yml2_manufacturer_warranty'] == 'Y') {
                $offer['items']['manufacturer_warranty'] = 'true';
            } else {
                $offer['items']['manufacturer_warranty'] = 'false';
            }
        }

        if (!empty($product['yml2_expiry'])) {
            $offer['items']['expiry'] = $product['yml2_expiry'];
        }

        if (!empty($product['yml2_origin_country']) && fn_yml_check_country($product['yml2_origin_country'])) {
            $offer['items']['country_of_origin'] = $product['yml2_origin_country'];
        }

        $this->buildFeatures($product, $offer);

        if (empty($offer['items']['model'])
                && !empty($this->options['yml2_model_categories'][$category_id])
                && !empty($this->options['yml2_model_select'][$category_id]['value'])
                && $this->options['yml2_model_select'][$category_id]['value'] == 'yml2_model') {
            $offer['items']['model'] = $this->options['yml2_model_categories'][$category_id];
        }

        if (empty($offer['items']['typePrefix']) && !empty($this->options['yml2_type_prefix_categories'][$category_id])) {
            $offer['items']['typePrefix'] = $this->options['yml2_type_prefix_categories'][$category_id];
        }

        if (!empty($product['yml2_bid'])) {
            $offer['attr']['bid'] = $product['yml2_bid'];
        }

        if (!empty($product['yml2_cbid'])) {
            $offer['attr']['cbid'] = $product['yml2_cbid'];
        }

        if (!empty($product['yml2_fee'])) {
            $offer['attr']['fee'] = $product['yml2_fee'];
        } elseif ($this->options['export_default_fee'] > YML_MIN_FEE) {
            $offer['attr']['fee'] = $this->options['export_default_fee'];
        }

        if (!empty($product['yml2_purchase_price'])) {
            $offer['items']['purchase_price'] = $product['yml2_purchase_price'];
        }

        if ($this->options['weight'] == "Y") {
            $offer['items']['weight'] = $this->calculateWeight($product);
        }

        if ($this->options['dimensions'] == "Y") {
            $product['shipping_params'] = unserialize($product['shipping_params']);

            if (!empty($product['shipping_params']['min_items_in_box'])) {
                $length = $product['shipping_params']['box_length'];
                $width = $product['shipping_params']['box_width'];
                $height = $product['shipping_params']['box_height'];

                $offer['items']['dimensions'] = $length . '/' . $width . '/' . $height;
            }
        }

        if (!empty($product['yml2_cpa']) && $product['yml2_cpa'] == 'N') {
            $offer['items']['cpa'] = '0';
        }

        if (!empty($product['yml2_adult']) && $product['yml2_adult'] == 'Y') {
            $offer['items']['adult'] = 'true';
        }

        if ($this->options['not_downloadable'] == 'N' && !empty($product['is_edp']) && $product['is_edp'] == 'Y') {
            $offer['items']['downloadable'] = 'true';
        }

        return $offer;
    }

    protected function getImageUrl($image_pair)
    {
        $url = '';

        if (empty($image_pair)) {
            return '';
        }

        if ($this->options['image_type'] == 'detailed') {
            if (!empty($image_pair['detailed'])) {
                $url = $image_pair['detailed']['image_path'];
            }

        } else {
            $image_data = fn_image_to_display(
                $image_pair,
                $this->options['thumbnail_width'],
                $this->options['thumbnail_height']
            );

            if (!empty($image_data['image_path'])) {
                if (strpos($image_data['image_path'], '.php') && !empty($image_data['detailed_image_path']) && !empty($image_data['width']) && !empty($image_data['height'])) {
                    $image_data['image_path'] = fn_generate_thumbnail(
                        $image_data['detailed_image_path'],
                        $image_data['width'],
                        $image_data['height'],
                        false,
                        false,
                        $image_pair
                    );
                }

                if (!empty($image_data['image_path'])) {
                    $url = $image_data['image_path'];
                }
            }
        }

        if (!empty($url)) {
            $url = $this->escapeUrl($url);
            $url = fn_query_remove($url, 't');
            $url = str_replace('–', urlencode('–'), $url);
        }

        return $url;
    }

    protected function offerToXml($offer,  $level = 1)
    {
        $attr = $offer['attr'];
        $items = $offer['items'];
        $tab = str_repeat('    ', $level);

        $attr_str = '';
        if (!empty($attr)) {
            foreach($attr as $attr_key => $attr_value) {
                $attr_str .= ' ' . $attr_key . '="' . $attr_value . '"';
            }
        }

        $xml = $tab . "<offer$attr_str>\n";
        foreach($this->schema as $item) {
            if (isset($items[$item])) {
                $xml .= $this->arrayToXml($item, $items[$item], $level + 1);
            }
        }

        $xml .= $tab . "</offer>\n";

        return $xml;
    }

    protected function arrayToXml($item, $data, $level = 0, $attr=array())
    {
        $tab = str_repeat('    ', $level);
        if (!is_array($data)) {
            $attr_str = '';
            if (!empty($attr)) {
                foreach($attr as $attr_key => $attr_value) {
                    $attr_value = SecurityHelper::escapeHtml($attr_value);
                    $attr_str .= ' ' . $attr_key . '="' . $attr_value . '"';
                }
            }

            $attr_str = SecurityHelper::escapeHtml($attr_str);

            return $tab . '<' . $item . $attr_str . '>' . SecurityHelper::escapeHtml($data) . '</' . $item . ">\n";
        }

        $xml = '';
        foreach($data as $item_type => $value) {

            if (is_numeric($item_type)) {
                if (is_array($value)) {

                    $attr = !empty($value['attr']) ? $value['attr'] : array();
                    $attr_str = '';
                    if (!empty($attr)) {
                        foreach($attr as $attr_key => $attr_value) {
                            $attr_value = SecurityHelper::escapeHtml($attr_value);
                            $attr_str .= ' ' . $attr_key . '="' . $attr_value . '"';
                        }
                    }

                    if (isset($value['value'])) {
                        $xml .= $tab . '<' . $item . $attr_str . '>' . SecurityHelper::escapeHtml($value['value']) . '</' . $item . ">\n";

                    } elseif (isset($value['items'])) {
                        $xml .= $tab . '<' . $item . ">\n" . $this->arrayToXml($item_type, $value, $level + 1) .
                            $tab . '</' . $item . ">\n";

                    } else {
                        $xml .= $tab . '<' . $item . $attr_str . "/>\n";
                    }

                } else {
                    $xml .= $this->arrayToXml($item, $value, $level);
                }

            } else {
                $xml .= $tab . '<' . $item . ">\n" . $this->arrayToXml($item_type, $value, $level + 1) .
                        $tab . '</' . $item . ">\n";

            }
        }

        return $xml;
    }

    protected function escape($data)
    {
        $data = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $data);

        return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
    }

    protected function escapeUrl($url)
    {
        $url = fn_url($url);
        $url = fn_yml_c_encode($url);

        return $url;
    }

    protected function buildFeatures($product, &$offer)
    {
        $offers_feature = $this->getFeaturesValues($this->offer_type);

        if (!empty($product['typePrefix'])) {
            $offers_feature['param_data']['typePrefix'] = array(
                'type' => 'product',
                'value' => 'yml2_type_prefix'
            );

        } elseif (!empty($this->options['yml2_type_prefix_select'][$product['category_id']])) {
            $offers_feature['param_data']['typePrefix'] = $this->options['yml2_type_prefix_select'][$product['category_id']];
        }

        if (!empty($product['yml2_model'])) {
            $offers_feature['param_data']['model'] = array(
                'type' => 'product',
                'value' => 'yml2_model'
            );

        } elseif (!empty($this->options['yml2_model_select'][$product['category_id']])) {
            $offers_feature['param_data']['model'] = $this->options['yml2_model_select'][$product['category_id']];
        }

        if (!empty($product) && !empty($product['product_features'])) {

            foreach ($product['product_features'] as $feature) {

                $found = false;
                foreach($offers_feature['param_data'] as $feature_code => $feature_data) {
                    if ($feature_data['type'] != 'feature') {
                        continue;
                    }

                    if ($feature['feature_id'] == $feature_data['value']) {
                        $offer['items'][$feature_code] = $feature['value'];
                        $found = true;
                    }
                }

                if (!$found &&
                    ($feature['is_visible'] ||
                        (isset($product['variation_feature_ids']) && in_array($feature['feature_id'], $product['variation_feature_ids']))
                    )
                ) {
                     $param = array(
                        'attr' => array(
                            'name' => $feature['description']
                        ),
                        'value' => $feature['value']
                    );

                    if (!empty($feature['yml2_unit'])) {
                        $param['attr']['unit'] = $feature['yml2_unit'];
                    }

                    $offer['items']['param'][] = $param;
                }
            }
        }

        foreach($offers_feature['param_data'] as $feature_code => $feature_data) {
            if ($feature_data['type'] != 'product') {
                continue;
            }

            if (!empty($product[$feature_data['value']])) {
                $offer['items'][$feature_code] = $product[$feature_data['value']];
            }
        }
    }

    protected function getFeaturesValues($offer_type)
    {
        static $offers_feature = array();

        if (!isset($offers_feature['common'])) {
            $offers_feature['common'] = db_get_row("SELECT param_key, param_data FROM ?:yml_param WHERE param_type = 'offer' AND param_key = 'common' AND company_id = ?i", $this->options['company_id']);
            $offers_feature['common']['param_data'] = !empty($offers_feature['common']['param_data']) ? unserialize($offers_feature['common']['param_data']) : array();
        }

        if (!isset($offers_feature[$offer_type])) {
            $offers_feature[$offer_type] = db_get_row("SELECT param_key, param_data FROM ?:yml_param WHERE param_type = 'offer' AND param_key = ?s AND company_id = ?i", $offer_type, $this->options['company_id']);
            $offers_feature[$offer_type]['param_data'] = !empty($offers_feature[$offer_type]['param_data']) ? unserialize($offers_feature[$offer_type]['param_data']) : array();
            $offers_feature[$offer_type]['param_data'] = array_merge($offers_feature[$offer_type]['param_data'], $offers_feature['common']['param_data']);
        }

        return $offers_feature[$offer_type];
    }

    /**
     * Gets the offer type for product.
     *
     * @return string The offer type value.
     */
    public static function getOfferType()
    {
        $self = new static;

        return $self->offer_type;
    }

    /**
     * Returns variation name of product if it exists else return product name.
     *
     * @param array $product Current product
     *
     * @return string Offer name for product
     */
    public function getOfferName(array $product)
    {
        if (!empty($product['variation_name'])) {
            return $product['variation_name'];
        } else {
            return $product['product'];
        }
    }

    /**
     * Returns weight of product grams.
     *
     * @param array<float|int|string> $product Current product
     *
     * @return float|int|string Weight of product
     */
    protected function calculateWeight(array $product)
    {
        if (fn_is_not_empty((float) $product['weight'])) {
            return $product['weight'] * Registry::get('settings.General.weight_symbol_grams') / 1000;
        }

        return '0.01';
    }
}
