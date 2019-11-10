# Page Views

Page views tracking works like Google Analytics. 

![Image](https://docs.microsoft.com/en-us/azure/azure-monitor/app/media/usage-flows/00001-flows.png)

In order to start using it you can use preregistered twig function:

```html
<!DOCTYPE html>
<html lang="{{ app.request.locale }}">
    <head>
        ...
        {{ app_insights_php() }}
    </head>
    <body>
        ...
    </body>
</html>
```

If you want to identify your metrics by logged users you can also pass username/userid
optional id to this function

```html
{% if app.user %}
    {{ app_insights_php(app.user.username) }}
{% else %}
    {{ app_insights_php() }}
{% endif %}
```