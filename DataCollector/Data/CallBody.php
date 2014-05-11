<?php

namespace Pinkeen\ApiDebugBundle\DataCollector\Data;

use Pinkeen\ApiDebugBundle\MimeType\PrettyPrinter;
use Pinkeen\ApiDebugBundle\MimeType\MimeTypeGuesser;

/**
 * Class for storing call data.
 */
class CallBody implements \Serializable
{
    /**
     * @var string
     */
    private $filename;

    /** 
     * @param string|resource $data
     */
    public function __construct($data) 
    {
        if(null === $data) {
            throw new \InvalidArgumentException('Data must be either a resource or a type coercable to string!');
        }

        $this->filename = tempnam(sys_get_temp_dir(), 'api-call-body-');

        if(is_resource($data)) {
            $file = fopen($this->filename, 'w');
            fseek($data, 0);
            stream_copy_to_stream($data, $file);
            fclose($file);
        } else {
            file_put_contents($this->filename, strval($data));
        }
    }

    /**
     * Returns the mime-type from headers or tries 
     * to guess it. Returns false if all else fails.
     *
     * @return string|false
     */
    public function guessMimeType()
    {
        return MimeTypeGuesser::getInstance()->guess($this->filename);
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return filesize($this->filename);
    }

    /**
     * @return string|null
     */
    public function getData()
    {
        return file_get_contents($this->filename);
    }

    /**
     * @return string
     */
    public function getFileId()
    {
        return basename($this->filename);
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->filename,
        ]);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        list(
            $this->filename
        ) = unserialize($data);
    }     

    public function __toString()
    {
        return $this->getData();
    }    
}