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
        $this->data = [
            'calls' => [],
            'apis' => [],
        ];
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
        $callData = $event->getData();

        $this->data['calls'][] = $callData;

        if(!in_array($callData->getApiName(), $this->data['apis'])) {
            $this->data['apis'][] = $callData->getApiName();
        }
    }

    /**
     * Returns array of call data items.
     *
     * @return array
     */
    public function getCalls()
    {
        return $this->data['calls'];
    }

    /**
     * Returns true if any calls were collected.
     *
     * @return bool
     */
    public function hasCalls()
    {
        return !empty($this->data['calls']);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'api_consumer';
    }
}