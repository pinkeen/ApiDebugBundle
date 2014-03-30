<?php

namespace Pinkeen\ApiDebugBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pinkeen\ApiDebugBundle\Event\ApiCallEvent;

/**
 * Collects data from an API cosumer's requests.
 */
class ApiConsumerDataCollector extends DataCollector
{
    public function __construct()
    {
        $this->data['calls'] = [];
    }

    /**
     * Does nothing because data is collected by subscribing to events.
     *
     * {@inheritDoc}
     */    
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    /**
     * Collects an API call.
     */
    public function collectCall(ApiCallEvent $event)
    {
        $this->data['calls'][] = $event->getData();
    }

    public function getCalls()
    {
        return $this->data['calls'];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'api_consumer';
    }
}