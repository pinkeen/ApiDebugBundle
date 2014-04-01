<?php

namespace Pinkeen\ApiDebugBundle\DataCollector;

/**
 * Class for storing data common between 
 * api call request and response.
 */
class ApiCallMessageData implements \Serializable
{
    /**
     * @var array
     */
    private $headers = null;

    /**
     * @var string
     */
    private $body = null;

    /**
     * @var int
     */
    private $length = null;

    /**
     * You can pass the length in case there is no Content-Length header
     * and body had to be discarded for various concerns.
     *
     * @param array $headers
     * @param string $body
     * @param int $length
     */
    public function __construct(array $headers, $body, $length = null)
    {
        $this->headers = $headers;
        $this->body = $body;
        $this->length = $length;
    }

    /**
     * @return bool
     */ 
    public function hasHeaders()
    {
        return null !== $this->headers && !empty($this->headers);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return bool
     */
    public function hasBody() 
    {
        return null !== $this->body;
    }

    /**
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader($name)
    {
        return array_key_exists($name, $this->headers);
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getHeader($name)
    {
        if(!$this->hasHeader($name)) {
            return null;
        }

        return $this->headers[$name];
    }

    /**
     * Returns body length in bytes or null
     * if not available.
     *
     * @return int
     */
    public function getLength()
    {
        if(null === $this->length) {
            if($this->hasHeader('Content-Length')) {
                return $this->length = intval($this->getHeader('Content-Length'));
            }

            if(null !== $this->getBody()) {
                return $this->length = strlen($this->getBody());
            }
        }

        return $this->length;        
    }        

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->headers,
            $this->body,
            $this->length
        ]);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        list(
            $this->headers,
            $this->body,
            $this->length
        ) = unserialize($data);
    }       
}