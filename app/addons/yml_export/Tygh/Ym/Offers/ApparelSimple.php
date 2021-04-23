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

use Tygh\Ym\Offers;
use Tygh\Ym\Logs;
use Tygh\Enum\ProductTracking;

class ApparelSimple extends Simple
{
    protected $offer_type = 'apparel_simple';
    protected $params = array();
    protected $offer_origin = array();

    protected $schema = array(
        'url',
        'price',
        'oldprice',
        'currencyId',
        'categoryId',
        'picture',
        'store',
        'pickup',
        'delivery',
        'delivery-options',
        'name',
        'vendor',
        'vendorCode',
        'model',
        'description',
        'sales_notes',
        'manufacturer_warranty',
        'country_of_origin',
        'barcode',
        'cpa',
        'adult',
        'expiry',
        'weight',
        'dimensions',
        'purchase_price',
        'param',
    );

    public function gatherAdditional($product)
    {
        $this->offer['items']['name'] = $product['product'];

        return true;
    }

    public function xml($product)
    {
        $yml2_product_skip = 0;
        $xml = '';
        $this->offer = $this->build($product);
        $this->gatherAdditional($product);
        $this->getApparelOffer($product);

        if ($this->options['export_stock'] == 'Y') {
            if ($product['tracking'] !== ProductTracking::DO_NOT_TRACK && $product['amount'] <= 0) {
                $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_product_amount_is_empty'));
                $yml2_product_skip++;

                return array('', $yml2_product_skip);
            }
        }

        if (!empty($this->offer['items']['param'])) {
            $this->params = $this->offer['items']['param'];
        }

        if (!empty($product['variation_group_id'])) {
            if ($this->postBuild($xml, $product, $this->offer)) {
                $xml .= $this->offerToXml($this->offer);
            } else {
                $yml2_product_skip++;
            }

        } else {
            $product['product_options'] = empty($product['product_options']) ? fn_get_product_options($product['product_id'], CART_LANGUAGE) : $product['product_options'];

            $product_combinations = $this->generateCombinations($product);

            if (empty($product_combinations)) {
                $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_product_combinations_are_empty'));
                return array('', 1);
            }

            $offer_origin = $this->offer;
            $count_combination = 0;
            foreach ($product_combinations as $combination) {
                if (!$this->buildOfferCombination($product, $combination)) {
                    continue;
                }

                if ($this->postBuild($xml, $product, $this->offer)) {
                    $xml .= $this->offerToXml($this->offer);
                    $count_combination++;
                }

                $this->offer = $offer_origin;
            }
            if (empty($count_combination)) {
                $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_product_amount_is_empty'));
                $yml2_product_skip++;
            }
        }

        return array($xml, $yml2_product_skip);
    }

    protected function buildOfferCombination($product, $combination)
    {
        $this->offer['items']['param'] = $this->params;
        $product_options = $product['product_options'];

        foreach($combination['combination'] as $option_id => $variant_id) {
            if (!isset($product_options[$option_id]['variants'][$variant_id])) {
                return false;
            }
        }

        $options = fn_get_selected_product_options($product['product_id'], $combination['combination']);

        if (!empty($combination['image_pairs'])) {
            $url = $this->getImageUrl($combination['image_pairs']);
            if (!empty($url)) {
                $this->offer['items']['picture'] = $url;
            }
        }

        $this->offer['items']['price'] = fn_apply_options_modifiers($combination['combination'], $this->offer['items']['price'], 'P');

        if (isset($this->offer['items']['oldprice'])) {
            $this->offer['items']['oldprice'] = fn_apply_options_modifiers($combination['combination'], $this->offer['items']['oldprice'], 'P');
        }

        $combination_hash = array();
        foreach($combination['combination'] as $option_id => $variant_id) {
            $combination_hash[] = $option_id;
            $combination_hash[] = $variant_id;
        }

        $this->offer['items']['url'] = $this->escapeUrl('products.view?product_id=' . $product['product_id'] . '&combination=' . implode('_', $combination_hash));

        if (!empty($combination['product_code']) && isset($this->offer['items']['vendorCode']) && $this->offer['items']['vendorCode'] == $product['product_code']) {
            $this->offer['items']['vendorCode'] = $combination['product_code'];
        }

        foreach ($options as $option) {
            if (!empty($option['yml2_type_options'])) {
                $this->setOfferOptions($option);
            }
        }

        $this->offer['attr']['id'] = $this->generateNewId($options);

        sort($this->offer['items']['param']);

        return true;
    }

    protected function generateNewId($options)
    {
        $id = $this->offer['attr']['group_id'];
        $_options = array();
        foreach ($options as $option_index => $option) {
            $_options[$option['option_id']] = $option['value'];
        }

        ksort($_options);

        foreach ($_options as $option_id => $option_value) {
            $id .= $option_id . $option_value;
        }

        return substr(md5($id), 0, 20);
    }

    protected function getApparelOffer($product)
    {
        if (!empty($product['variation_group_id'])) {
            $this->offer['attr']['group_id'] = sprintf('%s0%s',
                $product['variation_group_id'],
                empty($product['variation_parent_product_id']) ? $product['product_id'] : $product['variation_parent_product_id']
            );
        } else {
            $this->offer['attr']['group_id'] = $product['product_id'];
        }
    }

    protected function setOfferOptions($option)
    {
        $type_list = fn_get_schema('yml', 'options_type');
        $name = $type_list[$option['yml2_type_options']]['name'];

        list($value, $variant_name) = $this->getValueOption($option);

        $this->offer['items']['param'][$name] = array(
            'attr' => array(
                'name' => $value
            ),
            'value' => $variant_name
        );

        if (!empty($option['yml2_option_param'])) {
            $this->offer['items']['param'][$name]['attr']['unit'] = $option['yml2_option_param'];
        }
    }

    protected function getValueOption($option)
    {
        $type_list = fn_get_schema('yml', 'options_type');
        $value = $type_list[$option['yml2_type_options']]['value'];

        if ($value == 'Цвет' && isset($this->offer['items']['param'])) {
            foreach($this->offer['items']['param'] as $param_index => $param_value) {
                if (!empty($param_value['attr']['name']) && $param_value['attr']['name'] == 'Цвет') {
                    unset($this->offer['items']['param'][$param_index]);
                }
            }
        }

        $variant_name = $option['variants'][$option['value']]['variant_name'];
        if (!empty($option['variants'][$option['value']]['yml2_variant'])) {
            $variant_name = $option['variants'][$option['value']]['yml2_variant'];
        }

        return array($value, $variant_name);
    }

    protected function generateCombinations($product)
    {
        $combinations = array();
        if (!empty($product['product_options'])) {

            $options = $product['product_options'];

            $variants = array();
            $variant_ids = array_keys($options);

            foreach ($variant_ids as $key => $option_id) {
                $variants[$key] = array_keys($options[$option_id]['variants']);
            }

            $combinations_ids = fn_get_options_combinations($variant_ids, $variants);

            $combinations = array();
            foreach($combinations_ids as $key => $combination) {
                $combination_hash = fn_generate_cart_id($product['product_id'], array('product_options' => $combination));

                if (!empty($product['selected_options'])) {
                    $variation_combination_hash = fn_generate_cart_id($product['product_id'], array('product_options' => $product['selected_options']));

                    if ($variation_combination_hash != $combination_hash) {
                        continue;
                    }
                }

                $combinations[$key] = array(
                    'product_id' => $product['product_id'],
                    'product_code' => $product['product_code'],
                    'combination_hash' => fn_generate_cart_id($product['product_id'], array('product_options' => $combination)),
                    'combination' => $combination,
                    'amount' => $product['amount'],
                    'temp' => 'Y',
                    'position' => 0,
                    'image_pairs' => reset($product['images'])
                );
            }
        }

        return $combinations;
    }
}
