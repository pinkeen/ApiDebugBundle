<?php

namespace Pinkeen\ApiDebugBundle\Bridge\RingPHP\Middleware;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Ring\Future\CompletedFutureArray;
use GuzzleHttp\Ring\Future\FutureArray;
use GuzzleHttp\Ring\Future\FutureArrayInterface;
use GuzzleHttp\Tests\Ring\Future\CompletedFutureArrayTest;
use Pinkeen\ApiDebugBundle\ApiEvents;
use Pinkeen\ApiDebugBundle\DataCollector\Data\PsrCallData;
use Pinkeen\ApiDebugBundle\Event\ApiCallEvent;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
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
     * @param string|null $apiName
     * @return \Closure
     */
    public function createHandler(callable $handler, $apiName = null)
    {
        $collector = $this;

        return function (array $request) use ($collector, $handler, $apiName) {
            /** @var PromiseInterface $promise */
            $promise = $handler($request);

            $promise->then(
                function(array $response) use ($collector, $request, $apiName) {
                    $collector->collect($request, $response, $apiName);
                },
                function(array $response) use ($collector, $request, $apiName) {
                    $collector->collect($request, $response, $apiName);
                }
            );

            return $promise;
        };
    }

    protected function transformBody($body)
    {
        /* Copying the body to temp, because for some reason the original resource sometimes get destroyed
         * somewhere in the guzzle PSR-7 Response object. */
        if (is_resource($body)) {
            $temp = fopen('php://temp', 'w+');
            stream_copy_to_stream($body, $temp);
            fseek($temp, 0);
            fseek($body, 0);
            return $temp;
        }

        return $body;
    }

    /**
     * @param array $request
     * @return RequestInterface
     */
    protected function transformRequestToMessage(array $request)
    {
        $uri = sprintf("%s://%s%s",
            isset($request['scheme']) ? $request['scheme'] : 'http',
            $request['headers']['host'][0],
            $request['uri']
        );

        return new Request(
            isset($request['http_method']) ? $request['http_method'] : 'GET',
            $uri,
            $request['headers'],
            isset($request['body']) ? $this->transformBody($request['body']) : null
        );
    }

    /**
     * @param array $response
     * @return ResponseInterface
     */
    protected function transformResponseToMessage(array $response)
    {
        return new Response(
            $response['status'],
            $response['headers'],
            isset($response['body']) ? $this->transformBody($response['body']) : null
        );
    }

    /**
     * @param array $request
     * @param array $response
     * @param string|null $apiName
     * @internal param RequestException $exception
     */
    protected function collect(array $request, array $response = null, $apiName = null)
    {
        $this->eventDispatcher->dispatch(
            ApiEvents::CALL,
            new ApiCallEvent(new PsrCallData(
                $this->transformRequestToMessage($request),
                $this->transformResponseToMessage($response),
                isset($response['error']) ? $response['error'] : null,
                isset($response['transfer_stats']['total_time']) ? $response['transfer_stats']['total_time'] : null,
                $apiName
            ))
        );
    }
}
