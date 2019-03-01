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
            'error_count' => 0,
            'success_count' => 0,
            'warning_count' => 0,
            'total_time' => 0,
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

        if($callData->hasError()) {
            ++$this->data['error_count'];
        } elseif($callData->hasWarning()) {
            ++$this->data['warning_count'];
        } else {
            ++$this->data['success_count'];
        }

        if(false !== $callData->getTotalTime()) {
            $this->data['total_time'] += $callData->getTotalTime();
        }
    }

    public function reset()
    {
        $this->data = [];
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return 0 !== $this->data['error_count'];
    }

    /**
     * @return bool
     */
    public function hasWarnings()
    {
        return 0 !== $this->data['warning_count'];
    }    

    /**
     * @return int
     */
    public function getErrorCount()
    {
        return $this->data['error_count'];
    }

    /**
     * @return int
     */
    public function getWarningCount()
    {
        return $this->data['warning_count'];
    }    

    /**
     * @return int
     */
    public function getSuccessCount()
    {
        return $this->data['success_count'];
    }    

    /**
     * @return int
     */
    public function getTotalTime()
    {
        return $this->data['total_time'];
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