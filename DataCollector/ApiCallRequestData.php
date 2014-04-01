<?php

namespace Pinkeen\ApiDebugBundle\DataCollector;

/**
 * Class for storing api call request data.
 */
class ApiCallRequestData extends ApiCallMessageData
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
     * HTTP method name
     *
     * @var string
     */
    private $method;    
    
    /**
     * Url of the endpoint that was called.
     *
     * @var string
     */
    private $url;

    /**
     * {@inheritDoc}
     * @param string $method
     * @param string $url
     */
    public function __construct(array $headers, $body, $method, $url, $length = null)
    {
        parent::__construct($headers, $body, $length);

        $this->method = $method;
        $this->url = $url;
    }

    /**
     * Returns request method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }    

    /**
     * Returns full URL of the API endpoint that was called.
     * 
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
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

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->method,
            $this->url,
            parent::serialize()
        ]);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        list(
            $this->method,
            $this->url,
            $parentData
        ) = unserialize($data);

        parent::unserialize($parentData);
    }       

}