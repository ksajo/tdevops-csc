<?php


namespace Tygh\Tests\Unit\RusOnlineCashRegister\CashRegister\Atol;

use PHPUnit_Framework_TestCase;
use Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol\InfoResponse;
use Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt;

class InfoResponseTest extends PHPUnit_Framework_TestCase
{
    public $runTestInSeparateProcess = true;
    public $backupGlobals = false;
    public $preserveGlobalState = false;

    /**
     * @param $json
     * @param $expected_requisites
     * @param $expected_errors
     * @param $expected_status
     * @param $expected_status_message
     * @dataProvider dpParseJson
     */
    public function testParseJson($json, $expected_requisites, $expected_errors, $expected_status, $expected_status_message)
    {
        $response = new InfoResponse($json);
        $requisites = $response->getReceiptRequisites();

        if ($expected_requisites !== null) {
            $this->assertEquals($expected_requisites, $requisites->toArray());
        } else {
            $this->assertNull($requisites);
        }

        if ($expected_errors !== null) {
            $this->assertEquals($expected_errors, $response->getErrors());
        } else {
            $this->assertEmpty($response->getErrors());
        }

        $this->assertEquals($expected_status, $response->getStatus());
        $this->assertEquals($expected_status_message, $response->getStatusMessage());
    }

    /**
     * @return array
     */
    public function dpParseJson()
    {
        return array(
            array(
                json_encode(array(
                    'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                    'timestamp' => '10.01.2017 02:24:56 PM',
                    'callback_url' => 'http://example.com/callback',
                    'status' => 'done',
                    'payload' => array(
                        'fiscal_receipt_number' => 777,
                        'shift_number' => 100777,
                        'receipt_datetime' => '10.01.2017 02:34:56 PM',
                        'total' => 15700,
                        'fn_number' => '56878686',
                        'ecr_registration_number' => '123567883434677888',
                        'fiscal_document_number' => 100500,
                        'fiscal_document_attribute' => 900,
                    )
                )),
                array(
                    'receipt_timestamp' => strtotime('10.01.2017 02:34:56 PM'),
                    'receipt_total' => 15700,
                    'fiscal_receipt_number' => 777,
                    'shift_number' => 100777,
                    'ecr_registration_number' => '123567883434677888',
                    'fiscal_document_number' => 100500,
                    'fiscal_document_attribute' => 900,
                    'fn_number' => '56878686',
                ),
                null,
                Receipt::STATUS_DONE,
                null
            ),
            array(
                json_encode(array(
                    'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                    'inn' => '123567890123445',
                    'timestamp' => '10.01.2017 02:24:56 PM',
                    'callback_url' => 'http://example.com/callback',
                    'status' => 'fail',
                    'error' => array(
                        'code' => 1,
                        'text' => 'error message'
                    )
                )),
                array(
                    'fiscal_receipt_number' => null,
                    'receipt_timestamp' => null,
                    'receipt_total' => null,
                    'shift_number' => null,
                    'fn_number' => null,
                    'ecr_registration_number' => null,
                    'fiscal_document_number' => null,
                    'fiscal_document_attribute' => null,
                ),
                array(
                    '1' => 'error message'
                ),
                Receipt::STATUS_FAIL,
                'error message'
            )
        );
    }
}