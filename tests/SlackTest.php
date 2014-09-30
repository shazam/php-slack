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

    public function testSendMessageRequestChannels()
    {
        $channelId = 13;
        $channels = array('channels' => array(array('id' => 13, 'name' => 'channel-name-1')));

        $this->client
            ->expects($this->once())
            ->method('get')
            ->with('channels.list')
            ->will($this->returnValue($channels));
        $this->client
            ->expects($this->once())
            ->method('post')
            ->with('chat.postMessage', array('channel' => $channelId, 'text' => 'a text', 'link_names' => 1))
            ->will($this->returnValue(array('channel' => array('id' => $channelId))));

        $slack = new Slack($this->client);

        $slack->sendMessage('channel-name-1', 'a text');
    }

    /**
     * @expectedException \Exception
     */
    public function testSendMessageChannelNotFound()
    {
        $channelId = 13;
        $channels = array('channels' => array());

        $this->client
            ->expects($this->once())
            ->method('get')
            ->with('channels.list')
            ->will($this->returnValue($channels));

        $slack = new Slack($this->client);

        $slack->sendMessage('channel-name-2', 'a text');
    }

    /**
     * @expectedException \Exception
     */
    public function testAddUserToChannelUserNotFound()
    {
        $users = array('members' => array());

        $this->client
            ->expects($this->once())
            ->method('get')
            ->with('users.list')
            ->will($this->returnValue($users));

        $slack = new Slack($this->client);

        $slack->addUserToChannel('channel-name-1', 'user');
    }

    public function testSendMessageKnownChannel()
    {
        $channelId = 13;
        $channels = array('channels' => array(array('id' => 13, 'name' => 'channel-name-1')));
        $users = array(
            'members' => array(
                array('id' => 123, 'name' => 'tools', 'profile' => array('email'  => 'tools@shazam.com'))
            )
        );

        $this->client
            ->expects($this->at(0))
            ->method('get')
            ->with('users.list')
            ->will($this->returnValue($users));
        $this->client
            ->expects($this->at(1))
            ->method('get')
            ->with('channels.list')
            ->will($this->returnValue($channels));
        $this->client
            ->expects($this->exactly(2))
            ->method('post')
            ->with('chat.postMessage', array('channel' => $channelId, 'text' => 'for @tools.', 'link_names' => 1))
            ->will($this->returnValue(array('channel' => array('id' => $channelId))));

        $slack = new Slack($this->client);

        $slack->sendMessage('channel-name-1', 'for %s.', 'tools@shazam.com');

        $this->client
            ->expects($this->never())
            ->method('get');

        $slack->sendMessage('channel-name-1', 'for %s.', 'tools@shazam.com');
    }

    public function testAddUserToChannels()
    {
        $channels = array('channels' => array(array('id' => 456, 'name' => 'channel-name-6')));

        $this->client
            ->expects($this->at(0))
            ->method('get')
            ->with('channels.list')
            ->will($this->returnValue($channels));
        $this->client
            ->expects($this->at(1))
            ->method('post')
            ->with('channels.invite', array('channel' => 456, 'user' => 123));

        $slack = new Slack($this->client);

        $slack->addUserToChannel('channel-name-6', 'tools@shazam.com');
    }

    /**
     * @expectedException \Exception
     */
    public function testAddUserToChannelChannelNotFound()
    {
        $channels = array('channels' => array());

        $this->client
            ->expects($this->at(0))
            ->method('get')
            ->with('channels.list')
            ->will($this->returnValue($channels));

        $slack = new Slack($this->client);

        $slack->addUserToChannel('channel-name-4', 'tools@shazam.com');
    }
}
