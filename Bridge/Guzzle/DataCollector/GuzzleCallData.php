<?php

namespace Pinkeen\ApiDebugBundle\Bridge\Guzzle\DataCollector;

use Pinkeen\ApiDebugBundle\DataCollector\Data\AbstractCallData;
use Pinkeen\ApiDebugBundle\DataCollector\Data\CallRequestData;
use Pinkeen\ApiDebugBundle\DataCollector\Data\CallResponseData;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Stream\StreamInterface;
use GuzzleHttp\Stream\GuzzleStreamWrapper;
use GuzzleHttp\Exception\TransferException;

class GuzzleCallData extends AbstractCallData
{
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
        $requestData = new CallRequestData(
            $this->normalizeHeaders($request->getHeaders()),
            $this->normalizeBody($request->getBody()),
            $request->getMethod(),
            $request->getUrl(),
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
     * @return resource|null
     */
    private function normalizeBody(StreamInterface $body = null) 
    {
        if(
            null !== $body &&
            $body->isReadable() && 
            $body->isSeekable()
        ) {
            return GuzzleStreamWrapper::getResource($body);
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
        return 'Guzzle';
    }    

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
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
            $this->errorString,
            $this->totalTime,
            $parentData
        ) = unserialize($data);

        parent::unserialize($parentData);
    }      
}