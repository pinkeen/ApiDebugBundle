<?php

namespace Pinkeen\ApiDebugBundle\Bridge\Guzzle\DataCollector;

use Pinkeen\ApiDebugBundle\DataCollector\AbstractApiCallData;
use Pinkeen\ApiDebugBundle\DataCollector\ApiCallRequestData;
use Pinkeen\ApiDebugBundle\DataCollector\ApiCallResponseData;

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
     * @var string
     */
    private $apiName;

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
        $requestData = new ApiCallRequestData(
            $this->normalizeHeaders($request->getHeaders()),
            $this->normalizeBody($request->getBody()),
            $request->getMethod(),
            $request->getUrl(),
            $request->getBody() ? $request->getBody()->getSize() : null
        );

        $responseData = null;

        if(null !== $response) {
            $responseData = new ApiCallResponseData(
                $this->normalizeHeaders($response->getHeaders()),
                $this->normalizeBody($response->getBody()),
                $response->getStatusCode(),
                $response->getBody() ? $response->getBody()->getSize() : null
            );
        }

        parent::__construct($requestData, $responseData);

        if(null !== $exception) {
            $this->errorString = get_class($exception) . ": " . $exception->getMessage();
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
    public function getApiName()
    {
        return 'Guzzle';
    }    

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->apiName,
            $this->errorString,
            $this->totalTime,
            parent::serialize()
        ]);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        list(
            $this->apiName,
            $this->errorString,
            $this->totalTime,
            $parentData
        ) = unserialize($data);

        parent::unserialize($parentData);
    }      
}