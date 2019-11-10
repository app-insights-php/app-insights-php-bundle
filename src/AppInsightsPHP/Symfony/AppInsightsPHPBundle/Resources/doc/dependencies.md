# Dependencies

Tracking dependencies might be really useful, App Insights will build Application Map from your
dependencies and will track performance degradation for your automatically. 

![Image](https://docs.microsoft.com/en-us/azure/azure-monitor/app/media/app-map/application-map-001.png)

## Disable dependencies tracking 
Disabling app insights in long running CLI commands could help you to save extra money and reduce some noise 
in your logs. For example if you are reindexing your whole database in elasticsearch tracking each
call to dependency (elasticsearch) is probably useless, it wont give you any valuable data and might slow
down the process and generate extra costs. 

In this case you can disable whole App Insights client or just specific metric type

```php
<?php
$appInsights = new ClientFactory('instrumentation_key', Configuration::default());

$configuration->configuration()->disable();
```

Above code will prevent App Insights PHP Client from tracking any metrics but if for example
you want to disable dependency tracking (indexing documents in elasticsearch) but still need
to track exceptions or critical logs you might want to use following code:

```php
<?php
$appInsights = new ClientFactory('instrumentation_key', Configuration::default());

$configuration->configuration()->dependencies()->disable();
```

Still not satisfied? If for any reason you would like to disable only specific dependency you
can also tell Telemetryt Client to ignore them by name. 

Below example shows how you can ignore Doctrine DBAL SQL dependencies only:

```php
<?php
$appInsights = new ClientFactory('instrumentation_key', Configuration::default());

$configuration->configuration()
    ->dependencies()
    ->ignore(\AppInsightsPHP\Doctrine\DBAL\Logging\DependencyLogger::DEFAULT_NAME);
```

