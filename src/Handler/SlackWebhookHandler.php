<?php

namespace Webthink\MonologSlack\Handler;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Webthink\MonologSlack\Formatter\SlackFormatterInterface;
use Webthink\MonologSlack\Formatter\SlackLineFormatter;
use Webthink\MonologSlack\Utility\ClientInterface;
use Webthink\MonologSlack\Utility\GuzzleClient;

/**
 * Sends notifications through Slack API
 *
 * @author George Mponos <gmponos@gmail.com>
 */
class SlackWebhookHandler extends AbstractProcessingHandler
{
    /**
     * @var string
     */
    private $webhook;

    /**
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $useCustomEmoji;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param string $webhook Slack Webhook string
     * @param string|null $username Name of a bot
     * @param string $useCustomEmoji If you should use custom emoji or not
     * @param int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param ClientInterface|null $client
     */
    public function __construct(
        string $webhook,
        string $username = null,
        string $useCustomEmoji = null,
        int $level = Logger::ERROR,
        bool $bubble = true,
        ClientInterface $client = null,
        float $timeout = 10,
        float $connectTimeout = 10,
        float $readTimeout = 10,
        bool $verify = false,
        string $forceIPResolve = 'v4',
        bool $httpErrors = false
    ) {
        parent::__construct($level, $bubble);

        $this->webhook = $webhook;
        $this->username = $username;
        $this->useCustomEmoji = $useCustomEmoji;

        if ($client === null) {
            $client = new GuzzleClient(
                new Client([
                    RequestOptions::TIMEOUT => $timeout, //Time Guzzle will wait, once a connection has been made, for the server to handle the request. For example waiting on a long running script.
                    RequestOptions::CONNECT_TIMEOUT => $connectTimeout, //Time Guzzle will wait to establish a connection to the server
                    RequestOptions::HTTP_ERRORS => $httpErrors,
                    RequestOptions::FORCE_IP_RESOLVE => $forceIPResolve,
                    RequestOptions::READ_TIMEOUT => $readTimeout,
                    RequestOptions::VERIFY => $verify
                ])
            );
        }
        $this->client = $client;
    }

    /**
     * @param FormatterInterface $formatter
     * @return $this|\Monolog\Handler\HandlerInterface
     * @throws \InvalidArgumentException
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        if (!$formatter instanceof SlackFormatterInterface) {
            throw new \InvalidArgumentException('Expected a slack formatter');
        }
        return parent::setFormatter($formatter);
    }

    /**
     * @param array $record
     * @return void
     * @throws \Webthink\MonologSlack\Utility\Exception\TransferException
     */
    protected function write(array $record)
    {
        $this->client->send($this->webhook, $record['formatted']);
    }

    /**
     * @return SlackLineFormatter
     */
    protected function getDefaultFormatter()
    {
        return new SlackLineFormatter($this->username, $this->useCustomEmoji);
    }
}
