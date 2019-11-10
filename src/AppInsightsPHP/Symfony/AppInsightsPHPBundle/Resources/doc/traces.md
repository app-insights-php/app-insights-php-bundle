# Traces

Traces in App Insights are nothing else than good known to everyone in PHP community Logs (Monolog).

You can use one of following trace level:

```
abstract class Severity_Level
{
    const Verbose = 0;
    const Information = 1;
    const Warning = 2;
    const Error = 3;
    const Critical = 4;
}
```

[Our monolog hanlder](https://github.com/app-insights-php/monolog-handler) brings for you out of the
box integration with app insights. This means that if your system is using monolog stream logger you
can easily replace it with App Insights PHP trace logger to start sending your logs into the cloud. 

## Optimize Memory Usage

This is especially problematic when you log things in lon running CLI command. In order to 
avoid memory leaks you should be using [Buffer Handler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/BufferHandler.php)
in from of App Insights PHP trace handler. 

## Configuration

```yaml
app_insights_php:
  monolog:
    handlers:
      trace: # app_insights_php.monolog.handler.trace
        type: trace # trace | dependency
        level: ERROR
        bubble: true
      dependency: # app_insights_php.monolog.handler.dependency
        type: dependency # trace | dependency
```

Above configuration will register in service container trace handler with `app_insights_php.monolog.handler.trace` id. 
You can use it later directly in your monolog bundle configuration: 

```yaml
monolog:
    handlers:
        logger_buffer:
            type: buffer
            buffer_size: 100
            handler: app_insights
            level: error
        app_insights:
            type: service
            id: "app_insights_php.monolog.handler.trace"
```

### Types

`trace`

Regular handler with few log levels, should be used in most cases.

`dependency`

Special handler useful when you are logging requests/responses from external
services/systems/apis. 
Instead of using `trackTrace` this handler will use `trackDependency` method 
on App Insights PHP Client. 

**Known limitations**

There is no good and easy way to track dependency start and duration time :|
Those fields are going to be null.
