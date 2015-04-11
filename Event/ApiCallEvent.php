<?php

namespace Pinkeen\ApiDebugBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Pinkeen\ApiDebugBundle\DataCollector\Data\AbstractCallData;

class ApiCallEvent extends Event
{
    /**
     * @var AbstractCallData
     */
    protected $data;

    /**
     * @param AbstractCallData $data;
     */
    public function __construct(AbstractCallData $data)
    {
        $this->data = $data;
    }

    /**
     * @return AbstractCallData
     */
    public function getData()
    {
        return $this->data;
    }
}
