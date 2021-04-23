<?php


namespace Tygh\Tests\Unit\Addons\Retailcrm;


use Tygh\Addons\Retailcrm\Settings;
use Tygh\Tests\Unit\ATestCase;

class SettingsTest extends ATestCase
{
    public $runTestInSeparateProcess = true;
    public $backupGlobals = false;
    public $preserveGlobalState = false;

    private $storage_settings;

    public function setUp()
    {
        define('CART_LANGUAGE', 'en');

        $this->storage_settings = $this->getMockBuilder('\Tygh\Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storage_settings->method('getValue')->willReturnMap(array(
            array('retailcrm_map_order_statuses', 'retailcrm', null, null, json_encode(array(
                'P' => 'processed',
                'C' => 'complete',
                'O' => 'new',
                'I' => 'cancel-other'
            ))),
            array('retailcrm_map_payment_types', 'retailcrm', null, null, json_encode(array(
                '1' => 'bank-card',
                '2' => 'e-money',
                '3' => 'credit'
            ))),
        ));

        parent::setUp();
    }

    /**
     * @param $internal_value
     * @param $expected
     * @dataProvider dpGetExternalPaymentType
     */
    public function testGetExternalPaymentType($internal_value, $expected)
    {
        $settings = new Settings($this->storage_settings);

        $this->assertEquals($expected, $settings->getExternalPaymentType($internal_value));
    }

    public function dpGetExternalPaymentType()
    {
        return array(
            array('1', 'bank-card'),
            array('3', 'credit'),
            array('2', 'e-money'),
            array('5', false),
        );

    }

    /**
     * @param $external_value
     * @param $expected
     * @dataProvider dpGetInternalPaymentType
     */
    public function testGetInternalPaymentType($external_value, $expected)
    {
        $settings = new Settings($this->storage_settings);

        $this->assertEquals($expected, $settings->getInternalPaymentType($external_value));
    }

    public function dpGetInternalPaymentType()
    {
        return array(
            array('bank-card', '1'),
            array('credit', '3'),
            array('e-money', '2'),
            array('undefined', false),
        );
    }

    /**
     * @param $internal_value
     * @param $expected
     * @dataProvider dpGetExternalOrderStatus
     */
    public function testGetExternalOrderStatus($internal_value, $expected)
    {
        $settings = new Settings($this->storage_settings);

        $this->assertEquals($expected, $settings->getExternalOrderStatus($internal_value));
    }

    public function dpGetExternalOrderStatus()
    {
        return array(
            array('P', 'processed'),
            array('C', 'complete'),
            array('O', 'new'),
            array('I', 'cancel-other'),
            array('Z', false),
        );
    }

    /**
     * @param $external_value
     * @param $expected
     * @dataProvider dpGetInternalOrderStatus
     */
    public function testGetInternalOrderStatus($external_value, $expected)
    {
        $settings = new Settings($this->storage_settings);

        $this->assertEquals($expected, $settings->getInternalOrderStatus($external_value));
    }

    public function dpGetInternalOrderStatus()
    {
        return array(
            array('processed', 'P'),
            array('complete', 'C'),
            array('new', 'O'),
            array('cancel-other', 'I'),
            array('undefined', false),
        );
    }
}