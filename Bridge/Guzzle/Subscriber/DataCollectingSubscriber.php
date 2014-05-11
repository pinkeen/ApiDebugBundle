<?php

namespace Pinkeen\ApiDebugBundle\Bridge\Guzzle\Subscriber;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Exception\TransferException;

use Pinkeen\ApiDebugBundle\ApiEvents;
use Pinkeen\ApiDebugBundle\Event\ApiCallEvent;
use Pinkeen\ApiDebugBundle\Bridge\Guzzle\DataCollector\GuzzleCallData;

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
     * Handles guzzle complete event.
     *
     * @param CompleteEvent $event
     */
    public function onComplete(CompleteEvent $event)
    {
        if(null !== $event->getResponse() && in_array($event->getResponse()->getStatusCode()[0], [4, 5])) {
            /* Status code indicates an error so skip this event because
             * an error event should be emitted subsequently */
            return;
        }

        $this->collect($event->getRequest(), $event->getResponse(), null, $event->getTransferInfo());
    }

    /**
     * Handles guzzle error event.
     *
     * @param ErrorEvent $event
     */
    public function onError(ErrorEvent $event)
    {
        $this->collect($event->getRequest(), $event->getResponse(), $event->getException(), $event->getTransferInfo());
    }

    /**
     * Collects guzzle events.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param TransferException $exception
     * @param array $transferInfo
     */
    protected function collect(RequestInterface $request, ResponseInterface $response = null, TransferException $exception = null, array $transferInfo = null)
    {
        $this->eventDispatcher->dispatch(
            ApiEvents::CALL, 
            new ApiCallEvent(
                new GuzzleCallData($request, $response, $exception, $transferInfo)
            )
        );
    }
}