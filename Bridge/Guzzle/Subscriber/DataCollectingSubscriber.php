<?php

namespace Pinkeen\ApiDebugBundle\Bridge\Guzzle\Subscriber;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

use Pinkeen\ApiDebugBundle\ApiEvents;
use Pinkeen\ApiDebugBundle\Event\ApiCallEvent;
use Pinkeen\ApiDebugBundle\Bridge\Guzzle\DataCollector\GuzzleApiCallData;

class DataCollectingSubscriber implements SubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher) 
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function getEvents()
    {
        return [
            'complete' => ['onComplete', RequestEvents::EARLY],
            'error'    => ['onError', RequestEvents::EARLY],
        ];
    }	

    /**
     * Handles guzzle onComplete event.
     *
     * @param CompleteEvent $event
     */
    public function onComplete(CompleteEvent $event)
    {
        $this->collect($event->getRequest(), $event->getResponse());
    }

    /**
     * Handles guzzle onError event.
     *
     * @param ErrorEvent $event
     */
    public function onError(ErrorEvent $event)
    {
        $this->collect($event->getRequest(), $event->getResponse());
    }

    /**
     * Collects guzzle events.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    protected function collect(RequestInterface $request, ResponseInterface $response = null)
    {
        $this->eventDispatcher->dispatch(
            ApiEvents::API_CALL, 
            new ApiCallEvent(
                new GuzzleApiCallData($request, $response)
            )
        );
    }
}