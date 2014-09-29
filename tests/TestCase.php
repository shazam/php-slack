<?php

/**
 * @package PhpSlack\Tests
 * @author toni <toni.lopez@shazam.com>
 */

namespace PhpSlack\Tests;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    /**
     * @param array $params
     * @return Request
     */
    protected function buildRequest(array $params)
    {
        return new Request(
            array(), // get params
            $params, // post params
            array(), // attributes
            array(), // cookies
            array(), // files
            array(), // server
            json_encode($params) // body data
        );
    }

    /**
     * @param string $url
     * @return string
     */
    protected function request($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $jsonResponse = json_decode($response, true);

        return array(
            $jsonResponse === null ? $response : $jsonResponse,
            $statusCode
        );
    }
}
