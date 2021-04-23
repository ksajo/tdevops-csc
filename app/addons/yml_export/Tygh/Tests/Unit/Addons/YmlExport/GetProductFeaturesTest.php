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

namespace Tygh\Tests\Unit\Addons\YmlExport;

use Tygh\Tests\Unit\ATestCase;

class GetProductFeaturesTest extends ATestCase
{
    public $runTestInSeparateProcess = true;

    public $backupGlobals = false;

    public $preserveGlobalState = false;

    protected function setUp()
    {
        define('ITERATION_OFFERS', 5000);
        define('DESCR_SL', 'en');
        define('AREA', 'A');
        define('ACCOUNT_TYPE', 'admin');

        $this->requireMockFunction('__');
    }

    /**
     * @dataProvider dpTestGeneral
     */
    public function testGeneral($product_features_data, $features, $expected)
    {
        $yml = new Yml2(0, 1, DESCR_SL, 0, false, array('price_id' => 1));

        $actual = $yml->getProductFeaturesAdapter($product_features_data, $features);

        $this->assertEquals($expected, $actual);
    }

    public function dpTestGeneral()
    {
        return array(
            array(
                array(
                    53 => array(
                        'feature_id'  => '53',
                        'product_id'  => '12',
                        'variant_id'  => '203',
                        'value'       => '',
                        'value_int'   => '222.00',
                        'lang_code'   => 'en',
                        'variant_ids' => array(
                            0 => '203',
                        ),
                    ),
                    52 => array(
                        'feature_id'  => '52',
                        'product_id'  => '12',
                        'variant_id'  => '201',
                        'value'       => '',
                        'value_int'   => null,
                        'lang_code'   => 'en',
                        'variant_ids' => array(
                            0 => '201',
                        ),
                    ),
                    54 => array(
                        'feature_id' => '54',
                        'product_id' => '12',
                        'variant_id' => '0',
                        'value'      => 'Foobar',
                        'value_int'  => null,
                        'lang_code'  => 'en',
                    ),
                    55 => array(
                        'feature_id' => '55',
                        'product_id' => '12',
                        'variant_id' => '0',
                        'value'      => '',
                        'value_int'  => '42.00',
                        'lang_code'  => 'en',
                    ),
                    51 => array(
                        'feature_id'  => '51',
                        'product_id'  => '12',
                        'variant_id'  => '196',
                        'value'       => '',
                        'value_int'   => null,
                        'lang_code'   => 'en',
                        'variant_ids' => array(
                            0 => '198',
                            1 => '196',
                        ),
                    ),
                    50 => array(
                        'feature_id' => '50',
                        'product_id' => '12',
                        'variant_id' => '0',
                        'value'      => 'Y',
                        'value_int'  => null,
                        'lang_code'  => 'en',
                    ),
                    57 => array(
                        'feature_id'  => '57',
                        'product_id'  => '12',
                        'variant_id'  => '205',
                        'value'       => '',
                        'value_int'   => null,
                        'lang_code'   => 'en',
                        'variant_ids' => array(
                            0 => '205',
                        ),
                    ),
                    56 => array(
                        'feature_id' => '56',
                        'product_id' => '12',
                        'variant_id' => '0',
                        'value'      => '',
                        'value_int'  => '1493191000.00',
                        'lang_code'  => 'en',
                    ),
                ),
                array(
                    51 => array(
                        'feature_id'          => '51',
                        'company_id'          => '1',
                        'feature_type'        => 'M',
                        'parent_id'           => '0',
                        'display_on_product'  => 'Y',
                        'display_on_catalog'  => 'N',
                        'display_on_header'   => 'N',
                        'description'         => 'Checkbox Multiple',
                        'lang_code'           => 'en',
                        'prefix'              => '',
                        'suffix'              => '',
                        'categories_path'     => '',
                        'full_description'    => '',
                        'status'              => 'A',
                        'comparison'          => 'N',
                        'position'            => '1',
                        'group_position'      => null,
                        'yml2_exclude_prices' => array(
                            0 => '',
                        ),
                        'yml2_variants_unit'  => '',
                        'variants'            => array(
                            196 => array(
                                'variant_id'       => '196',
                                'variant'          => 'Var 1',
                                'description'      => '',
                                'page_title'       => '',
                                'meta_keywords'    => '',
                                'meta_description' => '',
                                'lang_code'        => 'en',
                                'yml2_unit'        => '',
                                'feature_id'       => '51',
                                'url'              => '',
                                'position'         => '1',
                            ),
                            197 => array(
                                'variant_id'       => '197',
                                'variant'          => 'Var 2',
                                'description'      => '',
                                'page_title'       => '',
                                'meta_keywords'    => '',
                                'meta_description' => '',
                                'lang_code'        => 'en',
                                'yml2_unit'        => '',
                                'feature_id'       => '51',
                                'url'              => '',
                                'position'         => '2',
                            ),
                            198 => array(
                                'variant_id'       => '198',
                                'variant'          => 'Var 3',
                                'description'      => '',
                                'page_title'       => '',
                                'meta_keywords'    => '',
                                'meta_description' => '',
                                'lang_code'        => 'en',
                                'yml2_unit'        => '',
                                'feature_id'       => '51',
                                'url'              => '',
                                'position'         => '3',
                            ),
                        ),
                    ),
                    50 => array(
                        'feature_id'          => '50',
                        'company_id'          => '1',
                        'feature_type'        => 'C',
                        'parent_id'           => '0',
                        'display_on_product'  => 'Y',
                        'display_on_catalog'  => 'N',
                        'display_on_header'   => 'N',
                        'description'         => 'Checkbox Single',
                        'lang_code'           => 'en',
                        'prefix'              => '',
                        'suffix'              => '',
                        'categories_path'     => '',
                        'full_description'    => '',
                        'status'              => 'A',
                        'comparison'          => 'N',
                        'position'            => '2',
                        'group_position'      => null,
                        'yml2_exclude_prices' => array(
                            0 => '',
                        ),
                        'yml2_variants_unit'  => '',
                    ),
                    56 => array(
                        'feature_id'          => '56',
                        'company_id'          => '1',
                        'feature_type'        => 'D',
                        'parent_id'           => '0',
                        'display_on_product'  => 'Y',
                        'display_on_catalog'  => 'N',
                        'display_on_header'   => 'N',
                        'description'         => 'Other Date',
                        'lang_code'           => 'en',
                        'prefix'              => '',
                        'suffix'              => '',
                        'categories_path'     => '',
                        'full_description'    => '',
                        'status'              => 'A',
                        'comparison'          => 'N',
                        'position'            => '3',
                        'group_position'      => null,
                        'yml2_exclude_prices' => array(
                            0 => '',
                        ),
                        'yml2_variants_unit'  => '',
                    ),
                    55 => array(
                        'feature_id'          => '55',
                        'company_id'          => '1',
                        'feature_type'        => 'O',
                        'parent_id'           => '0',
                        'display_on_product'  => 'Y',
                        'display_on_catalog'  => 'N',
                        'display_on_header'   => 'N',
                        'description'         => 'Other Number',
                        'lang_code'           => 'en',
                        'prefix'              => '',
                        'suffix'              => '',
                        'categories_path'     => '',
                        'full_description'    => '',
                        'status'              => 'A',
                        'comparison'          => 'N',
                        'position'            => '4',
                        'group_position'      => null,
                        'yml2_exclude_prices' => array(
                            0 => '',
                        ),
                        'yml2_variants_unit'  => '',
                    ),
                    54 => array(
                        'feature_id'          => '54',
                        'company_id'          => '1',
                        'feature_type'        => 'T',
                        'parent_id'           => '0',
                        'display_on_product'  => 'Y',
                        'display_on_catalog'  => 'N',
                        'display_on_header'   => 'N',
                        'description'         => 'Other Text',
                        'lang_code'           => 'en',
                        'prefix'              => '',
                        'suffix'              => '',
                        'categories_path'     => '',
                        'full_description'    => '',
                        'status'              => 'A',
                        'comparison'          => 'N',
                        'position'            => '5',
                        'group_position'      => null,
                        'yml2_exclude_prices' => array(
                            0 => '',
                        ),
                        'yml2_variants_unit'  => '',
                    ),
                    57 => array(
                        'feature_id'          => '57',
                        'company_id'          => '1',
                        'feature_type'        => 'E',
                        'parent_id'           => '0',
                        'display_on_product'  => 'Y',
                        'display_on_catalog'  => 'N',
                        'display_on_header'   => 'N',
                        'description'         => 'Select Brand',
                        'lang_code'           => 'en',
                        'prefix'              => '',
                        'suffix'              => '',
                        'categories_path'     => '',
                        'full_description'    => '',
                        'status'              => 'A',
                        'comparison'          => 'N',
                        'position'            => '6',
                        'group_position'      => null,
                        'yml2_exclude_prices' => array(
                            0 => '',
                        ),
                        'yml2_variants_unit'  => '',
                        'variants'            => array(
                            205 => array(
                                'variant_id'       => '205',
                                'variant'          => 'Brand 1',
                                'description'      => '',
                                'page_title'       => '',
                                'meta_keywords'    => '',
                                'meta_description' => '',
                                'lang_code'        => 'en',
                                'yml2_unit'        => '',
                                'feature_id'       => '57',
                                'url'              => '',
                                'position'         => '1',
                            ),
                            206 => array(
                                'variant_id'       => '206',
                                'variant'          => 'Brand 2',
                                'description'      => '',
                                'page_title'       => '',
                                'meta_keywords'    => '',
                                'meta_description' => '',
                                'lang_code'        => 'en',
                                'yml2_unit'        => '',
                                'feature_id'       => '57',
                                'url'              => '',
                                'position'         => '2',
                            ),
                            207 => array(
                                'variant_id'       => '207',
                                'variant'          => 'Brand 3',
                                'description'      => '',
                                'page_title'       => '',
                                'meta_keywords'    => '',
                                'meta_description' => '',
                                'lang_code'        => 'en',
                                'yml2_unit'        => '',
                                'feature_id'       => '57',
                                'url'              => '',
                                'position'         => '3',
                            ),
                        ),
                    ),
                    53 => array(
                        'feature_id'          => '53',
                        'company_id'          => '1',
                        'feature_type'        => 'N',
                        'parent_id'           => '0',
                        'display_on_product'  => 'Y',
                        'display_on_catalog'  => 'N',
                        'display_on_header'   => 'N',
                        'description'         => 'Select Number',
                        'lang_code'           => 'en',
                        'prefix'              => '',
                        'suffix'              => '',
                        'categories_path'     => '',
                        'full_description'    => '',
                        'status'              => 'A',
                        'comparison'          => 'N',
                        'position'            => '7',
                        'group_position'      => null,
                        'yml2_exclude_prices' => array(
                            0 => '',
                        ),
                        'yml2_variants_unit'  => '',
                        'variants'            => array(
                            202 => array(
                                'variant_id'       => '202',
                                'variant'          => '111',
                                'description'      => '',
                                'page_title'       => '',
                                'meta_keywords'    => '',
                                'meta_description' => '',
                                'lang_code'        => 'en',
                                'yml2_unit'        => '',
                                'feature_id'       => '53',
                                'url'              => '',
                                'position'         => '1',
                            ),
                            203 => array(
                                'variant_id'       => '203',
                                'variant'          => '222',
                                'description'      => '',
                                'page_title'       => '',
                                'meta_keywords'    => '',
                                'meta_description' => '',
                                'lang_code'        => 'en',
                                'yml2_unit'        => '',
                                'feature_id'       => '53',
                                'url'              => '',
                                'position'         => '2',
                            ),
                            204 => array(
                                'variant_id'       => '204',
                                'variant'          => '333',
                                'description'      => '',
                                'page_title'       => '',
                                'meta_keywords'    => '',
                                'meta_description' => '',
                                'lang_code'        => 'en',
                                'yml2_unit'        => '',
                                'feature_id'       => '53',
                                'url'              => '',
                                'position'         => '3',
                            ),
                        ),
                    ),
                    52 => array(
                        'feature_id'          => '52',
                        'company_id'          => '1',
                        'feature_type'        => 'S',
                        'parent_id'           => '0',
                        'display_on_product'  => 'Y',
                        'display_on_catalog'  => 'N',
                        'display_on_header'   => 'N',
                        'description'         => 'Select Text',
                        'lang_code'           => 'en',
                        'prefix'              => '',
                        'suffix'              => '',
                        'categories_path'     => '',
                        'full_description'    => '',
                        'status'              => 'A',
                        'comparison'          => 'N',
                        'position'            => '8',
                        'group_position'      => null,
                        'yml2_exclude_prices' => array(
                            0 => '',
                        ),
                        'yml2_variants_unit'  => '',
                        'variants'            => array(
                            199 => array(
                                'variant_id'       => '199',
                                'variant'          => 'Text 1',
                                'description'      => '',
                                'page_title'       => '',
                                'meta_keywords'    => '',
                                'meta_description' => '',
                                'lang_code'        => 'en',
                                'yml2_unit'        => '',
                                'feature_id'       => '52',
                                'url'              => '',
                                'position'         => '1',
                            ),
                            200 => array(
                                'variant_id'       => '200',
                                'variant'          => 'Text 2',
                                'description'      => '',
                                'page_title'       => '',
                                'meta_keywords'    => '',
                                'meta_description' => '',
                                'lang_code'        => 'en',
                                'yml2_unit'        => '',
                                'feature_id'       => '52',
                                'url'              => '',
                                'position'         => '2',
                            ),
                            201 => array(
                                'variant_id'       => '201',
                                'variant'          => 'Text 3',
                                'description'      => '',
                                'page_title'       => '',
                                'meta_keywords'    => '',
                                'meta_description' => '',
                                'lang_code'        => 'en',
                                'yml2_unit'        => '',
                                'feature_id'       => '52',
                                'url'              => '',
                                'position'         => '3',
                            ),
                        ),
                    ),
                ),
                array(
                    0 => array(
                        'description' => 'Select Number',
                        'feature_id'  => '53',
                        'yml2_unit'   => '',
                        'is_visible'  => true,
                        'value'       => '222',
                    ),
                    1 => array(
                        'description' => 'Select Text',
                        'feature_id'  => '52',
                        'yml2_unit'   => '',
                        'is_visible'  => true,
                        'value'       => 'Text 3',
                    ),
                    2 => array(
                        'description' => 'Other Text',
                        'feature_id'  => '54',
                        'yml2_unit'   => '',
                        'is_visible'  => true,
                        'value'       => 'Foobar',
                    ),
                    3 => array(
                        'description' => 'Other Number',
                        'feature_id'  => '55',
                        'yml2_unit'   => '',
                        'is_visible'  => true,
                        'value'       => '42.00',
                    ),
                    4 => array(
                        'description' => 'Checkbox Multiple',
                        'feature_id'  => '51',
                        'yml2_unit'   => '',
                        'is_visible'  => true,
                        'value'       => 'Var 1, Var 3',
                    ),
                    5 => array(
                        'description' => 'Checkbox Single',
                        'feature_id'  => '50',
                        'yml2_unit'   => '',
                        'is_visible'  => true,
                        'value'       => 'yes',
                    ),
                    6 => array(
                        'description' => 'Select Brand',
                        'feature_id'  => '57',
                        'yml2_unit'   => '',
                        'is_visible'  => true,
                        'value'       => 'Brand 1',
                    ),
                    7 => array(
                        'description' => 'Other Date',
                        'feature_id'  => '56',
                        'yml2_unit'   => '',
                        'is_visible'  => true,
                        'value'       => '26.04.2017',
                    ),
                ),
            ),
        );
    }
}