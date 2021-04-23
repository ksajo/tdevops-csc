<?php


namespace Tygh\Tests\Unit\Addons\Retailcrm\Converters;


use Tygh\Addons\Retailcrm\Converters\CustomerConverter;
use Tygh\Tests\Unit\ATestCase;

class CustomerConverterTest extends ATestCase
{
    public $runTestInSeparateProcess = true;
    public $backupGlobals = false;
    public $preserveGlobalState = false;

    protected $settings;

    public function setUp()
    {
        $this->settings = $this->getMockBuilder('\Tygh\Addons\Retailcrm\Settings')
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    /**
     * @param $customer
     * @param $expected
     * @dataProvider dpConvertToCrmCustomer
     */
    public function testConvertToCrmCustomer($customer, $expected)
    {
        $converter = new CustomerConverter($this->settings);
        $this->assertEquals($expected, $converter->convertToCrmCustomer($customer));
    }

    public function dpConvertToCrmCustomer()
    {
        return array(
            array(
                array(
                    'user_id' => 3,
                    'firstname' => 'Jon',
                    'lastname' => 'Black',
                    'email' => 'customer@example.com',
                    'phone' => '77 77 777 7777',
                    'birthday' => strtotime('1999-10-12'),
                    'b_address' => '44 Main street',
                    'b_address_2' => '7',
                    'b_city' => 'Boston',
                    'b_country' => 'US',
                    'b_zipcode' => '02134',
                    'b_state_descr' => 'Massachusetts',
                    's_country_descr' => 'United States',
                ),
                array(
                    'externalId' => 3,
                    'firstName' => 'Jon',
                    'lastName' => 'Black',
                    'email' => 'customer@example.com',
                    'address' => array(
                        'index' => '02134',
                        'region' => 'Massachusetts',
                        'city' => 'Boston',
                        'text' => 'United States, Massachusetts, Boston, 44 Main street, 7',
                    ),
                    'phones' => array(
                        array('number' => '77 77 777 7777')
                    ),
                    'birthday' => '1999-10-12'
                )
            ),
            array(
                array(
                    'firstname' => 'Jon',
                    'lastname' => 'Black',
                    'email' => 'customer@example.com',
                    'phone' => '77 77 777 7777',
                    'birthday' => strtotime('1999-10-12'),
                    'b_address' => '44 Main street',
                    'b_address_2' => '7',
                    'b_city' => 'Boston',
                    'b_country' => 'US',
                    'b_zipcode' => '02134',
                    'b_state_descr' => 'Massachusetts',
                    's_country_descr' => 'United States',
                ),
                array(
                    'externalId' => 'customer@example.com',
                    'firstName' => 'Jon',
                    'lastName' => 'Black',
                    'email' => 'customer@example.com',
                    'address' => array(
                        'index' => '02134',
                        'region' => 'Massachusetts',
                        'city' => 'Boston',
                        'text' => 'United States, Massachusetts, Boston, 44 Main street, 7',
                    ),
                    'phones' => array(
                        array('number' => '77 77 777 7777')
                    ),
                    'birthday' => '1999-10-12'
                )
            )
        );
    }
}