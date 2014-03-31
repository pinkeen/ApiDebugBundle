<?php

namespace Pinkeen\ApiDebugBundle\Bridge\Guzzle\DataCollector;

use Pinkeen\ApiDebugBundle\DataCollector\AbstractApiCallData;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Stream\StreamInterface;
use GuzzleHttp\Exception\TransferException;

class GuzzleApiCallData extends AbstractApiCallData
{
    /**
     * Size limit of the req/resp body.
     */
    const BODY_SIZE_LIMIT = 4096;

    /**
     * @var bool
     */
    private $hasResponse = false;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $requestHeaders;

    /**
     * @var array
     */
    private $responseHeaders;   

    /**
     * @var string
     */
    private $statusCode; 

    /**
     * @var string
     */
    private $apiName;

    /**
     * @var string
     */
    private $requestBody = null;

    /**
     * @var string
     */
    private $responseBody = null;

    /**
     * @var string
     */
    private $errorString = false;

    /**
     * @var int
     */
    private $totalTime = false;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param TransferException $exception
     * @param array $transferInfo     
     */
    public function __construct(RequestInterface $request, ResponseInterface $response = null, TransferException $exception = null, array $transferInfo = null)
    {
        $this->method = $request->getMethod();
        $this->url = $request->getUrl();
        $this->requestHeaders = $this->normalizeHeaders($request->getHeaders());
        $this->requestBody = $this->normalizeBody($request->getBody());

        if(null !== $exception) {
            $this->errorString = get_class($exception) . ": " . $exception->getMessage();
        }

        if(null !== $response) {
            $this->hasResponse = true;
            $this->statusCode = $response->getStatusCode();
            $this->responseHeaders = $this->normalizeHeaders($response->getHeaders());
            $this->responseBody = $this->normalizeBody($response->getBody());
        }

        if(null !== $transferInfo && isset($transferInfo['total_time'])) {
            $this->totalTime = $transferInfo['total_time'];
        }
    }

    /**
     * Checks if the body can be stored and returns it as string
     * or null if not possible.
     *
     * @param StreamInterface $body
     * @return string|null
     */
    private function normalizeBody(StreamInterface $body = null) 
    {
        if(
            null !== $body &&
            $body->isReadable() && 
            $body->isSeekable() && 
            $body->getSize() <= self::BODY_SIZE_LIMIT
        ) {
            return strval($body);
        }

        return null;
    }

    /**
     * Normalize guzzle headers.
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
     * {@inheritDoc}
     */
    public function getErrorString()
    {
        return $this->errorString;
    }    

    /**
     * {@inheritDoc}
     */
    public function hasResponse()
    {
        return $this->hasResponse;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestHeaders()
    {
        return $this->requestHeaders;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /** 
     * @return string
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }
    
    /** 
     * @return string
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * @return string
     */
    public function getbody()
    {
        return $this->body;
    }    

    /**
     * {@inheritDoc}
     */
    public function getApiName()
    {
        return 'Guzzle';
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }    

    /**
     * Serializes data to string.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->hasResponse,
            $this->method,
            $this->url,
            $this->requestHeaders,
            $this->responseHeaders,
            $this->statusCode,
            $this->apiName,
            $this->requestBody,
            $this->responseBody,
            $this->errorString,
            $this->totalTime
        ]);
    }

    /**
     * Unserializes the data.
     *
     * @param string $data
     */
    public function unserialize($data)
    {
        list(
            $this->hasResponse,
            $this->method,
            $this->url,
            $this->requestHeaders,
            $this->responseHeaders,
            $this->statusCode,
            $this->apiName,
            $this->requestBody,
            $this->responseBody,
            $this->errorString,
            $this->totalTime
        ) = unserialize($data);
    }
}