<?php

declare(strict_types=1);

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Tests\Twig;

use AppInsightsPHP\Client\ClientFactory;
use AppInsightsPHP\Client\Configuration;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Twig\TelemetryExtension;
use PHPUnit\Framework\TestCase;

final class TelemetryExtensionTest extends TestCase
{
    public function test_app_insights_php_function_without_user_id()
    {
        $client = (new ClientFactory('instrumentation_key', Configuration::createDefault()))->create();
        $client->getContext()->getOperationContext()->setId('operation_id');
        $twigExtension = new TelemetryExtension($client);

        $this->assertSame(
 <<<TWIG
<script type="text/javascript">
var appInsights=window.appInsights||function(a){
  function b(a){c[a]=function(){var b=arguments;c.queue.push(function(){c[a].apply(c,b)})}}var c={config:a},d=document,e=window;setTimeout(function(){var b=d.createElement("script");b.src=a.url||"https://az416426.vo.msecnd.net/scripts/a/ai.0.js",d.getElementsByTagName("script")[0].parentNode.appendChild(b)});try{c.cookie=d.cookie}catch(a){}c.queue=[];for(var f=["Event","Exception","Metric","PageView","Trace","Dependency"];f.length;)b("track"+f.pop());if(b("setAuthenticatedUserContext"),b("clearAuthenticatedUserContext"),b("startTrackEvent"),b("stopTrackEvent"),b("startTrackPage"),b("stopTrackPage"),b("flush"),!a.disableExceptionTracking){f="onerror",b("_"+f);var g=e[f];e[f]=function(a,b,d,e,h){var i=g&&g(a,b,d,e,h);return!0!==i&&c["_"+f](a,b,d,e,h),i}}return c
  }({
      instrumentationKey:"instrumentation_key"
  });

window.appInsights=appInsights;
window.appInsights.context.operation.id="operation_id";
window.appInsights.queue&&0===appInsights.queue.length&&appInsights.trackPageView();
</script>
TWIG
            , $twigExtension->appInsightsPHP()
        );
    }

    public function test_app_insights_php_function_with_user_id()
    {
        $client = (new ClientFactory('instrumentation_key', Configuration::createDefault()))->create();
        $client->getContext()->getOperationContext()->setId('operation_id');
        $twigExtension = new TelemetryExtension($client);

        $this->assertSame(
            <<<TWIG
<script type="text/javascript">
var appInsights=window.appInsights||function(a){
  function b(a){c[a]=function(){var b=arguments;c.queue.push(function(){c[a].apply(c,b)})}}var c={config:a},d=document,e=window;setTimeout(function(){var b=d.createElement("script");b.src=a.url||"https://az416426.vo.msecnd.net/scripts/a/ai.0.js",d.getElementsByTagName("script")[0].parentNode.appendChild(b)});try{c.cookie=d.cookie}catch(a){}c.queue=[];for(var f=["Event","Exception","Metric","PageView","Trace","Dependency"];f.length;)b("track"+f.pop());if(b("setAuthenticatedUserContext"),b("clearAuthenticatedUserContext"),b("startTrackEvent"),b("stopTrackEvent"),b("startTrackPage"),b("stopTrackPage"),b("flush"),!a.disableExceptionTracking){f="onerror",b("_"+f);var g=e[f];e[f]=function(a,b,d,e,h){var i=g&&g(a,b,d,e,h);return!0!==i&&c["_"+f](a,b,d,e,h),i}}return c
  }({
      instrumentationKey:"instrumentation_key"
  });

window.appInsights=appInsights;
window.appInsights.context.operation.id="operation_id";
window.appInsights.setAuthenticatedUserContext("norbert@orzechowicz.pl");
window.appInsights.queue&&0===appInsights.queue.length&&appInsights.trackPageView();
</script>
TWIG
            , $twigExtension->appInsightsPHP('norbert@orzechowicz.pl')
        );
    }
}
