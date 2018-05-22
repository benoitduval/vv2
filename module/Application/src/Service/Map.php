<?php

namespace Application\Service;

use Zend\Http\Client;
use Zend\Json\Json;
use Zend\Json\Decoder;

/**
* 
*/
class Map
{
    protected $_key;
    protected $_url;

    public function __construct($apiKey, $url)
    {
        $this->_key = $apiKey;
        $this->_url = $url;
    }

    public function getGeocodedLatitudeAndLongitude($address)
    {
        $response = null;
        try {
            $client = new Client();
            $client->setUri($this->_url);
            $client->setParameterGet(array('address' => urlencode($address), 'sensor' => 'false'));

            $client->setMethod('GET');
            $result = $client->send();
            $response = Decoder::decode($result->getBody(), Json::TYPE_OBJECT);
        } catch (\Exception $e) {}
        return $response;
    }

    public function getCoordinates($address)
    {
        $response = $this->getGeocodedLatitudeAndLongitude($address);
        if (isset($response->results[0]->geometry->location)) {
             return array(
                'lat'  => $response->results[0]->geometry->location->lat,
                'long' => $response->results[0]->geometry->location->lng
            );
        } else {
           return null;
        }
    }
}
