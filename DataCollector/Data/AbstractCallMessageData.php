<?php

namespace Pinkeen\ApiDebugBundle\DataCollector\Data;

use Pinkeen\ApiDebugBundle\MimeType\MimeTypeGuesser;
use Pinkeen\ApiDebugBundle\MimeType\PrettyPrinter;

/**
 * Class for storing data common between 
 * api call request and response.
 */
abstract class AbstractCallMessageData implements \Serializable
{
    /**
     * @var array
     */
    private $headers = null;

    /**
     * @var CallBody
     */
    private $body = null;

    /**
     * @var int
     */
    private $length = null;
    
    /**
     * @var string
     */
    private $mimeType = null;

    /**
     * You can pass the length in case there is no Content-Length header
     * and body had to be discarded for various concerns.
     *
     * @param array $headers
     * @param string|resource $body
     * @param int $length
     */
    public function __construct(array $headers = null, $body, $length = null)
    {
        $this->headers = $headers;
        $this->length = $length;

        if(null !== $body) {
            $this->body = new CallBody($body);
        }
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
     * @return CallBody|null
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
     * @return string
     */
    public function getContentTypeHeader()
    {
       if(null !== $mime = $this->getHeader('Content-Type')) {
            return mb_strtolower(trim(explode(';', $mime)[0]));
       }

       return null;
    }

    /**
     * Returns the mime-type from headers or tries 
     * to guess it. Returns false if all else fails.
     *
     * @return string|false
     */
    public function getMimeType()
    {
        if(null !== $this->mimeType) {
            return $this->mimeType;
        }

        if(null !== $this->getHeader('Content-Type')) {
            return $this->mimeType = $this->getContentTypeHeader();
        }

        if(!$this->hasBody()) {
            return $this->mimeType = false;
        }

        return $this->mimeType = $this->getBody()->guessMimeType();
    }

    /**
     * Returns body prettified for HTML
     * or false if not available or cannot
     * be printed.
     *
     * @return string|false
     */
    public function getPrettyBody()
    {
        if(!$this->hasBody()) {
            return false;
        }

        if(false === $this->getMimeType()) {
            return false;
        }

        return PrettyPrinter::getInstance()->prettify(
            $this->getBody()->getData(), 
            $this->getMimeType()
        );
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
                return $this->length = $this->getBody()->getSize();
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
