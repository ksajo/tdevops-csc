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

class CategoryTreeTest extends ATestCase
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

        $this->requireCore('functions/fn.common.php');
    }

    /**
     * @dataProvider dpStringToArray
     */
    public function testStringToArray($string, $expected)
    {
        $yml = new Yml2(0, 1, DESCR_SL, 0, false, array('price_id' => 1));

        $actual = $yml->convertCategoryToCategoryTreeItem($string);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider dpAttachCategoryId
     */
    public function testAttachCategoryId($tree, $expected)
    {
        $yml = new Yml2(0, 1, DESCR_SL, 0, false, array('price_id' => 1));

        $actual = $yml->convertToCategoriesTree($tree);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider dpAddManualCategory
     */
    public function testAddManualCategory($tree, $category, $category_tree_current_id, $expected)
    {
        $yml = new Yml2(0, 1, DESCR_SL, 0, false, array('price_id' => 1));

        $yml->setCategoriesCurrentId($category_tree_current_id);

        $yml->setCategoriesList($tree);

        $yml->addManualCategory($category);
        $actual = $yml->getCategoriesList();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider dpRestoreCategoriesList
     */
    public function testRestoreCategoriesList($xml_strings, $expected)
    {
        $yml = new Yml2(0, 1, DESCR_SL, 0, false, array('price_id' => 1));

        $actual = $yml->restoreCategoriesList($xml_strings);

        $this->assertEquals($expected, $actual);
    }

    public function dpStringToArray()
    {
        return array(
            array(
                'Авто',
                array('Авто' => array())
            ),
            array(
                'Авто/Запчасти',
                array('Авто' => array('Запчасти' => array())),
            ),
            array(
                'Авто/Запчасти/Автосвет',
                array('Авто' => array('Запчасти' => array('Автосвет' => array())))
            ),
            array(
                'Авто/Запчасти/Автосвет/Комплектующие к ксенону',
                array('Авто' => array('Запчасти' => array('Автосвет' => array('Комплектующие к ксенону' => array()))))
            ),
            array(
                '',
                array()
            ),
        );
    }

    public function dpAttachCategoryId()
    {
        return array(
            array(
                array('Авто' => array()),
                array(
                    'Авто' => array(
                        'name' => 'Авто',
                        'id' => 1,
                        'parent_id' => null,
                        'path' => 'Авто',
                        'parent_path' => '',
                        'children' => array(),
                    )
                )
            ),
            array(
                array('Авто' => array('Запчасти' => array())),
                array(
                    'Авто' => array(
                        'name' => 'Авто',
                        'id' => 1,
                        'parent_id' => null,
                        'path' => 'Авто',
                        'parent_path' => '',
                        'children' => array(
                            'Запчасти' => array(
                                'name' => 'Запчасти',
                                'id' => 2,
                                'parent_id' => 1,
                                'path' => 'Авто///Запчасти',
                                'parent_path' => 'Авто',
                                'children' => array(),
                            )
                        ),
                    )
                ),
            ),
            array(
                array(
                    'Авто' => array(
                        'Запчасти' => array('Автосвет' => array(), 'Аккумуляторы' => array()),
                        'Автомобильные инструменты' => array(),
                    )
                ),
                array(
                    'Авто' => array(
                        'name' => 'Авто',
                        'id' => 1,
                        'parent_id' => null,
                        'path' => 'Авто',
                        'parent_path' => '',
                        'children' => array(
                            'Запчасти' => array(
                                'name' => 'Запчасти',
                                'id' => 2,
                                'parent_id' => 1,
                                'path' => 'Авто///Запчасти',
                                'parent_path' => 'Авто',
                                'children' => array(
                                    'Автосвет' => array(
                                        'name' => 'Автосвет',
                                        'id' => 3,
                                        'parent_id' => 2,
                                        'path' => 'Авто///Запчасти///Автосвет',
                                        'parent_path' => 'Авто///Запчасти',
                                        'children' => array(),
                                    ),
                                    'Аккумуляторы' => array(
                                        'name' => 'Аккумуляторы',
                                        'id' => 4,
                                        'parent_id' => 2,
                                        'path' => 'Авто///Запчасти///Аккумуляторы',
                                        'parent_path' => 'Авто///Запчасти',
                                        'children' => array(),
                                    )
                                ),
                            ),
                            'Автомобильные инструменты' => array(
                                'name' => 'Автомобильные инструменты',
                                'id' => 5,
                                'parent_id' => 1,
                                'path' => 'Авто///Автомобильные инструменты',
                                'parent_path' => 'Авто',
                                'children' => array(),
                            ),
                        ),
                    )
                ),
            ),
        );
    }

    public function dpAddManualCategory()
    {
        return array(
            array(
                array(
                    'Авто' => array(
                        'name' => 'Авто',
                        'path' => 'Авто',
                        'parent_id' => null,
                        'id' => 1,
                    ),
                    'Авто///Запчасти' => array(
                        'name' => 'Запчасти',
                        'path' => 'Авто///Запчасти',
                        'parent_id' => 1,
                        'id' => 2,
                    ),
                    'Авто///Запчасти///Автосвет' => array(
                        'name' => 'Автосвет',
                        'path' => 'Авто///Запчасти///Автосвет',
                        'parent_id' => 2,
                        'id' => 3,
                    ),
                    'Авто///Запчасти///Аккумуляторы' => array(
                        'name' => 'Аккумуляторы',
                        'path' => 'Авто///Запчасти///Аккумуляторы',
                        'parent_id' => 2,
                        'id' => 4,
                    ),
                    'Авто///Автомобильные инструменты' => array(
                        'name' => 'Автомобильные инструменты',
                        'path' => 'Авто///Автомобильные инструменты',
                        'parent_id' => 1,
                        'id' => 5,
                    ),
                ),
                'Авто///Запчасти///Глушители',
                5,
                array(
                    'Авто' => array(
                        'name' => 'Авто',
                        'path' => 'Авто',
                        'parent_id' => null,
                        'id' => 1,
                    ),
                    'Авто///Запчасти' => array(
                        'name' => 'Запчасти',
                        'path' => 'Авто///Запчасти',
                        'parent_id' => 1,
                        'id' => 2,
                    ),
                    'Авто///Запчасти///Автосвет' => array(
                        'name' => 'Автосвет',
                        'path' => 'Авто///Запчасти///Автосвет',
                        'parent_id' => 2,
                        'id' => 3,
                    ),
                    'Авто///Запчасти///Аккумуляторы' => array(
                        'name' => 'Аккумуляторы',
                        'path' => 'Авто///Запчасти///Аккумуляторы',
                        'parent_id' => 2,
                        'id' => 4,
                    ),
                    'Авто///Запчасти///Глушители' => array(
                        'name' => 'Глушители',
                        'path' => 'Авто///Запчасти///Глушители',
                        'parent_id' => 2,
                        'id' => 6,
                    ),
                    'Авто///Автомобильные инструменты' => array(
                        'name' => 'Автомобильные инструменты',
                        'path' => 'Авто///Автомобильные инструменты',
                        'parent_id' => 1,
                        'id' => 5,
                    ),
                ),
            ),

            array(
                array(
                    'Авто' => array(
                        'name' => 'Авто',
                        'path' => 'Авто',
                        'parent_id' => null,
                        'id' => 1,
                    ),
                    'Авто///Запчасти' => array(
                        'name' => 'Запчасти',
                        'path' => 'Авто///Запчасти',
                        'parent_id' => 1,
                        'id' => 2,
                    ),
                    'Авто///Запчасти///Автосвет' => array(
                        'name' => 'Автосвет',
                        'path' => 'Авто///Запчасти///Автосвет',
                        'parent_id' => 2,
                        'id' => 3,
                    ),
                    'Авто///Запчасти///Аккумуляторы' => array(
                        'name' => 'Аккумуляторы',
                        'path' => 'Авто///Запчасти///Аккумуляторы',
                        'parent_id' => 2,
                        'id' => 4,
                    ),
                    'Авто///Автомобильные инструменты' => array(
                        'name' => 'Автомобильные инструменты',
                        'path' => 'Авто///Автомобильные инструменты',
                        'parent_id' => 1,
                        'id' => 5,
                    ),
                ),
                'Авто///Автомобили',
                5,
                array(
                    'Авто' => array(
                        'name' => 'Авто',
                        'path' => 'Авто',
                        'parent_id' => null,
                        'id' => 1,
                    ),
                    'Авто///Запчасти' => array(
                        'name' => 'Запчасти',
                        'path' => 'Авто///Запчасти',
                        'parent_id' => 1,
                        'id' => 2,
                    ),
                    'Авто///Запчасти///Автосвет' => array(
                        'name' => 'Автосвет',
                        'path' => 'Авто///Запчасти///Автосвет',
                        'parent_id' => 2,
                        'id' => 3,
                    ),
                    'Авто///Запчасти///Аккумуляторы' => array(
                        'name' => 'Аккумуляторы',
                        'path' => 'Авто///Запчасти///Аккумуляторы',
                        'parent_id' => 2,
                        'id' => 4,
                    ),
                    'Авто///Автомобильные инструменты' => array(
                        'name' => 'Автомобильные инструменты',
                        'path' => 'Авто///Автомобильные инструменты',
                        'parent_id' => 1,
                        'id' => 5,
                    ),
                    'Авто///Автомобили' => array(
                        'name' => 'Автомобили',
                        'path' => 'Авто///Автомобили',
                        'parent_id' => 1,
                        'id' => 6,
                    )
                ),
            ),

            array(
                array(
                    'Авто' => array(
                        'name' => 'Авто',
                        'path' => 'Авто',
                        'parent_id' => null,
                        'id' => 1,
                    ),
                    'Авто///Запчасти' => array(
                        'name' => 'Запчасти',
                        'path' => 'Авто///Запчасти',
                        'parent_id' => 1,
                        'id' => 2,
                    ),
                    'Авто///Запчасти///Автосвет' => array(
                        'name' => 'Автосвет',
                        'path' => 'Авто///Запчасти///Автосвет',
                        'parent_id' => 2,
                        'id' => 3,
                    ),
                    'Авто///Запчасти///Аккумуляторы' => array(
                        'name' => 'Аккумуляторы',
                        'path' => 'Авто///Запчасти///Аккумуляторы',
                        'parent_id' => 2,
                        'id' => 4,
                    ),
                    'Авто///Автомобильные инструменты' => array(
                        'name' => 'Автомобильные инструменты',
                        'path' => 'Авто///Автомобильные инструменты',
                        'parent_id' => 1,
                        'id' => 5,
                    ),
                ),
                'Продукты питания///Фрукты и овощи///Яблоки',
                5,
                array(
                    'Авто' => array(
                        'name' => 'Авто',
                        'path' => 'Авто',
                        'parent_id' => null,
                        'id' => 1,
                    ),
                    'Авто///Запчасти' => array(
                        'name' => 'Запчасти',
                        'path' => 'Авто///Запчасти',
                        'parent_id' => 1,
                        'id' => 2,
                    ),
                    'Авто///Запчасти///Автосвет' => array(
                        'name' => 'Автосвет',
                        'path' => 'Авто///Запчасти///Автосвет',
                        'parent_id' => 2,
                        'id' => 3,
                    ),
                    'Авто///Запчасти///Аккумуляторы' => array(
                        'name' => 'Аккумуляторы',
                        'path' => 'Авто///Запчасти///Аккумуляторы',
                        'parent_id' => 2,
                        'id' => 4,
                    ),
                    'Авто///Автомобильные инструменты' => array(
                        'name' => 'Автомобильные инструменты',
                        'path' => 'Авто///Автомобильные инструменты',
                        'parent_id' => 1,
                        'id' => 5,
                    ),
                    'Продукты питания' => array(
                        'name' => 'Продукты питания',
                        'path' => 'Продукты питания',
                        'parent_id' => null,
                        'id' => 6,
                    ),
                    'Продукты питания///Фрукты и овощи' => array(
                        'name' => 'Фрукты и овощи',
                        'path' => 'Продукты питания///Фрукты и овощи',
                        'parent_id' => 6,
                        'id' => 7,
                    ),
                    'Продукты питания///Фрукты и овощи///Яблоки' => array(
                        'name' => 'Яблоки',
                        'path' => 'Продукты питания///Фрукты и овощи///Яблоки',
                        'parent_id' => 7,
                        'id' => 8,
                    ),
                ),
            )
        );
    }

    public function dpRestoreCategoriesList()
    {
        return array(
            array(
                array(
                    '<category id="1">Авто</category>',
                    '<category id="2" parentId="1">Запчасти</category>',
                    '<category id="3" parentId="2">Автосвет</category>',
                    '<category id="4" parentId="2">Аккумуляторы</category>',
                    '<category id="6" parentId="2">Глушители</category>',
                    '<category id="5" parentId="1">Автомобильные инструменты</category>',
                ),
                array(
                    'Авто' => array(
                        'name' => 'Авто',
                        'path' => 'Авто',
                        'parent_id' => null,
                        'id' => 1,
                    ),
                    'Авто///Запчасти' => array(
                        'name' => 'Запчасти',
                        'path' => 'Авто///Запчасти',
                        'parent_id' => 1,
                        'id' => 2,
                    ),
                    'Авто///Запчасти///Автосвет' => array(
                        'name' => 'Автосвет',
                        'path' => 'Авто///Запчасти///Автосвет',
                        'parent_id' => 2,
                        'id' => 3,
                    ),
                    'Авто///Запчасти///Аккумуляторы' => array(
                        'name' => 'Аккумуляторы',
                        'path' => 'Авто///Запчасти///Аккумуляторы',
                        'parent_id' => 2,
                        'id' => 4,
                    ),
                    'Авто///Запчасти///Глушители' => array(
                        'name' => 'Глушители',
                        'path' => 'Авто///Запчасти///Глушители',
                        'parent_id' => 2,
                        'id' => 6,
                    ),
                    'Авто///Автомобильные инструменты' => array(
                        'name' => 'Автомобильные инструменты',
                        'path' => 'Авто///Автомобильные инструменты',
                        'parent_id' => 1,
                        'id' => 5,
                    ),
                ),
            ),
            array(
                array(
                    '<category id="1">Авто</category>',
                    '<category id="2" parentId="1">Запчасти</category>',
                    '<category id="3" parentId="2">Автосвет</category>',
                    '<category id="4" parentId="2">Аккумуляторы</category>',
                    '<category id="5" parentId="1">Автомобильные инструменты</category>',
                    '<category id="6" parentId="1">Автомобили</category>',
                ),
                array(
                    'Авто' => array(
                        'name' => 'Авто',
                        'path' => 'Авто',
                        'parent_id' => null,
                        'id' => 1,
                    ),
                    'Авто///Запчасти' => array(
                        'name' => 'Запчасти',
                        'path' => 'Авто///Запчасти',
                        'parent_id' => 1,
                        'id' => 2,
                    ),
                    'Авто///Запчасти///Автосвет' => array(
                        'name' => 'Автосвет',
                        'path' => 'Авто///Запчасти///Автосвет',
                        'parent_id' => 2,
                        'id' => 3,
                    ),
                    'Авто///Запчасти///Аккумуляторы' => array(
                        'name' => 'Аккумуляторы',
                        'path' => 'Авто///Запчасти///Аккумуляторы',
                        'parent_id' => 2,
                        'id' => 4,
                    ),
                    'Авто///Автомобильные инструменты' => array(
                        'name' => 'Автомобильные инструменты',
                        'path' => 'Авто///Автомобильные инструменты',
                        'parent_id' => 1,
                        'id' => 5,
                    ),
                    'Авто///Автомобили' => array(
                        'name' => 'Автомобили',
                        'path' => 'Авто///Автомобили',
                        'parent_id' => 1,
                        'id' => 6,
                    )
                ),
            ),
            array(
                array(
                    '<category id="1">Авто</category>',
                    '<category id="2" parentId="1">Запчасти</category>',
                    '<category id="3" parentId="2">Автосвет</category>',
                    '<category id="4" parentId="2">Аккумуляторы</category>',
                    '<category id="5" parentId="1">Автомобильные инструменты</category>',
                    '<category id="6">Продукты питания</category>',
                    '<category id="7" parentId="6">Фрукты и овощи</category>',
                    '<category id="8" parentId="7">Яблоки</category>',
                ),
                array(
                    'Авто' => array(
                        'name' => 'Авто',
                        'path' => 'Авто',
                        'parent_id' => null,
                        'id' => 1,
                    ),
                    'Авто///Запчасти' => array(
                        'name' => 'Запчасти',
                        'path' => 'Авто///Запчасти',
                        'parent_id' => 1,
                        'id' => 2,
                    ),
                    'Авто///Запчасти///Автосвет' => array(
                        'name' => 'Автосвет',
                        'path' => 'Авто///Запчасти///Автосвет',
                        'parent_id' => 2,
                        'id' => 3,
                    ),
                    'Авто///Запчасти///Аккумуляторы' => array(
                        'name' => 'Аккумуляторы',
                        'path' => 'Авто///Запчасти///Аккумуляторы',
                        'parent_id' => 2,
                        'id' => 4,
                    ),
                    'Авто///Автомобильные инструменты' => array(
                        'name' => 'Автомобильные инструменты',
                        'path' => 'Авто///Автомобильные инструменты',
                        'parent_id' => 1,
                        'id' => 5,
                    ),
                    'Продукты питания' => array(
                        'name' => 'Продукты питания',
                        'path' => 'Продукты питания',
                        'parent_id' => null,
                        'id' => 6,
                    ),
                    'Продукты питания///Фрукты и овощи' => array(
                        'name' => 'Фрукты и овощи',
                        'path' => 'Продукты питания///Фрукты и овощи',
                        'parent_id' => 6,
                        'id' => 7,
                    ),
                    'Продукты питания///Фрукты и овощи///Яблоки' => array(
                        'name' => 'Яблоки',
                        'path' => 'Продукты питания///Фрукты и овощи///Яблоки',
                        'parent_id' => 7,
                        'id' => 8,
                    ),
                ),
            ),
        );
    }
}
