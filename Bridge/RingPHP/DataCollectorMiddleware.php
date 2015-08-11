<?php

namespace Pinkeen\ApiDebugBundle\Bridge\RingPHP;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Ring\Future\FutureArray;
use GuzzleHttp\Ring\Future\FutureArrayInterface;
use Pinkeen\ApiDebugBundle\ApiEvents;
use Pinkeen\ApiDebugBundle\DataCollector\Data\PsrCallData;
use Pinkeen\ApiDebugBundle\Event\ApiCallEvent;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\Promise\Deferred;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DataCollectorMiddleware
{
    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param callable $handler
     * @return \Closure
     */
    public function createHandler(callable $handler)
    {
        $collector = $this;

        return function (array $request) use ($collector, $handler) {
            $defferred = new Deferred();
            $promise = $defferred->promise();

            $handler($request)->then(
                function(array $response) use ($collector, $request, $defferred) {
                    $collector->collect($request, $response);
                    $defferred->resolve($response);
                },
                function(array $response) use ($collector, $request, $defferred) {
                    $collector->collect($request, $response);
                    $defferred->reject($response);
                }
            );

            return new FutureArray($promise);
        };
    }

    /**
     * @param array $request
     * @return RequestInterface
     */
    protected function transformRequestToMessage(array $request)
    {
        $uri = sprintf("%s://%s%s", $request['scheme'], $request['headers']['host'], $request['uri']);

        return new Request($request['http_method'], $uri, $request['headers'], $request['body']);
    }

    /**
     * @param array $response
     * @return ResponseInterface
     */
    protected function transformResponseToMessage(array $response)
    {
        return new Response($response['status'], $response['headers'], $response['body']);
    }

    /**
     * @param array $request
     * @param array $response
     * @param RequestException $exception
     */
    protected function collect(array $request, array $response = null)
    {

        $this->eventDispatcher->dispatch(
            ApiEvents::CALL,
            new ApiCallEvent(new PsrCallData(
                $this->transformRequestToMessage($request),
                $this->transformResponseToMessage($response),
                $response['error'],
                isset($response['transfer_stats']['total_time']) ? $response['transfer_stats']['total_time'] : null
            ))
        );
    }
}
