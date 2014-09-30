<?php

/**
 * @author toni <toni.lopez@shazam.com>
 * @package PhpSlack\Tests
 */

namespace PhpSclack\Tests;

use PhpSlack\Slack;
use PhpSlack\Utils\RestApiClient;
use PHPUnit_Framework_TestCase;

class SlackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RestApiClient
     */
    private $client;

    public function setUp()
    {
        $this->client = $this->getMock('PhpSlack\Slack\RestApiClient');
    }

    public function testNothing()
    {
        $this->assertTrue(true);
    }
}
