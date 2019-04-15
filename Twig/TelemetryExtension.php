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

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Twig;

use AppInsightsPHP\Client\Client;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TelemetryExtension extends AbstractExtension
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_insights_php', [$this, 'appInsightsPHP'], ['is_safe' => ['html']]),
        ];
    }

    public function appInsightsPHP(?string $userId = null): string
    {
        $script = "<script type=\"text/javascript\">\n";
        if ($this->client->configuration()->isEnabled()) {
            $script .= sprintf(<<<JS
var appInsights=window.appInsights||function(a){
  function b(a){c[a]=function(){var b=arguments;c.queue.push(function(){c[a].apply(c,b)})}}var c={config:a},d=document,e=window;setTimeout(function(){var b=d.createElement("script");b.src=a.url||"https://az416426.vo.msecnd.net/scripts/a/ai.0.js",d.getElementsByTagName("script")[0].parentNode.appendChild(b)});try{c.cookie=d.cookie}catch(a){}c.queue=[];for(var f=["Event","Exception","Metric","PageView","Trace","Dependency"];f.length;)b("track"+f.pop());if(b("setAuthenticatedUserContext"),b("clearAuthenticatedUserContext"),b("startTrackEvent"),b("stopTrackEvent"),b("startTrackPage"),b("stopTrackPage"),b("flush"),!a.disableExceptionTracking){f="onerror",b("_"+f);var g=e[f];e[f]=function(a,b,d,e,h){var i=g&&g(a,b,d,e,h);return!0!==i&&c["_"+f](a,b,d,e,h),i}}return c
  }({
      instrumentationKey:"%s",
      disableCorrelationHeaders: false
  });

window.appInsights.queue.push(function () {
    appInsights.context.operation.id = '%s';
});\n
JS
                , $this->client->getContext()->getInstrumentationKey(),
                $this->client->getContext()->getOperationContext()->getId()
            );

            if ($userId) {
                $script .= sprintf("window.appInsights.setAuthenticatedUserContext(\"%s\");\n", $userId);
            }

            $script .= "window.appInsights.trackPageView();\n";

        } else {
            $script .= "//app_insights_php integration is disabled, please check bundle configuration.\n";
        }

        $script .= '</script>';
        return $script;
    }
}
