<?php

declare(strict_types=1);

/*
 * This file is part of the App Insights PHP project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Tests\Twig;

use AppInsightsPHP\Client\ClientFactory;
use AppInsightsPHP\Client\Configuration;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Twig\TelemetryExtension;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;

final class TelemetryExtensionTest extends TestCase
{
    public function test_app_insights_php_function_without_user_id() : void
    {
        $client = (new ClientFactory('instrumentation_key', Configuration::createDefault(), $this->createMock(CacheInterface::class), new NullLogger()))->create();
        $client->getContext()->getOperationContext()->setId('operation_id');
        $twigExtension = new TelemetryExtension($client);

        $this->assertSame(
            <<<'TWIG'
<script type="text/javascript">
var appInsights=window.appInsights||function(a){
  function b(a){c[a]=function(){var b=arguments;c.queue.push(function(){c[a].apply(c,b)})}}var c={config:a},d=document,e=window;setTimeout(function(){var b=d.createElement("script");b.src=a.url||"https://az416426.vo.msecnd.net/scripts/a/ai.0.js",d.getElementsByTagName("script")[0].parentNode.appendChild(b)});try{c.cookie=d.cookie}catch(a){}c.queue=[];for(var f=["Event","Exception","Metric","PageView","Trace","Dependency"];f.length;)b("track"+f.pop());if(b("setAuthenticatedUserContext"),b("clearAuthenticatedUserContext"),b("startTrackEvent"),b("stopTrackEvent"),b("startTrackPage"),b("stopTrackPage"),b("flush"),!a.disableExceptionTracking){f="onerror",b("_"+f);var g=e[f];e[f]=function(a,b,d,e,h){var i=g&&g(a,b,d,e,h);return!0!==i&&c["_"+f](a,b,d,e,h),i}}return c
  }({
      instrumentationKey:"instrumentation_key",
      disableCorrelationHeaders: false
  });

window.appInsights.queue.push(function () {
    appInsights.context.operation.id = 'operation_id';
});
window.appInsights.trackPageView();
</script>
TWIG
            ,
            $twigExtension->appInsightsPHP()
        );
    }

    public function test_app_insights_php_function_with_user_id() : void
    {
        $client = (new ClientFactory('instrumentation_key', Configuration::createDefault(), $this->createMock(CacheInterface::class), new NullLogger()))->create();
        $client->getContext()->getOperationContext()->setId('operation_id');
        $twigExtension = new TelemetryExtension($client);

        $this->assertSame(
            <<<'TWIG'
<script type="text/javascript">
var appInsights=window.appInsights||function(a){
  function b(a){c[a]=function(){var b=arguments;c.queue.push(function(){c[a].apply(c,b)})}}var c={config:a},d=document,e=window;setTimeout(function(){var b=d.createElement("script");b.src=a.url||"https://az416426.vo.msecnd.net/scripts/a/ai.0.js",d.getElementsByTagName("script")[0].parentNode.appendChild(b)});try{c.cookie=d.cookie}catch(a){}c.queue=[];for(var f=["Event","Exception","Metric","PageView","Trace","Dependency"];f.length;)b("track"+f.pop());if(b("setAuthenticatedUserContext"),b("clearAuthenticatedUserContext"),b("startTrackEvent"),b("stopTrackEvent"),b("startTrackPage"),b("stopTrackPage"),b("flush"),!a.disableExceptionTracking){f="onerror",b("_"+f);var g=e[f];e[f]=function(a,b,d,e,h){var i=g&&g(a,b,d,e,h);return!0!==i&&c["_"+f](a,b,d,e,h),i}}return c
  }({
      instrumentationKey:"instrumentation_key",
      disableCorrelationHeaders: false
  });

window.appInsights.queue.push(function () {
    appInsights.context.operation.id = 'operation_id';
});
window.appInsights.setAuthenticatedUserContext("norbert@orzechowicz.pl");
window.appInsights.trackPageView();
</script>
TWIG
            ,
            $twigExtension->appInsightsPHP('norbert@orzechowicz.pl')
        );
    }

    public function test_app_insights_php_function_with_disabled_tracking() : void
    {
        $config = Configuration::createDefault();
        $config->disable();

        $client = (new ClientFactory('instrumentation_key', $config, $this->createMock(CacheInterface::class), new NullLogger()))->create();
        $client->getContext()->getOperationContext()->setId('operation_id');
        $twigExtension = new TelemetryExtension($client);

        $this->assertSame(
            <<<'TWIG'
<script type="text/javascript">
//app_insights_php integration is disabled, please check bundle configuration.
</script>
TWIG
            ,
            $twigExtension->appInsightsPHP('norbert@orzechowicz.pl')
        );
    }

    public function test_app_insights_php_function_with_empty_instrumentation_key() : void
    {
        $config = Configuration::createDefault();

        $client = (new ClientFactory('', $config, $this->createMock(CacheInterface::class), new NullLogger()))->create();
        $client->getContext()->getOperationContext()->setId('operation_id');
        $twigExtension = new TelemetryExtension($client);

        $this->assertSame(
            <<<'TWIG'
<script type="text/javascript">
//app_insights_php instrumentation_key is empty, please check bundle configuration.
</script>
TWIG
            ,
            $twigExtension->appInsightsPHP('norbert@orzechowicz.pl')
        );
    }
}
