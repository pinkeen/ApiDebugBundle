<?php

namespace Pinkeen\ApiDebugBundle\DataCollector;

/**
 * Interface for objects storing data from an API call.
 */
interface ApiCallDataInterface extends \Serializable
{
    /**
     * Returns true if API call received a response.
     *
     * @return bool
     */
    public function hasResponse();

    /**
     * Returns request method.
     *
     * @return string
     */
    public function getMethod();

    /**
     * Returns full URL of the API endpoint that was called.
     * 
     * @return string
     */
    public function getUrl();

    /**
     * Returns HTTP resonse code.
     *
     * @return int
     */
    public function getResponseStatusCode();

    /**
     * Returns request headers as an associative array.
     *
     * @return array
     */
    public function getRequestHeaders();

    /**
     * Returns response headers as an associative array.
     *
     * @return array
     */
    public function getResponseHeaders();

    /**
     * Returns the name of the api being called.
     *
     * @return string
     */
    public function getApiName();
}