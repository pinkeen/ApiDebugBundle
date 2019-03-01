<?php

namespace Pinkeen\ApiDebugBundle\Bridge\Guzzle\Middleware;

use GuzzleHttp\Exception\RequestException;
use Pinkeen\ApiDebugBundle\ApiEvents;
use Pinkeen\ApiDebugBundle\DataCollector\Data\PsrCallData;
use Pinkeen\ApiDebugBundle\Event\ApiCallEvent;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use GuzzleHttp\Promise;

class DataCollectorMiddleware
{
    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getHandler()
    {
        $collector = $this;

        return function (callable $handler) use ($collector) {
            return function ($request, array $options) use ($collector, $handler) {
                return $handler($request, $options)->then(
                    function ($response) use ($request, $collector, $options) {
                        $collector->onSuccess($request, $response, $options);
                        return $response;
                    },
                    function ($reason) use ($request, $collector, $options) {
                        $collector->onError($reason, $request, $options);
                        return Promise\rejection_for($reason);
                    }
                );
            };
        };
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $options
     */
    protected function onSuccess(RequestInterface $request, ResponseInterface $response, array $options)
    {
        $this->collect($request, $response, $options, null);
    }

    /**
     * @param $reason
     * @param RequestInterface $request
     * @param array $options
     */
    protected function onError($reason, RequestInterface $request, array $options)
    {
        $exception = $reason instanceof RequestException ? $reason : null;
        $response = $exception ? $exception->getResponse() : null;
        $this->collect($request, $response, $options, $exception);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $options
     * @param RequestException $exception
     */
    protected function collect(RequestInterface $request, ResponseInterface $response = null, array $options, RequestException $exception = null)
    {
        $this->eventDispatcher->dispatch(
            ApiEvents::CALL,
            new ApiCallEvent(new PsrCallData($request, $response, $exception))
        );
    }
}
