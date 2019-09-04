# Installation

Supported symfony versions: 

* `>= 3.4`
* `>= 4.0` 

## Applications that don't use Symfony Flex

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require app-insights-php/app-insights-php-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

## Applications that use Symfony Flex

(**Not available yet**)

Open a command console, enter your project directory and execute:

```console
$ composer require app-insights-php
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new AppInsightsPHP\Symfony\AppInsightsPHPBundle\AppInsightsPHPBundle(),
        ];

        // ...
    }

    // ...
}
```
### Step 3: Setup Instrumentation Key

If you are using environment variables you should create new entry: 

```dotenv   
MICROSOFT_APP_INSIGHTS_INTRUMENTATION_KEY='change_me'
```

In order to obtain instrumentation key please follow [Microsoft official documentation](https://docs.microsoft.com/en-us/azure/azure-monitor/app/create-new-resource)


### Step 4: Configuration Reference

```yaml
app_insights_php:
  enabled: true
  gzip_enabled: false
  instrumentation_key: "%env(MICROSOFT_APP_INSIGHTS_INTRUMENTATION_KEY)%"
  fallback_logger:         # optional
    service_id: "logger"   # optional 
    monolog_logger: "main" # optional 
  failure_cache_service_id: "your_cache_service_id" # optional
  exceptions:
    enabled: true
    ignored_exceptions:
      - 'Symfony\Component\HttpKernel\Exception\NotFoundHttpException'
      - 'Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException' 
  dependencies:
    enabled: true
  requests:
    enabled: true
  traces:
    enabled: true
  doctrine:
    track_dependency: true
  monolog:  
    handlers:
      trace: # register: app_insights_php.monolog.handler.trace - service  
        type: trace
        level: DEBUG
        bubble: true
      foo: # register: app_insights_php.monolog.handler.foo - service  
        type: trace
        level: ERROR
        bubble: true
        
monolog:
  handlers:
    app_insights:
      type: service
      id: "app_insights_php.monolog.handler.trace"
```

#### gzip_enabled

By default all requests to App Insights are sent uncompressed. If you have `zlib` extension installed then you can use
gzip compression to save some bandwidth or to send more data in one request. 

#### fallback_logger

When most of the configuration is pretty much self descriptive `fallback_logger` might need some extra explanation. 
Fallback logger is used to log failures in app insights logger. It's used by `KernelTerminateListener`.

You can configure only `service_id` or if using MonologBundle also `monolog_channel`. 

### failure_cache_service_id

It happens that from time to time app insights API returns 500 server error. In that case if you use fallback_logger 
error will be logged but you will also loose anything that supposed to be logged during that failed attempt. 

Failure cache (implementation of \PSR\SimpleCache\CacheInterface) will take whole queue during exception, it will serialize
it and save for next 24 hours in the cache. During next `onTerminate` with not empty Telemetry client queue content
of the cache will be deserialized and attached to the upcoming flush.  

On default [NullObject implementation](../../Cache/NullCache.php) is used.

### Step 5: How it works

Please check our [How it works](how_it_works.md) section.
