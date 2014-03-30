<?php

namespace Pinkeen\ApiDebugBundle\DataCollector;

/**
 * Base class for storing api call data that is to be displayed in
 * API debug toolbar. 
 */
abstract class AbstractApiCallData implements \Serializable
{
    /**
     * Array of url elements as returned by
     * parse_url()
     * 
     * @var array
     */
    private $urlElements = null;

    /**
     * Array of url query parameters.
     * 
     * @var array
     */
    private $urlQueryParams = null;    

    /**
     * Returns true if API call received a response.
     *
     * @return bool
     */
    abstract public function hasResponse();

    /**
     * Returns request method.
     *
     * @return string
     */
    abstract public function getMethod();

    /**
     * Returns full URL of the API endpoint that was called.
     * 
     * @return string
     */
    abstract public function getUrl();

    /**
     * Returns HTTP resonse code.
     *
     * @return int
     */
    abstract public function getResponseStatusCode();

    /**
     * Returns request headers as an associative array.
     *
     * @return array
     */
    abstract public function getRequestHeaders();

    /**
     * Returns response headers as an associative array.
     *
     * @return array
     */
    abstract public function getResponseHeaders();

    /**
     * Returns the name of the api being called.
     *
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
     * Should return true if the request is possibly ok
     * but it's not certain. This is the case with 404 codes
     * which are common in rest APIs and perfectly normal
     * in some applications.
     * 
     * @return bool
     */
    public function hasWarning()
    {
        if($this->getResponseStatusCode() == 404) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return false !== $this->getErrorString();
    }

    /**
     * @return bool
     */
    public function hasRequestHeaders()
    {
        return null !== $this->getRequestHeaders() && !empty($this->getRequestHeaders());
    }

    /**
     * @return bool
     */
    public function hasResponseHeaders()
    {
        return null !== $this->getResponseHeaders() && !empty($this->getResponseHeaders());
    }    

    /** 
     * @return string
     */
    public function getRequestBody()
    {
        return null;
    }

    /** 
     * @return string
     */
    public function getResponseBody()
    {
        return null;
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
     * @return int
     */
    public function getResponseLength()
    {
        if(
            $this->hasResponseHeaders() && 
            array_key_exists('Content-Length', $this->getResponseHeaders())
        ) {
            return intval($this->getResponseHeaders()['Content-Length']);
        }

        if(null !== $this->getResponseBody()) {
            return strlen($this->getResponseBody());
        }

        return null;        
    }

    /**
     * Returns url elements parsed by parse_url().
     *
     * @return array
     */
    protected function getUrlElements()
    {
        if(null === $this->urlElements) {
            $this->urlElements = parse_url($this->getUrl());
        }

        return $this->urlElements;
    }

    /**
     * Returns named ulr element or null if not exists.
     *
     * @param string $name
     * @return string|null
     */
    protected function getUrlElement($name)
    {
        if(array_key_exists($name, $this->getUrlElements())) {
            return $this->getUrlElements()[$name];
        }

        return null;
    }

    /**
     * @return string
     */
    public function getUrlScheme()
    {
        return $this->getUrlElement('scheme');
    }

    /**
     * @return string
     */
    public function getUrlHost()
    {
        return $this->getUrlElement('host');
    }

    /**
     * @return string
     */
    public function getUrlPort()
    {
        return $this->getUrlElement('port');
    }

    /**
     * @return string
     */
    public function getUrlUser()
    {
        return $this->getUrlElements('user');
    }

    /**
     * @return string
     */
    public function getUrlPassword()
    {
        return $this->getUrlElement('pass');
    }

    /**
     * @return string
     */
    public function getUrlPath()
    {
        return $this->getUrlElement('path');
    }

    /**
     * @return string
     */
    public function getUrlFragment()
    {
        return $this->getUrlElement('fragment');
    }    

    /**
     * @return string
     */
    public function getUrlQueryString()
    {
        return $this->getUrlElement('query');
    }

    /**
     * @return array
     */
    public function getUrlQueryParameters()
    {
        //parse_str($this->getQueryString(), $arr);

        if(null === $this->urlQueryParams) {
            $queryString = $this->getUrlQueryString();

            if(null === $queryString) {
                return $this->urlQueryParams = [];
            }

            $params = [];

            foreach(explode('&', $queryString) as $item) {
                list($name, $value) = explode('=', $item);

                $params[$name] = $value;
            }

            $this->urlQueryParams = $params;
        }

        return $this->urlQueryParams;
    }
}