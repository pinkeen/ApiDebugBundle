<?php

namespace Pinkeen\ApiDebugBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Pinkeen\ApiDebugBundle\DataCollector\AbstractApiCallData;

class ApiCallEvent extends Event
{
    /**
     * @var AbstractApiCallData
     */
    protected $data;

    /**
     * @param AbstractApiCallData $data;
     */
    public function __construct(AbstractApiCallData $data)
    {
        $this->data = $data;
    }

    /**
     * @return ApiCallDataInterface
     */
    public function getData()
    {
        return $this->data;
    }
}