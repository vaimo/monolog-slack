<?php

namespace Webthink\MonologSlack\Utility;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Psr7;
use Webthink\MonologSlack\Utility\Exception\TransferException;

/**
 * This is a class that wraps a Guzzle Client in order to send records to slack.
 */
class GuzzleClient implements ClientInterface
{
    /**
     * @var GuzzleClientInterface
     */
    private $client;

    /**
     * @param GuzzleClientInterface $client
     */
    public function __construct(GuzzleClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $webhook
     * @param array $data
     * @throws TransferException
     * @return void
     */
    public function send(string $webhook, array $data)
    {
        try {
            $this->client->request('post', $webhook, [
                RequestOptions::JSON => $data,
            ]);
        } catch (RequestException $e) {
            // Do nothing for now
            //echo "REQUEST: " . Psr7\str($e->getRequest()) . PHP_EOL;
            //exit(0);
        } catch (GuzzleException $e) {
            // Do nothing for now
            //throw new TransferException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
