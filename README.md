AppInsightsPHPBundle
=============

The AppInsightsPHPBundle brings support for Microsoft Application Insights into Symfony 3.4+ applications.
It's a profiler you can use on production to track everything that is important for you and your system.

[![Build Status](https://travis-ci.org/app-insights-php/app-insights-php-bundle.svg?branch=master)](https://travis-ci.org/app-insights-php/app-insights-php-bundle)

Microsoft App Insights allows you to track on production following metrics

* Traces (Logs with different verbosity level)
* Requests (http requests served by your app)
* Custom Events (web/cli/javascript) 
* Dependencies (SQL, Elasticsearch, Redis - any 3rd party service/system/api)
* Exceptions (web/cli/javascript)
* PageViews (javascript)

Query logs, visualize metrics and pin them to Azure Portal Dashboard, create alerts from metrics & health checks

All you need to do is register free [Microsoft Azure Account](https://azure.microsoft.com/en-us/free/free-account-faq/), 
setup new [App Insights Instance](https://docs.microsoft.com/en-us/azure/azure-monitor/app/create-new-resource) and install
this bundle in you symfony app. 

This bundle integrates app insights with all important libraries used by most of Symfony based applications.

* Monolog Handler (Trace)
* Doctrine Logger (Dependency) 
* Symfony HTTP (Request)
* Symfony Exception (Exceptions)

Microsoft App Insights is perfect for small teams that can't afford expensive monitoring tools or don't
have enough resources to install, configure and maintain powerful open source alternatives. 

If you are looking for a SAAS alternative to:

* Graylog / Kibana
* Zabbix 
* Grafana 
* New Relic / Datadog / etc 
* Google Analytics 

With 90 days data retention period for ~2.5EUR per 5GB [Pricing](https://azure.microsoft.com/en-us/pricing/details/monitor/)
Microsoft App Insights is exactly what you need. 

This bundle simplifies App Insights integration with your new or existing project. 

[![Image](https://docs.microsoft.com/en-us/azure/azure-monitor/app/media/web-monitor-performance/performancetriageview7dayszoomedtrendzoomed95th99th.png)]

Documentation
-------------

The source of the documentation is stored in the `Resources/doc/` folder
in this bundle, and available on symfony.com:

[Read the Documentation for master](Resources/doc/index.md)

Installation
------------

All the installation instructions are located in the documentation.

License
-------

This bundle is under the MIT license. See the complete license [in the bundle](LICENSE)
