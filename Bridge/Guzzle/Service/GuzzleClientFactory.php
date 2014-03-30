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
     * @param DataCollectingSubscriber $collectingSubscriber
     */
    public function __construct(DataCollectingSubscriber $collectingSubscriber)
    {
        $this->collectingSubscriber = $collectingSubscriber;
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
        $client->getEmitter()->attach($this->collectingSubscriber);

        return $client;
    }
}
