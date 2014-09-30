<?php

/**
 * @package PhpSlack\Utils
 * @author toni <toni.lopez@shazam.com
 */

namespace PhpSlack\Utils;

use Common\Config;
use Exception;

class RestApiClient
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param string
     */
    private $token;

    public function __construct($baseUrl, $token)
    {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
    }

    /**
     * @param string $path
     * @param array $params
     * @return array
     * @return array
     */
    public function get($path, $params = array())
    {
        $params['token'] = $this->token;

        $pairs = array();
        foreach ($params as $key => $value) {
            $pairs[] = "$key=$value";
        }

        $path .= '?' . implode('&', $pairs);

        return $this->query($path, 'GET');
    }

    /**
     * @param string $path
     * @param array $params
     * @return array
     */
    public function post($path, $params = array())
    {
        $path .= '?token=' . $this->token;

        return $this->query($path, 'POST', $params);
    }

    /**
     * @param string $path
     * @param string $method GET|POST
     * @param array $params
     * @return array
     */
    private function query($path, $method, $params = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        $response = curl_exec($ch);
        curl_close($ch);

        $jsonResponse = json_decode($response, true);

        if (!$jsonResponse['ok']) {
            throw new Exception($jsonResponse['error']);
        }

        return $jsonResponse;
    }
}
