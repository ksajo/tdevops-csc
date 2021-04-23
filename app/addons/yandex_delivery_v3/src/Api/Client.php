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

namespace Tygh\Addons\YandexDelivery\Api;

use Tygh\Http;

class Client
{
    /** @var string $oauth_key */
    protected $oauth_key;

    /** @var string $endpoint */
    protected $endpoint = 'https://api.delivery.yandex.ru/';

    /**
     * Client constructor.
     *
     * @param string $oauth_key OAuth authorization key.
     */
    public function __construct($oauth_key)
    {
        $this->oauth_key = $oauth_key;
    }

    /**
     * Makes request to Yandex.Delivery API endpoint.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     *
     * @param string               $method_name Name of request method in Yandex.Delivery API.
     * @param string               $method_type Type of request to Yandex.Delivery API.
     * @param array<string, mixed> $data        Body of request.
     *
     * @return array<string, mixed>|string
     */
    public function request($method_name, $method_type, array $data = [])
    {
        $headers = [
            'Authorization: OAuth ' . $this->oauth_key,
            'Content-type: application/json',
        ];
        switch ($method_type) {
            case Http::GET:
                $answer = Http::get($this->endpoint . $method_name, $data, ['headers' => $headers]);
                break;
            case Http::POST:
                $data = json_encode($data);
                $answer = Http::post($this->endpoint . $method_name, $data, ['headers' => $headers]);
                break;
            case Http::PUT:
                $data = json_encode($data);
                $answer = Http::put($this->endpoint . $method_name, $data, ['headers' => $headers]);
                break;
            case Http::DELETE:
                $answer = Http::delete($this->endpoint . $method_name, ['headers' => $headers]);
                break;
            default:
                $answer = '';
                break;
        }
        $answer = json_decode($answer, true);
        return $answer;
    }

    /**
     * Makes multiple parallel requests to Yandex.Delivery API endpoint.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     *
     * @param string                    $method_name Name of request method in Yandex.Delivery API.
     * @param string                    $method_type Type of request to Yandex.Delivery API.
     * @param array<string, int|string> $data        Body of request.
     *
     * @return array<string, mixed>|string
     */
    public function multiRequest($method_name, $method_type, array $data)
    {
        $headers = [
            'Authorization: OAuth ' . $this->oauth_key,
            'Content-type: application/json',
        ];
        switch ($method_type) {
            case Http::PUT:
                $requests = [];
                foreach ($data as $data_chunk) {
                    $data_chunk = json_encode($data_chunk);
                    $thread_id = Http::mput($this->endpoint . $method_name, $data_chunk, ['headers' => $headers]);
                    $requests[$thread_id] = $thread_id;
                }
                /** @var array<string> $answer */
                $answer = Http::processMultiRequest($requests);
                break;
            case Http::GET:
            case Http::POST:
            default:
                $answer = [];
                break;
        }
        $result = [];
        foreach ($answer as $request) {
            $result = array_merge($result, json_decode($request, true));
        }
        return $result;
    }
}
