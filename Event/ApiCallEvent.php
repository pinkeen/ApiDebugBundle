<?php

namespace Pinkeen\ApiDebugBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Pinkeen\ApiDebugBundle\DataCollector\ApiCallDataInterface;

class ApiCallEvent extends Event
{
    /**
     * @var ApiCallDataInterface
     */
    protected $data;

    /**
     * @param ApiCallDataInterface $data;
     */
    public function __construct(ApiCallDataInterface $data)
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