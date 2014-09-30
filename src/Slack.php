<?php

/**
 * @package PhpSlack
 * @author toni <toni.lopez@shazam.com
 */
namespace PhpSlack;

use PhpSlack\Utils\RestApiClient;

class Slack
{
    /**
     * @const string
     */
    const BASE_URL = 'https://slack.com/api/';

    /**
     * @var RestApiClient
     */
    private $client;

    /**
     * @var array
     */
    private static $users;

    /**
     * @var array
     */
    private static $channels;

    /**
     * @param string $token
     */
    public function __construct($token)
    {
        $this->client = new RestApiClient(self::BASE_URL, $token);
    }

    /**
     * @param string $channelName
     * @return string channel id
     */
    public function createChannel($channelName)
    {
        $response = $this->client->post('channels.join', array('name' => $channelName));
        self::$channels[$channelName] = $response['channel']['id'];
    }

    /**
     * @param string $channelName
     * @param string $message
     * 
     */
    public function sendMessage($channelName, $message)
    {
        if (!isset(self::$channels[$channelName])) {
            self::$channels = $this->getChannels();
        }

        $params = array(
            'channel' => self::$channels[$channelName],
            'text' => $message
        );

        $this->client->post('chat.postMessage', $params);
    }

    /**
     * @param string $channelName
     * @param string $userName
     */
    public function addUserToChannel($channelName, $userName)
    {
        $users = $this->getUsers();
        if (!isset($users[$userName])) {
            throw new Exception('User not found.');
        }

        if (!isset(self::$channels[$channelName])) {
            self::$channels = $this->getChannels();
        }

        $params = array(
            'channel' => self::$channels[$channelName],
            'user' => $users[$userName]
        );
        $this->client->post('channels.invite', $params);
    }

    /**
     * @return array of users
     */
    public function getUsers()
    {
        if (empty(self::$users)) {
            $response = $this->client->get('users.list');

            $users = array();
            foreach ($response['members'] as $user) {
                $users[$user['name']] = $user['id'];
            }

            self::$users = $users;
        }

        return self::$users;
    }

    /**
     * @return array of channels
     */
    public function getChannels()
    {
        $response = $this->client->get('channels.list');

        $channels = array();
        foreach ($response['channels'] as $channel) {
            $channels[$channel['name']] = $channel['id'];
        }

        return $channels;
    }
}
