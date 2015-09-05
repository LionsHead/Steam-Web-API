<?php

namespace SteamApi;

/**
 * This class sending request to Steam web api service
 */
class Request {

    public $lastTimer = 0; // timer the last request

    const CURL_TIMEOUT = 120; // maximum! seconds to allow cURL functions to execute.
    const CURL_CONNECTTIMEOUT = 5; // wacht max 5 sec voor connectie server

    private $STEAM_API_LNG = 'ru'; // language format ISO639-1 English (en, en_US), Russian (ru, ru_RU)
    private $STEAM_API_FORMAT = 'json'; //json (default), xml, vdf - Valve Data Format
    private $STEAM_API_KEY = ''; // see www.steamcommunity.com/dev/apikey

    const ONLY_REQUIRED = TRUE; // only the passed parameters

    public function __construct($key = NULL) {
        if (!is_null($key))
            $this->STEAM_API_KEY = $key;
    }

    /**
     *
     * @param string api urls
     * @param array $params
     * @param bool only required parametrs
     * @return array result requests
     */
    public function send($url = NULL, array $params = [], $only_required = FALSE) {
        $time = microtime(TRUE);
        $this->lastTimer = 0;

        // required parameters
        if (!$only_required) {
            $params['key'] = $this->STEAM_API_KEY;
            $params['format'] = $this->STEAM_API_FORMAT;
            $params['language'] = $this->STEAM_API_LNG;
        }
        // formatting query url
        $url = $url . '?';
        foreach ($params as $key => $value) {
            if (!is_null($value)) {
                $url .= $key . '=' . $value . '&';
            }
        }

        // send request
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            // degug
            // CURLOPT_NOPROGRESS => FALSE,
            // CURLOPT_VERBOSE => TRUE,
            //connect
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_CONNECTTIMEOUT => Request::CURL_CONNECTTIMEOUT,
            CURLOPT_TIMEOUT => Request::CURL_TIMEOUT
        ]);

        $response = curl_exec($curl) or die(curl_error($curl));
        curl_close($curl);

        // return result
        $return = json_decode($response, TRUE, 128, JSON_BIGINT_AS_STRING);

        $this->lastTimer = microtime(TRUE) - $time;
        return $return;
    }

}

