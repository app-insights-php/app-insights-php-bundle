# How it works

All components from [App Insights PHP](https://github.com/app-insights-php) are build on top of 
official [Microsoft App Insights SDK](https://github.com/Microsoft/ApplicationInsights-PHP) for PHP. Official SDK talks to App Insights API through HTTP protocol. 

What about performance? This bundle should not affect your system performance at all however
there are some best practices we recommend you to follow (more later). 

Basically whole idea behind official SDK is to collect all metrics/logs in memory and flush them 
when needed. 

```php
<?php

$appInsights = new ClientFactory('instrumentation_key', Configuration::default());

$appInsights->trackEvent('My Custom Event!');

$redisStartTime = time();

$appInsights->doSomething();

$appInsights->trackDependency('Redis', 'Cache', 'do something()', $redisStartTime);
```

In above example AppInsights client will track custom event and redis call (dependency) however
it will not fire any HTTP call, it needs to be done explicitly by calling `flush()` method.

```php
<?php

$appInsights->flush();

``` 

`AppInsightsPHPBundle` makes use of Symfony [kernel.terminate](https://symfony.com/doc/current/reference/events.html#kernel-terminate) event
to flush SDK telemetry queue. 


Read more about tracking: 

* [Dependencies](dependencies.md)
* [Traces](traces.md)
* [PageViews](page_views.md)