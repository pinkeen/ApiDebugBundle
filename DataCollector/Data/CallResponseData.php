<?php

namespace Pinkeen\ApiDebugBundle\DataCollector\Data;

/**
 * Class for storing api call response data.
 */
class CallResponseData extends AbstractCallMessageData
{
    /**
     * @var string
     */
    private $statusCode;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $headers = null, $body, $statusCode, $length = null)
    {
        parent::__construct($headers, $body, $length);

        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCodeLevel()
    {
        return intval($this->statusCode[0]);
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->statusCode,
            parent::serialize()
        ]);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        list(
            $this->statusCode,
            $parentData
        ) = unserialize($data);

        parent::unserialize($parentData);
    }      
}