<?php


namespace Pinkeen\ApiDebugBundle\Bridge\RingPHP\Service;

use GuzzleHttp\Ring\Client\CurlHandler;
use Pinkeen\ApiDebugBundle\Bridge\RingPHP\Middleware\DataCollectorMiddleware;

class RingPHPHandlerFactory
{
    /**
     * @var DataCollectorMiddleware
     */
    private $middleware;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @param DataCollectorMiddleware $middleware
     * @param bool $debug
     */
    public function __construct(DataCollectorMiddleware $middleware, $debug)
    {
        $this->middleware = $middleware;
        $this->debug = $debug;
    }

    /**
     * Wraps around base handler if in debug mode.
     *
     * CurlHandler is used by default.
     *
     * @param callable|null $baseHandler
     * @param string|null $apiName
     * @return callable
     */
    public function create($baseHandler = null, $apiName = null)
    {
        if (null === $baseHandler) {
            $baseHandler = new CurlHandler();
        }

        if (!$this->debug) {
            return $baseHandler;
        }

        return $this->middleware->createHandler($baseHandler, $apiName);
    }
}