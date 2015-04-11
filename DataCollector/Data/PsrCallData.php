<?php

namespace Pinkeen\ApiDebugBundle\DataCollector\Data;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Call data that can be created with request/response implementing PSR-7.
 */
class PsrCallData extends AbstractCallData
{
    /**
     * @var string
     */
    private $errorString = null;

    /**
     * @var float
     */
    private $totalTime = null;

    /**
     * @var string
     */
    protected $apiName;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param \Exception $exception
     * @param float $totalTime Time it took to exeute request.
     * @param string $apiName
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response = null,
        \Exception $exception = null,
        $totalTime = null,
        $apiName = null
    ) {
        $requestData = new CallRequestData(
            $this->normalizeHeaders($request->getHeaders()),
            $this->normalizeBody($request->getBody()),
            $request->getMethod(),
            (string)$request->getUri(),
            $request->getBody() ? $request->getBody()->getSize() : null
        );

        $responseData = null;

        if(null !== $response) {
            $responseData = new CallResponseData(
                $this->normalizeHeaders($response->getHeaders()),
                $this->normalizeBody($response->getBody()),
                $response->getStatusCode(),
                $response->getBody() ? $response->getBody()->getSize() : null
            );
        }

        if(null !== $exception) {
            $this->errorString = get_class($exception) . ": " . $exception->getMessage();
        }

        $this->totalTime = $totalTime;

        parent::__construct($requestData, $responseData);
    }

    /**
     * Checks if the body can be stored and returns it as string
     * or null if not possible.
     *
     * @param StreamInterface $body
     * @return resource|string|null
     */
    private function normalizeBody(StreamInterface $body = null)
    {
        if(
            null !== $body &&
            $body->isReadable() &&
            $body->isSeekable() &&
            $body->getSize() !== 0
        ) {
            $body->rewind();
            return $body->getContents();
        }

        return null;
    }

    /**
     * Normalize headers.
     *
     * @param array $headers
     * @return array
     */
    private function normalizeHeaders(array $headers = null)
    {
        if(null !== $headers) {
            return array_map(function($x) { return implode(', ', $x); }, $headers);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }

    /**
     * {@inheritDoc}
     */
    public function getErrorString()
    {
        return $this->errorString;
    }

    /**
     * {@inheritDoc}
     */
    public function getApiName()
    {
        return $this->apiName;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->errorString,
            $this->totalTime,
            $this->apiName,
            parent::serialize()
        ]);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        list(
            $this->errorString,
            $this->totalTime,
            $this->apiName,
            $parentData
        ) = unserialize($data);

        parent::unserialize($parentData);
    }
}
