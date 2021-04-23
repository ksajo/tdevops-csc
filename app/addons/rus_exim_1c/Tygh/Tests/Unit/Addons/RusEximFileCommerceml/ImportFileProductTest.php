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

namespace Tygh\Tests\Unit\Addons\RusEximFileCommerceml;

use Tygh\Database\Connection;
use Tygh\Tests\Unit\ATestCase;
use Tygh\Registry;
use Tygh\Commerceml\RusEximCommerceml;
use Tygh\Commerceml\Logs;

class ImportFileProductTest extends ATestCase
{
    public $runTestInSeparateProcess = true;
    public $backupGlobals = false;
    public $preserveGlobalState = false;
    public $path_files_for_tests = '';
    public $s_commerceml = array();
    public $cml = array();

    protected $app;
    protected $log;

    protected function setUp()
    {
        define('AREA', 'A');
        define('CART_LANGUAGE', 'ru');
        define('CHARSET', 'utf-8');
        define('DEFAULT_DIR_PERMISSIONS', 0777);
        define('SHIPPING_ADDRESS_PREFIX', 's');
        define('BILLING_ADDRESS_PREFIX', 'b');

        $this->requireCore('functions/fn.common.php');
        $this->requireCore('functions/fn.control.php');
        $this->requireCore('functions/fn.fs.php');
        $this->requireCore('functions/fn.users.php');
        $this->requireCore('functions/fn.database.php');

        $this->log = $this->getMockBuilder('Tygh\Commerceml\Logs')
            ->setMethods(array())
            ->disableOriginalConstructor()
            ->getMock();

        $this->app = \Tygh\Tygh::createApplication();
        $this->app['session'] = new \Tygh\Web\Session($this->app);

        $driver = $this->getMockBuilder('\Tygh\Backend\Database\Pdo')
            ->setMethods(array('escape', 'query', 'insertId'))
            ->getMock();
        $driver->method('escape')->will($this->returnCallback('addslashes'));
        $this->app['db.driver'] = $driver;

        $this->app['db'] = $this->getMockBuilder('\Tygh\Database\Connection')
            ->setMethods(array('error', 'getRow', 'getField', 'getColumn', 'hasError', 'getArray'))
            ->setConstructorArgs(array($driver))
            ->getMock();

        $this->path_files_for_tests = __DIR__ . '/';
        Registry::set('config.dir.files', $this->path_files_for_tests);
        Registry::set('config.dir.cache_templates', DIR_ROOT . '/var/cache/templates/');
        Registry::set('config.dir.cache_registry', DIR_ROOT . '/var/cache/registry/');
        Registry::set('config.dir.cache_misc', DIR_ROOT . '/var/cache/misc/');
        Registry::set('config.dir.cache_static', DIR_ROOT . '/var/cache/static/');
        Registry::set('config.dir.schemas', DIR_ROOT . '/app/schemas/');

        $this->cml = $this->getFieldsNames();

        Registry::set('addons.rus_exim_1c', $this->getSettingsExim());
        $this->s_commerceml = Registry::get('addons.rus_exim_1c');

        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;
        $exim_commerceml->s_commerceml = $this->s_commerceml;
        $exim_commerceml->path_commerceml = $this->path_files_for_tests;
        $exim_commerceml->company_id = 0;
    }

    public function getSettingsExim()
    {
        $exim_settings = array(
            'exim_1c_schema_version' => '2.05',
            'exim_1c_lang' => 'ru',
            'exim_1c_import_type_categories' => 'default',
            'exim_1c_import_type' => 'default',
            'exim_1c_import_products' => 'all_products',
            'exim_1c_allow_import_categories' => 'Y',
            'exim_1c_default_category' => '',
            'exim_1c_add_out_of_stock' => '',
            'exim_1c_add_tax' => '',
            'exim_1c_all_images_is_additional' => '',
            'exim_1c_import_product_name' => 'name',
            'exim_1c_import_product_code' => 'art',
            'exim_1c_import_full_description' => 'not_import',
            'exim_1c_import_short_description' => 'not_import',
            'exim_1c_page_title' => 'not_import',
            'exim_1c_allow_import_features' => 'Y',
            'exim_1c_property_product' => '',
            'exim_1c_used_brand' => 'not_used',
            'exim_1c_property_for_manufacturer' => '',
            'exim_1c_deny_or_allow' => 'not_used',
            'exim_1c_features_list' => '',
            'exim_1c_type_option' => 'S',
            'exim_1c_import_mode_offers' => 'standart',
            'exim_1c_import_option_name' => 'Варианты',
            'exim_1c_only_import_offers' => 'Y',
            'exim_1c_create_prices' => '',
            'exim_1c_check_prices' => '',
            'exim_1c_option_price' => 'N',
            'exim_1c_info_features' => '',
            'exim_1c_weight_property' => 'Вес',
            'exim_1c_display_weight' => '',
            'exim_1c_free_shipping' => 'Бесплатная доставка',
            'exim_1c_display_free_shipping' => '',
            'exim_1c_shipping_cost' => 'Стоимость доставки',
            'exim_1c_number_of_items' => 'Количество штук в коробке',
            'exim_1c_box_length' => 'Длина коробки',
            'exim_1c_box_width' => 'Ширина коробки',
            'exim_1c_box_height' => 'Высота коробки',
            'exim_1c_order_shipping' => '',
            'exim_1c_product_options' => '',
            'exim_1c_from_order_id' => '',
            'exim_1c_import_statuses' => 'Y',
            'exim_1c_export_statuses' => 'Y',
            'exim_1c_all_product_order' => '',
            'exim_1c_order_statuses' => ''
        );

        return $exim_settings;
    }

    public function getFieldsNames()
    {
        $schema = array(
            'commerceml' => 'КоммерческаяИнформация',
            'name' => 'Наименование',
            'classifier' => 'Классификатор',
            'groups' => 'Группы',
            'group' => 'Группа',
            'catalog' => 'Каталог',
            'products' => 'Товары',
            'product' => 'Товар',
            'id' => 'Ид',
            'value_fields' => 'ЗначенияРеквизитов',
            'value_field' => 'ЗначениеРеквизита',
            'full_name' => 'Полное наименование',
            'code' => 'Код',
            'html_description' => 'ОписаниеВФорматеHTML',
            'article' => 'Артикул',
            'description' => 'Описание',
            'properties_values' => 'ЗначенияСвойств',
            'property_values' => 'ЗначенияСвойства',
            'property' => 'Свойство',
            'properties' => 'Свойства',
            'taxes_rates' => 'СтавкиНалогов',
            'tax_rate' => 'СтавкаНалога',
            'rate_t' => 'Ставка',
            'value' => 'Значение',
            'manufacturer' => 'Изготовитель',
            'official_name' => 'ОфициальноеНаименование',
            'variants_values' => 'ВариантыЗначений',
            'id_value' => 'ИдЗначения',
            'directory' => 'Справочник',
            'file_description' => 'ОписаниеФайла',
            'packages' => 'ПакетПредложений',
            'offers' => 'Предложения',
            'offer' => 'Предложение',
            'amount' => 'Количество',
            'product_features' => 'ХарактеристикиТовара',
            'product_feature' => 'ХарактеристикаТовара',
            'prices_types' => 'ТипыЦен',
            'price_type' => 'ТипЦены',
            'prices' => 'Цены',
            'price' => 'Цена',
            'price_per_item' => 'ЦенаЗаЕдиницу',
            'price_id' => 'ИдТипаЦены',
            'warehouses' => 'Склады',
            'warehouse' => 'Склад',
            'warehouse_id' => 'ИдСклада',
            'warehouse_in_stock' => 'КоличествоНаСкладе',
            'image' => 'Картинка',
            'bar' => 'Штрихкод',
            'type_field' => 'ТипЗначений',
            'brand' => 'Бренд',
            'status' => 'Статус',
            'commerce_information' => 'КоммерческаяИнформация',
            'version_sheme' => 'ВерсияСхемы',
            'creation_date' => 'ДатаФормирования',
            'document' => 'Документ',
            'number' => 'Номер',
            'date' => 'Дата',
            'operation' => 'ХозОперация',
            'order' => 'Заказ товара',
            'role' => 'Роль',
            'seller' => 'Продавец',
            'currency' => 'Валюта',
            'rate' => 'Курс',
            'multiply' => 'Коэффициент',
            'total' => 'Сумма',
            'contractors' => 'Контрагенты',
            'lastname' => 'Фамилия',
            'firstname' => 'Имя',
            'contractor' => 'Контрагент',
            'address' => 'Адрес',
            'presentation' => 'Представление',
            'address_field' => 'АдресноеПоле',
            'locality'=> 'Населенный пункт',
            'type' => 'Тип',
            'post_code' => 'Почтовый индекс',
            'country' => 'Страна',
            'city' => 'Город',
            'street' => 'Улица',
            'contacts' => 'Контакты',
            'contact' => 'Контакт',
            'mail' => 'Почта',
            'work_phone' => 'ТелефонРабочий',
            'representatives' => 'Представители',
            'representative' => 'Представитель',
            'ratio' => 'Отношение',
            'administrator' => 'Администратор',
            'unregistered' => 'Незарегистрированный',
            'yes' => 'Да',
            'no' => 'Нет',
            'full_name_contractor' => 'ПолноеНаименование',
            'time' => 'Время',
            'notes' => 'Комментарий',
            'delivery_order' => 'Доставка заказа',
            'service' => 'Услуга',
            'spec_nomenclature' => 'ВидНоменклатуры',
            'type_nomenclature' => 'ТипНоменклатуры',
            'base_unit' => 'БазоваяЕдиница',
            'full_name_unit' => 'НаименованиеПолное',
            'item' => 'шт',
            'discounts' => 'Скидки',
            'discount' => 'Скидка',
            'product_discount' => 'Скидка на товар',
            'orders_discount' => 'Скидка на заказ',
            'rate_discounts' => 'Процент',
            'in_total' => 'УчтеноВСумме',
            'payment' => 'Метод оплаты',
            'status_order' => 'Статус заказа',
            'shipping' => 'Способ доставки',
            'taxes' => 'Налоги',
            'tax' => 'Налог'
        );

        return $schema;
    }

    /**
     * @dataProvider dpGetFileCommerceml
     */
    public function testGetFileCommerceml($data_xml, $file)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);

        $expected_xml = simplexml_load_string($data_xml);

        list($xml, $status, $text_message) = $exim_commerceml->getFileCommerceml($file);

        $this->assertEquals(true, $status);
        $this->assertEquals($expected_xml, $xml);
    }

    /**
     * @dataProvider dpGetCompanyIdByLinkType
     */
    public function testGetCompanyIdByLinkType($link_type, $group, $expected_sql, $expected_sql_param)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        $_group = simplexml_load_string($group);
        $this->app['db']->expects($this->once())->method('getColumn')->with(
            $expected_sql,
            $expected_sql_param
        );

        $exim_commerceml->getCompanyIdByLinkType($link_type, $_group);
    }

    /**
     * @dataProvider dpGetDataCategoryByFile
     */
    public function testGetDataCategoryByFile($group, $category_id, $parent_id, $lang_code, $data_comparison)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        $data_comparison['timestamp'] = time();

        $_group = simplexml_load_string($group);

        $data_file = $exim_commerceml->getDataCategoryByFile($_group, $category_id, $parent_id, $lang_code);

        $this->assertEquals($data_comparison, $data_file);
    }

    /**
     * @dataProvider dpDataFeatures
     */
    public function testDataFeatures($feature_name, $feature_id, $feature_type, $data_type, $used_brand, $property_for_manufacturer, $external_id, $data_comparison)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        $data_comparison['feature_type'] = $feature_type;

        $this->app['db']->expects($this->once())->method('getField')->with(
            "SELECT feature_type FROM ?:product_features WHERE external_id = ?s",
            '75579'
        );

        $data_features = $exim_commerceml->dataFeatures($feature_name, $feature_id, $data_type, $used_brand, $property_for_manufacturer, $external_id);

        $this->assertEquals($data_comparison, $data_features);
    }

    /**
     * @dataProvider dpGetProductDataByLinkType
     */
    public function testGetProductDataByLinkType($link_type, $product, $expected_sql, $expected_sql_param)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        $_product = simplexml_load_string($product);

        $this->app['db']->expects($this->once())->method('getRow')->with(
            $expected_sql,
            $expected_sql_param
        );

        $exim_commerceml->getProductDataByLinkType($link_type, $_product, $this->cml);
    }

    /**
     * @dataProvider dpGetProductIdByFile
     */
    public function testGetProductIdByFile($ids, $t_external_id, $t_combination_id)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        list($external_id, $combination_id) = $exim_commerceml->getProductIdByFile($ids);
        $this->assertEquals($t_external_id, $external_id);
        $this->assertEquals($t_combination_id, $combination_id);
    }

    /**
     * @dataProvider dpGetAdditionalDataProduct
     */
    public function testGetAdditionalDataProduct($requisites, $t_full_name, $t_product_code, $t_html_description)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        $_requisites = simplexml_load_string($requisites);

        list($full_name, $product_code, $html_description) = $exim_commerceml->getAdditionalDataProduct($_requisites, $this->cml);

        $this->assertEquals($t_full_name, $full_name);
        $this->assertEquals($t_product_code, $product_code);
        $this->assertEquals($t_html_description, $html_description);
    }

    /**
     * @dataProvider dpGetProductNameByType
     */
    public function testGetProductNameByType($type_name, $product, $full_name, $name_comparison)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        $_product = simplexml_load_string($product);

        $product_name = $exim_commerceml->getProductNameByType($type_name, $_product, $full_name, $this->cml);

        $this->assertEquals($name_comparison, $product_name);
    }

    /**
     * @dataProvider dpGetProductCodeByTypeCode
     */
    public function testGetProductCodeByTypeCode($type_code, $product, $product_code, $code_comparison)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        $_product = simplexml_load_string($product);

        $product_code = $exim_commerceml->getProductCodeByTypeCode($type_code, $_product, $product_code, $this->cml);

        $this->assertEquals($code_comparison, $product_code);
    }

    /**
     * @dataProvider dpGetProductDescriptionByType
     */
    public function testGetProductDescriptionByType($type_description, $product, $html_description, $full_name, $t_description)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        $_product = simplexml_load_string($product);

        $description = $exim_commerceml->getProductDescriptionByType($type_description, $_product, $html_description, $full_name, $this->cml);

        $this->assertEquals($t_description, $description);
    }

    /**
     * @dataProvider dpGetProductPageTitle
     */
    public function testGetProductPageTitle($type_description, $product, $full_name, $t_page_title)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        $_product = simplexml_load_string($product);

        $page_title = $exim_commerceml->getProductPageTitle($type_description, $_product, $full_name, $this->cml);

        $this->assertEquals($t_page_title, $page_title);
    }

    /**
     * @dataProvider dpAddProductTaxes
     */
    public function testAddProductTaxes($data_tax, $product_id, $company_id, $_expected_sql, $expected_sql_params)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        $_data_tax = simplexml_load_string($data_tax);

        foreach ($_expected_sql as $key_expected_sql => $expected_sql) {
            $this->app['db']->expects($this->at($key_expected_sql))->method('getColumn')->with(
                $expected_sql,
                $expected_sql_params[$key_expected_sql],
                $company_id
            );
        }

        $exim_commerceml->addProductTaxes($_data_tax, $product_id);
    }

    /**
     * @dataProvider dpGetOrderDataForXml
     */
    public function testGetOrderDataForXml($order_data, $t_order_data)
    {
        /** @var Connection|\PHPUnit_Framework_MockObject_MockObject $db */
        $db = $this->app['db'];

        $exim_commerceml = new RusEximCommerceml($db, $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        $db->expects($this->once())
            ->method('getArray')
            ->with('SELECT * FROM ?:rus_commerceml_currencies')
            ->willReturn(array());

        $order_xml = $exim_commerceml->getOrderDataForXml($order_data, $this->cml);

        $this->assertEquals($t_order_data, $order_xml);
    }

    /**
     * @dataProvider dpGetAdditionalOrderData
     */
    public function testGetAdditionalOrderData($order_data, $t_payment, $t_shipping)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        list($payment, $shipping) = $exim_commerceml->getAdditionalOrderData($order_data);

        $this->assertEquals($t_payment, $payment);
        $this->assertEquals($t_shipping, $shipping);
    }

    /**
     * @dataProvider dpGetDataOrderUser
     */
    public function testGetDataOrderUser($order_data, $user_data)
    {
        $exim_commerceml = new RusEximCommerceml($this->app['db'], $this->log, $this->path_files_for_tests);
        $exim_commerceml->cml = $this->cml;

        $user_xml = $exim_commerceml->getDataOrderUser($order_data);

        $this->assertEquals($user_data, $user_xml);
    }

    public function dpGetFileCommerceml()
    {
        $file = 'import.xml';

        $data_xml = <<<XML
<КоммерческаяИнформация>
    <Классификатор>
        <Ид>1</Ид>
        <Наименование>Классификатор (Каталог товаров)</Наименование>
        <Владелец>
            <Ид>75519</Ид>
            <Наименование>Праздник-Лето (тест)</Наименование>
        </Владелец>
        <Группы>
            <Группа>
                <Ид>75567</Ид>
                <Наименование>Аксесссуары для праздника</Наименование>
            </Группа>
        </Группы>
        <Свойства>
            <Свойство>
                <Ид>75579</Ид>
                <Наименование>Производитель</Наименование>
                <ДляТоваров>1</ДляТоваров>
                <ТипЗначений>Справочник</ТипЗначений>
                <ВариантыЗначений>
                    <Справочник>
                        <Ид>75580</Ид>
                        <ИдЗначения>75580</ИдЗначения>
                        <Значение>Веселая Затея</Значение>
                    </Справочник>
                </ВариантыЗначений>
            </Свойство>
        </Свойства>
    </Классификатор>
    <Каталог СодержитТолькоИзменения="false">
        <Ид>1</Ид>
        <ИдКлассификатора>1</ИдКлассификатора>
        <Наименование>Каталог товаров</Наименование>
        <Владелец>
            <Ид>75519</Ид>
            <Наименование>Праздник-Лето (тест)</Наименование>
        </Владелец>
        <Товары>
            <Товар>
                <Ид>75599</Ид>
                <Наименование>Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)</Наименование>
                <БазоваяЕдиница Код="778" НаименованиеПолное="упаковка" МеждународноеСокращение="PCE">упак.</БазоваяЕдиница>
                <Артикул>А552234</Артикул>
                <Группы>
                    <Ид>75568</Ид>
                </Группы>
                <Вес></Вес>
                <Описание></Описание>
                <СтавкиНалогов>
                    <СтавкаНалога>
                        <Наименование>НДС</Наименование>
                        <Ставка>18</Ставка>
                    </СтавкаНалога>
                </СтавкиНалогов>
                <ЗначенияСвойств>
                    <ЗначенияСвойства>
                        <Ид>75579</Ид>
                        <Наименование>Производитель</Наименование>
                        <Значение>75580</Значение>
                    </ЗначенияСвойства>
                </ЗначенияСвойств>
                <Производитель>
                    <Страна>Россия</Страна>
                </Производитель>
                <ЗначенияРеквизитов>
                    <ЗначениеРеквизита>
                        <Наименование>ВидНоменклатуры</Наименование>
                        <Значение>Товар</Значение>
                    </ЗначениеРеквизита>
                    <ЗначениеРеквизита>
                        <Наименование>ТипНоменклатуры</Наименование>
                        <Значение>Товар</Значение>
                    </ЗначениеРеквизита>
                    <ЗначениеРеквизита>
                        <Наименование>Полное наименование</Наименование>
                        <Значение>Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)</Значение>
                    </ЗначениеРеквизита>
                    <ЗначениеРеквизита>
                        <Наименование>Вес</Наименование>
                        <Значение>7</Значение>
                    </ЗначениеРеквизита>
                    <ЗначениеРеквизита>
                        <Наименование>ID Class365</Наименование>
                        <Значение>75599</Значение>
                    </ЗначениеРеквизита>
                </ЗначенияРеквизитов>
                <Картинка>import_files/1690.jpg</Картинка>
            </Товар>
        </Товары>
    </Каталог>
</КоммерческаяИнформация>
XML;

        return array(
            array($data_xml, $file)
        );
    }

    public function dpGetCompanyIdByLinkType()
    {
        $group = <<<XML
<Группа>
    <Ид>75567</Ид>
    <Наименование>Аксесссуары для праздника</Наименование>
</Группа>
XML;

        return array(
            array(
                'name', 
                $group, 
                "SELECT category_id FROM ?:category_descriptions WHERE category = ?s", 
                'Аксесссуары для праздника'
            ),
            array(
                'id', 
                $group, 
                "SELECT category_id FROM ?:categories WHERE external_id = ?s", 
                '75567'
            )
        );
    }

    public function dpGetDataCategoryByFile()
    {
        $group = <<<XML
<Группа>
    <Ид>75567</Ид>
    <Наименование>Аксесссуары для праздника</Наименование>
</Группа>
XML;

        $new_category = array(
            'category' => 'Аксесссуары для праздника',
            'lang_code' => 'ru',
            'company_id' => 0,
            'external_id' => '75567',
            'parent_id' => 0,
            'status' => 'A'
        );

        $data_category = array(
            'category' => 'Аксесссуары для праздника',
            'lang_code' => 'ru',
            'company_id' => 0,
            'external_id' => '75567'
        );

        return array(
            array($group, 0, 0, 'ru', $new_category),
            array($group, 1, 0, 'ru', $data_category)
        );
    }

    public function dpDataFeatures()
    {
        $cml = $this->getFieldsNames();

        $data_feature = array(
            'variants' => array(),
            'description' => 'Производитель',
            'company_id' => 0,
            'external_id' => '75579'
        );

        $new_feature = array(
            'variants' => array(),
            'description' => 'Производитель',
            'company_id' => 0,
            'external_id' => '75579',
            'position' => 0,
            'parent_id' => 0,
            'prefix' => '',
            'suffix' => '',
            'display_on_catalog' => 'Y',
            'display_on_product' => 'Y'
        );

        return array(
            array('Производитель', 0, 'S', 'S', '', '', '75579', $new_feature),
            array('Производитель', 0, 'N', 'Число', '', '', '75579', $new_feature),
            array('Производитель', 0, 'E', 'E', '', '', '75579', $new_feature),
            array('Производитель', 1, 'S', 'S', '', '', '75579', $data_feature),
            array('Производитель', 1, 'S', '', '', '', '75579', $data_feature),
            array('Производитель', 0, 'S', '', '', '', '75579', $new_feature),
            array('Производитель', 0, 'S', 'S', 'feature_product', '', '75579', $new_feature),
            array('Производитель', 0, 'E', 'S', 'feature_product', 'Производитель', '75579', $new_feature)
        );
    }

    public function dpGetProductDataByLinkType()
    {
        $product = <<<XML
<Товар>
    <Ид>75599</Ид>
    <Наименование>Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)</Наименование>
    <Артикул>А552234</Артикул>
    <Штрихкод>45542123</Штрихкод>
</Товар>
XML;

        return array(
            array(
                'article',
                $product,
                "SELECT product_id, update_1c FROM ?:products WHERE product_code = ?s",
                'А552234'
            ),
            array(
                'barcode',
                $product,
                "SELECT product_id, update_1c FROM ?:products WHERE product_code = ?s",
                '45542123'
            ),
            array(
                'id',
                $product,
                "SELECT product_id, update_1c FROM ?:products WHERE external_id = ?s",
                '75599'
            )
        );
    }

    public function dpGetProductIdByFile()
    {
        return array(
            array('75599', 75599, 0),
            array('75599#', 75599, ''),
            array('75599#253', 75599, 253),
            array('#253', '', 253)
        );
    }

    public function dpGetAdditionalDataProduct()
    {
        $requisites = <<<XML
<ЗначенияРеквизитов>
    <ЗначениеРеквизита>
        <Наименование>ВидНоменклатуры</Наименование>
        <Значение>Товар</Значение>
    </ЗначениеРеквизита>
    <ЗначениеРеквизита>
        <Наименование>ТипНоменклатуры</Наименование>
        <Значение>Товар</Значение>
    </ЗначениеРеквизита>
    <ЗначениеРеквизита>
        <Наименование>Полное наименование</Наименование>
        <Значение>Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)</Значение>
    </ЗначениеРеквизита>
    <ЗначениеРеквизита>
        <Наименование>Вес</Наименование>
        <Значение>7</Значение>
    </ЗначениеРеквизита>
    <ЗначениеРеквизита>
        <Наименование>ID Class365</Наименование>
        <Значение>75599</Значение>
    </ЗначениеРеквизита>
    <ЗначениеРеквизита>
        <Наименование>Код</Наименование>
        <Значение>KL-745</Значение>
    </ЗначениеРеквизита>
    <ЗначениеРеквизита>
        <Наименование>ОписаниеВФорматеHTML</Наименование>
        <Значение>Скрепки стальные Kores KCR-33, круглые, никелированные, 100шт.</Значение>
    </ЗначениеРеквизита>
</ЗначенияРеквизитов>
XML;

        return array(
            array(
                $requisites,
                'Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)',
                'KL-745',
                'Скрепки стальные Kores KCR-33, круглые, никелированные, 100шт.'
            )
        );
    }

    public function dpGetProductNameByType()
    {
        $product = <<<XML
<Товар>
    <Ид>75599</Ид>
    <Наименование>Скрепки стальные Kores KCR-33</Наименование>
    <Артикул>А552234</Артикул>
    <Штрихкод>45542123</Штрихкод>
</Товар>
XML;

        return array(
            array('name', $product, 'Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)', 'Скрепки стальные Kores KCR-33'),
            array('full_name', $product, 'Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)', 'Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)')
        );
    }

    public function dpGetProductCodeByTypeCode()
    {
        $product = <<<XML
<Товар>
    <Ид>75599</Ид>
    <Наименование>Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)</Наименование>
    <Артикул>А552234</Артикул>
    <Штрихкод>45542123</Штрихкод>
</Товар>
XML;

        return array(
            array('article', $product, 'KCR-33', 'А552234'),
            array('bar', $product, 'KCR-33', '45542123'),
            array('code', $product, 'KCR-33', 'KCR-33')
        );
    }

    public function dpGetProductDescriptionByType()
    {
        $product = <<<XML
<Товар>
    <Ид>75599</Ид>
    <Наименование>Скрепки стальные Kores KCR-33</Наименование>
    <Артикул>А552234</Артикул>
    <Штрихкод>45542123</Штрихкод>
    <Описание>Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)</Описание>
</Товар>
XML;

        return array(
            array(
                'description',
                $product,
                'Скрепки стальные Kores KCR-33, круглые, никелированные, 100шт.',
                'Скрепки стальные Kores KCR-33',
                'Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)'
            ),
            array(
                'html_description',
                $product,
                'Скрепки стальные Kores KCR-33, круглые, никелированные, 100шт.',
                'Скрепки стальные Kores KCR-33',
                'Скрепки стальные Kores KCR-33, круглые, никелированные, 100шт.'
            ),
            array(
                'full_name',
                $product,
                'Скрепки стальные Kores KCR-33, круглые, никелированные, 100шт.',
                'Скрепки стальные Kores KCR-33',
                'Скрепки стальные Kores KCR-33'
            )
        );
    }

    public function dpGetProductPageTitle()
    {
        $product = <<<XML
<Товар>
    <Ид>75599</Ид>
    <Наименование>Скрепки стальные Kores KCR-33</Наименование>
    <Артикул>А552234</Артикул>
    <Штрихкод>45542123</Штрихкод>
    <Описание>Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)</Описание>
</Товар>
XML;

        return array(
            array('name', $product, 'Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)', 'Скрепки стальные Kores KCR-33'),
            array('full_name', $product, 'Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)', 'Скрепки стальные Kores KCR-33 (33мм, круглые, никелированные, 100шт./уп.) (тест)')
        );
    }

    public function dpAddProductTaxes()
    {
        $data_tax = <<<XML
<СтавкиНалогов>
    <СтавкаНалога>
        <Наименование>НДС</Наименование>
        <Ставка>18</Ставка>
    </СтавкаНалога>
</СтавкиНалогов>
XML;

        return array(
            array(
                $data_tax,
                0,
                0,
                array(
                    '0' => "SELECT tax_id FROM ?:rus_exim_1c_taxes WHERE tax_1c = ?s AND company_id = ?i"
                ),
                array(
                    '0' => '18'
                )
            ),
            array(
                $data_tax,
                1,
                0,
                array(
                    '0' => "SELECT tax_ids FROM ?:products WHERE product_id = ?i AND company_id = ?i",
                    '1' => "SELECT tax_id FROM ?:rus_exim_1c_taxes WHERE tax_1c = ?s AND company_id = ?i"
                ),
                array(
                    '0' => 1,
                    '1' => '18'
                )
            )
        );
    }

    public function dpGetOrderDataForXml()
    {
        $order_data = array(
            'order_id' => 1,
            'user_id' => 1,
            'total' => 2616.61,
            'subtotal' => 3270.75,
            'discount' => 0.00,
            'subtotal_discount' => 0.00,
            'shipping_cost' => 0.00,
            'timestamp' => 1466744635,
            'status' => 'O',
            'notes' => '',
            'shipment_ids' => array(),
            'secondary_currency' => 'RUB',
            'display_subtotal' => 3270.75
        );

        $t_order_data = array(
            'Ид' => 1,
            'Номер' => 1,
            'Дата' => '2016-06-24',
            'Время' => '05:03:55',
            'ХозОперация' => 'Заказ товара',
            'Роль' => 'Продавец',
            'Курс' => '1',
            'Сумма' => '2616.61',
            'Валюта' => 'RUB',
            'Комментарий' => ''
        );

        return array(
            array($order_data, $t_order_data)
        );
    }

    public function dpGetAdditionalOrderData()
    {
        $order_data = array(
            'order_id' => 1,
            'payment_method' => array(
                'payment_id' => 12,
                'payment' => 'Сбербанк'
            ),
            'shipping' => array(
                '0' => array(
                    'shipping_id' => 10,
                    'shipping' => 'Курьером до двери'
                )
            )
        );

        return array(
            array($order_data, 'Сбербанк', 'Курьером до двери')
        );
    }

    public function dpGetDataOrderUser()
    {
        $order_data = array(
            'order_id' => 1,
            'user_id' => 1,
            'total' => 2616.60,
            'subtotal' => 3270.75,
            'discount' => 0.00,
            'subtotal_discount' => 0.00,
            'shipping_cost' => 0.00,
            'timestamp' => 1466744635,
            'status' => 'O',
            'notes' => '',
            'firstname' => 'Ксения',
            'lastname' => 'Родионова',
            'company' => '',
            'b_firstname' => 'Ксения',
            'b_lastname' => 'Родионова',
            'b_address' => 'Салова 8-25',
            'b_address_2' => '',
            'b_city' => 'Санкт-Петербург',
            'b_state' => 'SPE',
            'b_country' => 'RU',
            'b_zipcode' => '192102',
            'b_phone' => '+79174453201',
            's_firstname' => 'Ксения',
            's_lastname' => 'Родионова',
            's_address' => 'Салова 8-25',
            's_address_2' => '',
            's_city' => 'Санкт-Петербург',
            's_state' => 'SPE',
            's_country' => 'RU',
            's_zipcode' => '192102',
            's_phone' => '+79174453201',
            'phone' => '+79174453201',
            'email' => 'user1@example.com',
            'taxes' => array(),
            'b_country_descr' => 'Россия',
            's_country_descr' => 'Россия',
            'b_state_descr' => 'Санкт-Петербург',
            's_state_descr' => 'Санкт-Петербург',
            'shipment_ids' => array(),
            'secondary_currency' => 'RUB',
            'display_subtotal' => 3270.75
        );

        $user_data = array(
            'Ид' => 1,
            'Незарегистрированный' => 'Нет',
            'Наименование' => 'Родионова Ксения',
            'Роль' => 'Продавец',
            'ПолноеНаименование' => 'Родионова Ксения',
            'Фамилия' => 'Родионова',
            'Имя' => 'Ксения',
            'Адрес' => array(
                'Представление' => '192102, Россия, Санкт-Петербург, Салова 8-25 -',
                '0' => array(
                    'АдресноеПоле' => array(
                        'Тип' => 'Почтовый индекс',
                        'Значение' => '192102'
                    )
                ),
                '1' => array(
                    'АдресноеПоле' => array(
                        'Тип' => 'Страна',
                        'Значение' => 'Россия'
                    )
                ),
                '2' => array(
                    'АдресноеПоле' => array(
                        'Тип' => 'Город',
                        'Значение' => 'Санкт-Петербург'
                    )
                ),
                '3' => array(
                    'АдресноеПоле' => array(
                        'Тип' => 'Адрес',
                        'Значение' => 'Салова 8-25 -'
                    )
                )
            ),
            'Контакты' => array(
                '0' => array(
                    'Контакт' => array(
                        'Тип' => 'Почта',
                        'Значение' => 'user1@example.com'
                    )
                ),
                '1' => array(
                    'Контакт' => array(
                        'Тип' => 'ТелефонРабочий',
                        'Значение' => '+79174453201'
                    )
                )
            )
        );

        return array(
            array($order_data, $user_data)
        );
    }
}

