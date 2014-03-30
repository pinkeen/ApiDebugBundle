<?php

namespace Pinkeen\ApiDebugBundle\Bridge\Guzzle\DataCollector;

use Pinkeen\ApiDebugBundle\DataCollector\ApiCallDataInterface;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

class GuzzleApiCallData implements ApiCallDataInterface
{
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
     * @var int
     */
    private $statusCode; 

    /**
     * @var string
     */
    private $apiName;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(RequestInterface $request, ResponseInterface $response = null)
    {
        $this->method = $request->getMethod();
        $this->url = $request->getUrl();
        $this->requestHeaders = $request->getHeaders();

        if(null !== $response) {
            $this->hasResponse = true;
            $this->responseHeaders = $response->getHeaders();
            $this->statusCode = intval($response->getStatusCode());
        }
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
        return $this->method();
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
     * {@inheritDoc}
     */
    public function getApiName()
    {
        return $this->apiName;
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
            $this->apiName
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
            $this->apiName
        ) = unserialize($data);
    }
}