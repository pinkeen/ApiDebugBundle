<?php

namespace Pinkeen\ApiDebugBundle\Bridge\Guzzle\Service;

use GuzzleHttp;
use Pinkeen\ApiDebugBundle\Bridge\Guzzle\Middleware\DataCollectorMiddleware;

/**
 * Fabricates Guzzle clients with bundle
 * integration preconfigured.
 */
class GuzzleClientFactory
{
    /**
     * @var DataCollectorMiddleware
     */
    protected $collector;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @param DataCollectorMiddleware $collector
     * @param bool $debug In debug mode?
     */
    public function __construct(DataCollectorMiddleware $collector, $debug)
    {
        $this->collector = $collector;
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
        if ($this->debug) {
            if (isset($config['handler']) && $config['handler'] instanceof GuzzleHttp\HandlerStack) {
                /** @var GuzzleHttp\HandlerStack $handler */
                $handler = $config['handler'];
                $handler->push($this->collector->getHandler());
            } else {
                $handler = new GuzzleHttp\HandlerStack(new GuzzleHttp\Handler\CurlHandler());
                $handler->push($this->collector->getHandler());
                $config['handler'] = $handler;
            }
        }

        return new GuzzleHttp\Client($config);
    }
}
