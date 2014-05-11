ApiDebugBundle
==============

![ApiDebugBundle in action](Resources/doc/meta/images/apidebug.png)

This bundle add a web debug toolbar tab which displays information about API consumer requests.

It aims to be universal and allow for easy integration with SDKs and HTTP client libraries.

Currently it supports [Guzzle 4](https://github.com/guzzle/guzzle) out-of-the box.

## Requirements

* PHP 5.4
* Symfony 2.4

## Installation

The usual [Symfony stuff](http://symfony.com/doc/current/cookbook/bundles/installation.html).

The **composer.json** needs: `"pinkeen/api-debug-bundle": "dev-master",`.

The **AppKernel.php** needs: `new Pinkeen\ApiDebugBundle\PinkeenApiDebugBundle(),`.

Add the following to your `app/config/routing_dev.yml` if you want to be able to view raw body data:

```yml
_api_debug:
    resource: "@PinkeenApiDebugBundle/Resources/config/routing.yml"
    prefix:   /_profiler
```

## Usage

### Integrate with your custom client

Firstly you have to subclass 
[`AbstractCallData`](DataCollector/AbstractCallData.php) 
which holds data from a single API request.

Then every time your API consumer makes a request dispatch an [`ApiEvents::API_CALL`](ApiEvents.php) event.

```php
    use Pinkeen\ApiDebugBundle\ApiEvents;
    use Pinkeen\ApiDebugBundle\Event\ApiCallEvent;
    
    /* ... */
    
    $serviceContainer->get('event_dispatcher')->dispatch(
        ApiEvents::API_CALL, 
        new ApiCallEvent(
            new YourApiCallData(/* your params */)
        )
    );
```

### Guzzle

You've got two options here, either:

*Let the bundle create the client for you...*

```php
    $serviceContainer->get('guzzle.client_factory')->create([
        /* Guzzle client config (optional).
         * It is passed directly to GuzzleHttp\Client constructor. */
    ]);
```

*... or attach data collecting subscriber to the client you already have.*

```php
    $guzzleClient->getEmitter()->attach(
        $serviceContainer->get('guzzle.collecting_subscriber')
    );
```

## Production

For production environment you probably want to skip all of the data gathering.

You should take care of that yourself, unless you're using `guzzle.client_factory`.