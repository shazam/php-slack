# PHP Slack #
[Google Sites with basic info](https://sites.google.com/a/shazam.com/infratools/docs/php-slack)

This library allows the developer to interact with Slack.

## Usage ##
```php
$token = "xoxp-2640873306-2668859284-2729881780-250123";
$slack = new PhpSlack\Slack($token);

$slack->createChannel("test-slack-channel-1");
$slack->sendMessage('test-slack-channel-1', 'that is a test message');
$slack->addUserToChannel('test-slack-channel-1', 'bradb');
```
