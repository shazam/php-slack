<?php

/**
 * @package PhpSlack
 * @author toni <toni.lopez@shazam.com
 */
namespace PhpSlack;

use Exception;
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
    private static $userIds;

    /**
     * @var array
     */
    private static $channels;

    /**
     * @param RestApiClient $client
     */
    public function __construct(RestApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $channelName
     * @return string channel id
     */
    public function createChannel($channelName)
    {
        $response = $this->client->post('channels.join', array('name' => $channelName));

        self::$channels[$channelName] = $response['channel']['id'];

        return $response['channel']['id'];
    }

    /**
     * @param string $channelName
     * @param string $message
     * @param string $mentionedUser
     */
    public function sendMessage($channelName, $message, $mentionedUser = '')
    {
        if (!empty($mentionedUser)) {
            $users = $this->getUsers();
            if (!isset($users[$mentionedUser])) {
                throw new Exception('User not found.');
            }

            $mention = '@' . self::$users[$mentionedUser]['name'];
            $message = sprintf($message, $mention);
        }

        if (!isset(self::$channels[$channelName])) {
            self::$channels = $this->getChannels();

            if (!isset(self::$channels[$channelName])) {
                throw new Exception('Channel not found.');
            }
        }

        $params = array(
            'channel' => self::$channels[$channelName],
            'text' => $message,
            'link_names' => 1
        );

        $this->client->post('chat.postMessage', $params);
    }

    /**
     * @param string $channelName
     * @param string $userEmail
     */
    public function addUserToChannel($channelName, $userEmail)
    {
        $users = $this->getUsers();
        if (!isset($users[$userEmail])) {
            throw new Exception('User not found.');
        }

        if (!isset(self::$channels[$channelName])) {
            self::$channels = $this->getChannels();

            if (!isset(self::$channels[$channelName])) {
                throw new Exception('Channel not found.');
            }
        }

        $params = array(
            'channel' => self::$channels[$channelName],
            'user' => $users[$userEmail]['id']
        );
        $this->client->post('channels.invite', $params);
    }

    /*
     * @param string $channelName
     * @param int $fromTimestamp
     * @return array
     */
    public function getChannelMessages($channelName, $fromTimestamp = 0)
    {
        $users = $this->getUsers();
        if (!isset(self::$channels[$channelName])) {
            self::$channels = $this->getChannels();

            if (!isset(self::$channels[$channelName])) {
                throw new Exception('Channel not found.');
            }
        }

        $params = array(
            'channel' => self::$channels[$channelName],
            'oldest' => $fromTimestamp,
            'count' => 1000
        );
        $rawMessages = $this->client->post('channels.history', $params);

        $messages = array();
        foreach ($rawMessages['messages'] as $rawMessage) {
            if (!isset($rawMessage['text'])) {
                continue;
            }
            $user = isset($rawMessage['user']) ? self::$userIds[$rawMessage['user']] : $rawMessage['username'];
            $message = array(
                'text' => $rawMessage['text'],
                'user' => $user,
                'time' => date('Y-m-d H:i:s', $rawMessage['ts'])
            );

            if (isset($rawMessage['file'])) {
                $message['file'] = $rawMessage['file']['url'];
                $pos = strpos($message['text'], 'and commented: ');
                $message['text'] = $pos === false
                    ? ''
                    : substr($message['text'], $pos + strlen('and commented: '));
            }

            $messages[] = $message;
        }

        return $messages;
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
                $users[$user['profile']['email']] = array(
                    'id' => $user['id'],
                    'name' => $user['name']
                );
                $userIds[$user['id']] = $user['profile']['email'];
            }

            self::$users = $users;
            self::$userIds = $userIds;
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
