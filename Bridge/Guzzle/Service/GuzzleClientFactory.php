<?php

namespace Pinkeen\ApiDebugBundle\Bridge\Guzzle\Service;

use GuzzleHttp;

use Pinkeen\ApiDebugBundle\Bridge\Guzzle\Subscriber\DataCollectingSubscriber;

/**
 * Fabricates Guzzle clients with bundle
 * integration preconfigured.
 */
class GuzzleClientFactory
{
    /**
     * @var DataCollectingSubscriber
     */
    protected $collectingSubscriber;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @param DataCollectingSubscriber $collectingSubscriber
     * @param bool $debug In debug mode?
     */
    public function __construct(DataCollectingSubscriber $collectingSubscriber, $debug)
    {
        $this->collectingSubscriber = $collectingSubscriber;
        $this->debug = $debug;
    }

    /**
     * Creates a new Guzzle client.
     *
     * The client is automatically integrated with
     * additional services like request logging.
     *
     * @param array $config
     * @return GuzzleHttp\Client
     */
    public function create($config = [])
    {
        $client = new GuzzleHttp\Client($config);

        if($this->debug) {
            $client->getEmitter()->attach($this->collectingSubscriber);
        }

        return $client;
    }
}
