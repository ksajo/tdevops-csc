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

namespace Tygh\Shippings;

use Exception;

use Tygh\Http;
use Tygh\Registry;

use Boxberry\Client\Client;
use Boxberry\Client\Serializer;
use Boxberry\Client\ParselCreateResponse;
use Boxberry\Client\Exceptions\BadSettingsException;
use Boxberry\Client\Exceptions\UnknownTypeException;
use Boxberry\Collections\ImgIdsCollection;
use Boxberry\Collections\ListPointsCollection;
use Boxberry\Models\Parsel;
use Boxberry\Models\Point;
use Boxberry\Requests\Request;
use Boxberry\Requests\PointsDescriptionRequest;
use Boxberry\Requests\Exceptions\RequiredFieldsNullException;


class BoxberryClient extends Client
{
    protected $init_cache = false;
    protected $lifetime = 0;
    protected $serializer = null;
    protected $production_url_old = 'http://api.boxberry.de/json.php';
    protected $production_url = 'http://api.boxberry.ru/json.php';

    public function __construct($service_params = array())
    {
        if (!empty($service_params['password'])) {
            $this->key = $service_params['password'];
        }

        $this->api_url = $this->getProductionUrl();
        $this->serializer = new Serializer();
        $this->lifetime = BOXBERRY_CACHE_DELIVERY;
    }

    /**
     * Sets cache
     *
     * @param Request $request The request that was sent to Boxberry
     * @param array $answer The response received from Boxberry
     */
    protected function setCache(Request $request, $answer)
    {
        if ($this->lifetime > 0) {
            $hash_request = md5($this->api_url . $request->getResultClass() . http_build_query($this->serializer->toArray($request)));
            Registry::set('boxberry_cache.' . $hash_request, $answer);
        }
    }

    /**
     * Gets cache
     *
     * @param Request $request The request to be sent to Boxberry
     * @return array The result of this request that may have been stored in cache
     */
    protected function getCache(Request $request)
    {
        $answer = array();

        if (!$this->init_cache) {
            Registry::registerCache('boxberry_cache', $this->lifetime, Registry::cacheLevel('time'));
            $this->init_cache = true;
        }

        if ($this->lifetime > 0) {
            $hash_request = md5($this->api_url . $request->getResultClass() . http_build_query($this->serializer->toArray($request)));

            $answer = Registry::get('boxberry_cache.' . $hash_request);
        }

        return $answer;
    }

    /**
     * Executes Http request
     *
     * @param Request $request The class that contains the information about the necessary request
     * @return resource|bool The result of the request, which is passed as a class
     *
     * @throws Exception Boxberry returns an error in response to the request
     * @throws UnknownTypeException The result of the request can't be processed
     */
    public function execute(Request $request, $lifetime = null)
    {
        if (empty($this->key)) {
            throw new BadSettingsException(__('rus_boxberry.enter_api_token'));
        }

        if ($request->checkRequiredFields() === false) {
            throw new RequiredFieldsNullException(__('rus_boxberry.not_all_fields_are_filled'));
        }

        if (isset($lifetime)) {
            $this->lifetime = $lifetime;
        }

        $answer = $this->getCache($request);

        if (empty($answer)) {
            if (property_exists($request, 'method') && $request->method == 'POST') {
                $data = array(
                    'method' => $request->getClassName(),
                    'token'  => $this->key,
                    'sdata'  => json_encode($this->serializer->toArray($request), JSON_UNESCAPED_UNICODE)
                );
                $answer = Http::post($this->api_url, $data, array(
                    'log_preprocessor' => '\Tygh\Http::unescapeJsonResponse'
                ));
            } else {
                $data = array(
                    'method' => $request->getClassName(),
                    'token'  => $this->key
                );

                $data = array_merge($data, $this->serializer->toArray($request));
                $answer = Http::get($this->api_url, $data, array(
                    'log_preprocessor' => '\Tygh\Http::unescapeJsonResponse'
                ));
            }

            $answer = json_decode($answer, true);
            $this->setCache($request, $answer);
        }

        $answerClass = $request->getResultClass();

        $type = $this->getType($answerClass);

        if (!empty($answer)) {
            $check_error = reset($answer);
            if (is_array($check_error)) {
                if (!empty($check_error['err'])) {
                    $answer['err'] = $check_error['err'];
                }
            }
        } else {
            $answer['err'] = __('rus_boxberry.connection_error');
        }

        if (!empty($answer['err'])) {
            throw new Exception($answer['err']);

        } elseif ($type == 'class') {

            return new $answerClass($answer);

        } elseif ($type == 'bool') {
            return $answer['text'] == 'ok';

        } else {
            throw new UnknownTypeException(__('rus_boxberry.unknown_error'));
        }
    }

    /**
     * Creates a parcel in Boxberry
     *
     * @param  Parsel $parcel The class with the information for creating a Boxberry parcel
     * @return string The tracking number of the created parcel
     */
    public function createParcel(Parsel $parcel)
    {
        $tracking_number = 0;

        /** @var \Boxberry\Requests\ParselCreateRequest $parselCreate */
        $parselCreate = $this->getParselCreate();
        $parselCreate->setParsel($parcel);

        /** @var ParselCreateResponse $answer */
        try {
            $answer = $this->execute($parselCreate);

            if (strlen($answer->getTrack())) {
                $tracking_number = $answer->getTrack();

                $parselSend = $this->getParselSend();
                $imIdsList = new ImgIdsCollection(array(
                    $answer->getTrack()
                ));

                $parselSend->setImgIdsList($imIdsList);
                /** @var \Boxberry\Client\ParselCheckResponse $parselCheck */
                //$parselCheck = $client->execute($parselSend);
            }
        } catch (Exception $e) {
            fn_set_notification('E', __('error'), $e->getMessage());
        }

        return $tracking_number;
    }

    /**
     * Gets the current status of the parcel
     *
     * @param string $tracking_number The tracking number of the parcel
     * @return string The status of the parcel
     */
    public function getStatus($tracking_number)
    {
        /** @var \Boxberry\Requests\ListStatusesRequest $listStatuses */
        $listStatuses = $this->getListStatusesFull();
        $listStatuses->setImId($tracking_number);

        try {
            $answer = $this->execute($listStatuses, false);
            $statuses = $answer->getStatuses();
            $status_data = $statuses->offsetGet($statuses->count() - 1);

            if ($status_data) {
                $status = $status_data->getName();
            } else {
                $status = __('rus_boxberry.without_status');
            }

        } catch (Exception $e) {
            $status = __('rus_boxberry.without_status');
        }

        return $status;
    }

    /**
     * Gets the pickup point description by ID
     *
     * @param int $point_id Point id
     * @return bool|Point The class with the pickup points
     */
    public function getPickupPoint($point_id)
    {
        $point_request = new PointsDescriptionRequest();
        $point_request->setCode($point_id);

        $point_description = false;
        try {
            $point_description = $this->execute($point_request);
            $point_description->setCode($point_id);
        } catch (Exception $e) {
            fn_set_notification('W', __('warning'), $e->getMessage());
        }

        return $point_description;
    }

    /**
     * Gets the list of pickup points available in the city
     *
     * @param string $city_name City name
     * @param string $region    Region name
     *
     * @return ListPointsCollection The class with the pickup points
     */
    public function getPickupPoints($city_name, $region = null)
    {
        static $boxberry_cities = null;
        static $points = array();

        $city_name = mb_strtoupper($city_name);

        if (!isset($boxberry_cities)) {
            $cities_list = $this->getListCities();
            $boxberry_cities = $this->execute($cities_list);
        }

        if (!isset($points[$city_name])) {
            $city_code = null;
            $points[$city_name] = new ListPointsCollection();

            foreach ($boxberry_cities as $city) {
                /** var /Boxberry/Models/City $city */
                if ($city->getName() == $city_name && (!isset($region) || preg_match('/' . preg_quote($city->getRegion(), '/') . '/ui', $region))) {
                    $city_code = $city->getCode();
                    break;
                }
            }

            if (!empty($city_code)) {
                $pickups_list = $this->getListPoints();
                $pickups_list->setCityCode($city_code);

                try {
                    $points[$city_name] = $this->execute($pickups_list);
                } catch (Exception $e) {
                    fn_set_notification('W', __('warning'), $e->getMessage());
                }
            }
        }

        $points[$city_name]->rewind();

        return $points[$city_name];
    }

    /**
     * Requests a link to the parcel's label
     *
     * @param string $tracking_number The tracking number of parcel
     * @return string A URL leading to the label
     */
    public function getLabel($tracking_number)
    {
        /** @var \Boxberry\Requests\ParselCheckRequest $parselCheck */
        $parselCheck = $this->getParselCheck();
        $parselCheck->setImId($tracking_number);

        try {
            $answer = $this->execute($parselCheck);
            $label = $answer->getLabel();
        } catch (Exception $e) {
            $label = false;
        }

        return $label;
    }

    /**
     * Gets production url according to the token type
     *
     * @return string $production_url Production url
     */
    public function getProductionUrl()
    {
        if (preg_match('/^[0-9]{4,5}[.]*[A-Za-z]+$/', $this->getKey(), $matches)) {
            return $this->production_url_old;
        }

        return $this->production_url;

    }
}
