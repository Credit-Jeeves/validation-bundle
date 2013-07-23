<?php

namespace RentJeeves\ComponentBundle\Service\Google;

use RentJeeves\ComponentBundle\Service\Google\GooglePlacesCallType;

class GooglePlaces
{
    const OK_STATUS = 'OK';

    public $outputType = 'json'; //either json, xml or array
    public $errors = array();
        
    protected $apiKey = '';
    protected $apiUrl = 'https://maps.googleapis.com/maps/api/place';
    protected $apiCallType = '';
    protected $includeDetails = false;
    protected $language = 'en';

    // REQUIRED:
    protected $location;           // Required - This must be provided as a google.maps.LatLng object.
    protected $query;              // Required if using textsearch
    protected $radius = 50000;     // Required if using nearbysearch or radarsearch (50,000 meters max)
    protected $sensor = 'false';   // Required simply true or false, is the provided $location coming from GPS?
    // Optional - http://code.google.com/apis/maps/documentation/places/supportedtypes.html
    protected $types;
    protected $name;               // Optional
    // Optional - "A term to be matched against all content that Google has indexed for this Place,
    // including but not limited to name, type, and address, as well as customer reviews and other third-party content."
    protected $keyword;
    protected $reference;
    protected $accuracy;
    protected $pageToken;
    protected $curloptSslVerifypeer = true; // option CURLOPT_SSL_VERIFYPEER with true value working not always

    /**
     * constructor - creates a googlePlaces object with the specified API Key
     *
     * @param $apiKey - the API Key to use
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    // for backward compatibility
    public function search()
    {
        $this->apiCallType = GooglePlacesCallType::SEARCH;

        return $this->executeAPICall();
    }

    // hits the v3 API
    public function nearbySearch()
    {
        $this->apiCallType = GooglePlacesCallType::NEARBY_SEARCH;
        return $this->executeAPICall();
    }

    // hits the v3 API
    public function radarSearch()
    {
        $this->apiCallType = GooglePlacesCallType::RADAR_SEARCH;

        return $this->executeAPICall();
    }

    // hits the v3 API
    public function textSearch()
    {
        $this->apiCallType = GooglePlacesCallType::TEXT_SEARCH;

        return $this->executeAPICall();
    }

    public function details()
    {
        $this->apiCallType = GooglePlacesCallType::DETAILS_SEARCH;
        
        return $this->executeAPICall();
    }

    public function checkIn()
    {
        $this->apiCallType = GooglePlacesCallType::CHECKIN;

        return $this->executeAPICall();
    }

    public function add()
    {
        $this->apiCallType = GooglePlacesCallType::ADD;

        return $this->executeAPICall();
    }

    public function delete()
    {
        $this->apiCallType = GooglePlacesCallType::DELETE;

        return $this->executeAPICall();
    }

    public function repeat($pageToken)
    {
        $this->apiCallType = GooglePlacesCallType::REPEAT;
        $this->pageToken = $pageToken;

        return $this->executeAPICall();
    }

    /**
     * executeAPICall - Executes the Google Places API call specified by this class's members and
     * returns the results as an array
     *
     * @return mixed - the array resulting from the Google Places API call specified by the members of this class
     */
    protected function executeAPICall()
    {
        $this->checkErrors();

        if ($this->apiCallType == GooglePlacesCallType::ADD || $this->apiCallType == GooglePlacesCallType::DELETE) {
            $result = $this->executeAddOrDelete();
            return $result;
        }

        $urlParameterString = $this->formatParametersForURL();

        $URLToCall = $this->apiUrl . '/' . $this->apiCallType . '/';
        $URLToCall .= $this->outputType . '?key='.$this->apiKey . '&' . $urlParameterString;
        $data = $this->curlCall($URLToCall);
        $result = json_decode($data, true);
        $formattedResults = $this->formatResults($result);

        return $formattedResults;
    }

    /**
     * checkErrors - Checks to see if this Google Places request has all of the required fields as 
     * far as we know. In the  event that it doesn't, it'll populate the errors array with an error
     * message for each error found.
     */
    protected function checkErrors()
    {
        if (empty($this->apiCallType)) {
            $this->errors[] = 'API Call Type is required but is missing.';
        }

        if (empty($this->apiKey)) {
            $this->errors[] = 'API Key is is required but is missing.';
        }

        if (($this->outputType!='json') && ($this->outputType!='xml') && ($this->outputType!='json')) {
            $this->errors[] = 'OutputType is required but is missing.';
        }
    }

    /**
     * executeAddOrDelete - Executes a Google Places add or delete call based on the call type member variable.
     * These are separated from the other types because they require a POST.
     *
     * @return mixed - the Google Places API response for the given call type
     */
    protected function executeAddOrDelete()
    {
        $postUrl = $this->apiUrl . '/' . $this->apiCallType . '/' . $this->outputType;
        $postUrl .='?key=' . $this->apiKey . '&sensor=' . $this->sensor;

        if ($this->apiCallType == GooglePlacesCallType::ADD) {
            $locationArray = explode(',', $this->location);
            $lat = trim($locationArray[0]);
            $lng = trim($locationArray[1]);

            $postData = array();
            $postData['location']['lat'] = floatval($lat);
            $postData['location']['lng'] = floatval($lng);
            $postData['accuracy'] = $this->accuracy;
            $postData['name'] = $this->name;
            $postData['types'] = explode('|', $this->types);
            $postData['language'] = $this->language;
        }

        if ($this->apiCallType == GooglePlacesCallType::DELETE) {
            $postData['reference'] = $this->reference;
        }

        $result = json_decode($this->curlCall($postUrl, json_encode($postData)));
        $result->errors = $this->errors;
        return $result;
    }

    /**
     * formatResults - Formats the results in such a way that they're easier to parse (especially addresses)
     *
     * @param mixed $result - the Google Places result array
     * @return mixed - the formatted Google Places result array
     */
    protected function formatResults($result)
    {
        $formattedResults = array();
        $formattedResults['errors'] = $this->errors;

        // for backward compatibility
        $resultColumnName = 'result';
        if (!isset($result[$resultColumnName])) {
            $resultColumnName = 'results';
        }

        $formattedResults['status'] = (isset($result['status'])) ? $result['status'] : null;
        $formattedResults['reference'] = (isset($result['reference'])) ? $result['reference'] : null;
        $formattedResults['id'] = (isset($result['id'])) ? $result['status'] : null;
        
        $formattedResults['result'] = $result[$resultColumnName];

        if (isset($result['status'])
            && $result['status'] == self::OK_STATUS
            && isset($result[$resultColumnName]['address_components'])
            ) {
            foreach ($result[$resultColumnName]['address_components'] as $key => $component) {

                $address_street_number='';
                $address_streetname='';
                $address_city='';
                $address_state='';
                $address_postal_code='';

                if ($component['types'][0]=='street_number') {
                    $address_street_number = $component['shortname'];
                }

                if ($component['types'][0]=='route') {
                    $address_streetname = $component['shortname'];
                }

                if ($component['types'][0]=='locality') {
                    $address_city = $component['shortname'];
                }

                if ($component['types'][0]=='administrative_area_level_1') {
                    $address_state = $component['shortname'];
                }

                if ($component['types'][0]=='postal_code') {
                    $address_postal_code = $component['shortname'];
                }
            }

            $formattedResults['result']['address_fixed']['street_number'] = $address_street_number;
            $formattedResults['result']['address_fixed']['address_streetname'] = $address_streetname;
            $formattedResults['result']['address_fixed']['address_city'] = $address_city;
            $formattedResults['result']['address_fixed']['address_state'] = $address_state;
            $formattedResults['result']['address_fixed']['address_postal_code'] = $address_postal_code;
        }

        return $formattedResults;
    }

    /**
     * formatParametersForURL - formats the url parameters for use with a GET request depending on the call type
     *
     * @return string - the formatted parameter request string based on the call type
     */
    protected function formatParametersForURL()
    {

        $parameterString = '';

        switch ($this->apiCallType) {
            case (GooglePlacesCallType::SEARCH):
                $parameterString = 'location=' . $this->location . '&radius='.$this->radius;
                $parameterString .= '&types=' . urlencode($this->types) . '&language=' . $this->language;
                $parameterString .= '&name=' . $this->name . '&keyword=' . $this->keyword. '&sensor=' . $this->sensor;
                break;
            case (GooglePlacesCallType::NEARBY_SEARCH):
                $parameterString = 'location=' . $this->location . '&radius='.$this->radius;
                $parameterString .= '&types=' . urlencode($this->types) . '&language=' . $this->language;
                $parameterString .= '&name=' . $this->name . '&keyword=' . $this->keyword. '&sensor=' . $this->sensor;
                break;
            case (GooglePlacesCallType::RADAR_SEARCH):
                $parameterString = 'location=' . $this->location . '&radius='.$this->radius;
                $parameterString .= '&types=' . urlencode($this->types) . '&language=' . $this->language;
                $parameterString .= '&name=' . $this->name . '&keyword=' . $this->keyword. '&sensor=' . $this->sensor;
                break;
            case (GooglePlacesCallType::TEXT_SEARCH):
                $parameterString = 'query=' . $this->query . '&location=' . $this->location;
                $parameterString .= '&radius=' . $this->radius . '&types=' . urlencode($this->types) . '&language=';
                $parameterString .= $this->language . '&sensor=' . $this->sensor;
                break;
            case (GooglePlacesCallType::DETAILS_SEARCH):
                $parameterString = 'reference=' . $this->reference . '&language=';
                $parameterString .= $this->language . '&sensor=' . $this->sensor;
                break;
            case (GooglePlacesCallType::CHECKIN):
                $parameterString = 'reference=' . $this->reference . '&language=';
                $parameterString .= $this->language . '&sensor=' . $this->sensor;
                break;
            case (GooglePlacesCallType::REPEAT):
                $parameterString = 'radius='.$this->radius . '&sensor=';
                $parameterString .=  $this->sensor . '&pagetoken=' . $this->pageToken;
                $this->apiCallType = 'search';
                break;
        }

        return $parameterString;
    }


    /**
     * curlCall - Executes a curl call to the specified url with the specified data to post and returns the result. If
     * the post data is empty, the call will default to a GET
     *
     * @param $url - the url to curl to
     * @param array $dataToPost - the data to post in the curl call (if any)
     * @return mixed - the response payload of the call
     */
    protected function curlCall($url, $topost = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->curloptSslVerifypeer);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (!empty($topost)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $topost);
        }
        $body = curl_exec($ch);
        curl_close($ch);

        return $body;
    }



    /***********************
     * Getters and Setters *
     ***********************/

    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function setQuery($query)
    {
        $this->query = preg_replace('/\s/', '+', $query);
    }

    public function setRadius($radius)
    {
        $this->radius = $radius;
    }

    public function setTypes($types)
    {
        $this->types = $types;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    public function setSensor($sensor)
    {
        $this->sensor = $sensor;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function setAccuracy($accuracy)
    {
        $this->accuracy = $accuracy;
    }

    public function setIncludeDetails($includeDetails)
    {
        $this->includeDetails = $includeDetails;
    }

    public function setCurloptSslVerifypeer($curloptSslVerifypeer)
    {
        $this->curloptSslVerifypeer = $curloptSslVerifypeer;
    }
}
