<?php

namespace Pinkeen\ApiDebugBundle\DataCollector\Data;

/**
 * Base class for storing api call data that is to be displayed in
 * API debug toolbar. 
 */
abstract class AbstractApiCallData implements \Serializable
{
    /**
     * @var ApiCallRequestData
     */
    private $responseData = null;

    /**
     * @var ApiCallResponseData
     */
    private $requestData = null;

    /**
     * @param ApiCallMessageData $requestData
     */
    public function __construct(ApiCallRequestData $requestData, ApiCallResponseData $responseData = null)
    {
        $this->requestData = $requestData;
        $this->responseData = $responseData;
    }

    /**
     * @return string
     */
    abstract public function getApiName();

    /**
     * Returns error string if there was an error
     * making the request. Returns false otherwise.
     * 
     * See hasWarning first!
     *
     * @return string|false
     */
    abstract public function getErrorString();

    /**
     * Returns request data.
     *
     * @return ApiCallRequestData
     */
    public function getRequestData()
    {
        return $this->requestData;
    }

    /**
     * Returns request data.
     *
     * @return ApiCallResponseData
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * Returns true if API call received a response.
     *
     * @return bool
     */
    public function hasResponse() 
    {
        return null !== $this->responseData;
    }    

    /**
     * Returns the total time (in seconds) spent for processing this request or false.
     *
     * @return float|false
     */
    public function getTotalTime()
    {
        return false;
    }    

    /**
     * Should return true for 3xx status codes and
     * other suspicious responses.
     * 
     * @return bool
     */
    public function hasWarning()
    {
        if(!$this->hasResponse()) {
            return false;
        }

        if(in_array($this->getResponseData()->getStatusCodeLevel(), [1, 3])) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return 
            false !== $this->getErrorString() || 
            !$this->hasResponse() ||
            in_array($this->getResponseData()->getStatusCodeLevel(), [4, 5])
        ;
    }

    /**
     * Serializes data to string.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->requestData,
            $this->responseData
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
            $this->requestData,
            $this->responseData
        ) = unserialize($data);
    }    
}