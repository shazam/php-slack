<?php

/**
 * @author toni <toni.lopez@shazam.com>
 * @package PhpSlack\Tests
 */

namespace PhpSclack\Tests;

use PhpSlack\Slack;
use PHPUnit_Framework_TestCase;

class SlackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PhpSlack\Utils\RestApiClient
     */
    private $client;

    public function setUp()
    {
        $this->client = $this->getMockBuilder('\PhpSlack\Utils\RestApiClient')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testICanCreateChannel()
    {
        $channelId = 12;

        $this->client
            ->expects($this->once())
            ->method('post')
            ->with('channels.join', array('name' => 'channel-name'))
            ->will($this->returnValue(array('channel' => array('id' => $channelId))));

        $slack = new Slack($this->client);

        $this->assertSame(
            $channelId,
            $slack->createChannel('channel-name'),
            'Channel id does not match.'
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testICannotCreateChannel()
    {
        $channelId = 12;

        $this->client
            ->expects($this->once())
            ->method('post')
            ->with('channels.join', array('name' => 'channel-name'))
            ->will($this->throwException(new \Exception()));

        $slack = new Slack($this->client);

        $slack->createChannel('channel-name');
    }
}
