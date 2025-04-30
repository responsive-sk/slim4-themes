# Usage

This document describes how to use the Slim4 Themes package in your application.

## Basic Usage

### Configuration

First, you need to register the theme services in your container:

```php
// Register theme services
$container->set(Slim4\Themes\Interface\ThemeInterface::class, function (ContainerInterface $container) {
    return new Slim4\Themes\Twig\TwigTheme(
        'default',
        __DIR__ . '/templates/themes/default',
        true
    );
});

$container->set(Slim4\Themes\Interface\ThemeLoaderInterface::class, function (ContainerInterface $container) {
    return new Slim4\Themes\Twig\TwigThemeLoader(
        $container->get(Slim4\Root\PathsInterface::class),
        [
            'default' => 'default',
            'available' => ['default', 'dark', 'blue', 'green', 'light']
        ]
    );
});

$container->set(Slim4\Themes\Interface\ThemeRendererInterface::class, function (ContainerInterface $container) {
    return new Slim4\Themes\Twig\TwigThemeRenderer(
        $container->get(Slim4\Themes\Interface\ThemeInterface::class),
        $container->get(Slim\Interfaces\RouteParserInterface::class)
    );
});
```

Then, add the theme middleware to your application:

```php
// Add theme middleware
$app->add(new Slim4\Themes\Middleware\ThemeMiddleware(
    $container->get(Slim4\Themes\Interface\ThemeLoaderInterface::class),
    $container->get(Slim4\Themes\Interface\ThemeRendererInterface::class),
    'theme',
    'theme'
));
```

### Using in Controllers

In your controllers, you can use the `ThemeRendererInterface` to render templates:

```php
use Slim4\Themes\Interface\ThemeRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController
{
    private ThemeRendererInterface $themeRenderer;

    public function __construct(ThemeRendererInterface $themeRenderer)
    {
        $this->themeRenderer = $themeRenderer;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = [
            'title' => 'Home',
            'content' => 'Welcome to the home page!',
        ];

        $html = $this->themeRenderer->render('home/index.twig', $data);
        $response->getBody()->write($html);
        return $response;
    }
}
```

### Using in Templates

In your templates, you can access the theme object and use the Slim functions:

#### Twig

```twig
{% extends "layout/default.twig" %}

{% block content %}
    <h1>{{ title }}</h1>
    <p>{{ content }}</p>

    <p>Current theme: {{ theme.name }}</p>

    <a href="{{ url_for('home') }}">Home</a>
    <a href="{{ url_for('about') }}">About</a>
{% endblock %}
```

#### Latte

```latte
{extends "layout/default.latte"}

{block content}
    <h1>{$title}</h1>
    <p>{$content}</p>

    <p>Current theme: {$theme->getName()}</p>

    <a href="{url_for('home')}">Home</a>
    <a href="{url_for('about')}">About</a>
{/block}
```

## Advanced Usage

### Theme Switching

You can switch themes by adding a query parameter to the URL:

```
https://example.com/?theme=dark
```

This will set a cookie with the theme name, so the theme will be remembered for future requests.

You can also switch themes programmatically:

```php
use Slim4\Themes\Interface\ThemeLoaderInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;

class ThemeSwitcherController
{
    private ThemeLoaderInterface $themeLoader;
    private ThemeRendererInterface $themeRenderer;

    public function __construct(ThemeLoaderInterface $themeLoader, ThemeRendererInterface $themeRenderer)
    {
        $this->themeLoader = $themeLoader;
        $this->themeRenderer = $themeRenderer;
    }

    public function switchTheme(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $themeName = $args['theme'] ?? 'default';

        try {
            $theme = $this->themeLoader->load($themeName);
            $this->themeRenderer->setTheme($theme);

            // Set cookie
            $response = $response->withHeader(
                'Set-Cookie',
                sprintf(
                    'theme=%s; Path=/; HttpOnly; SameSite=Lax',
                    $themeName
                )
            );

            // Redirect to home page
            return $response->withHeader('Location', '/')->withStatus(302);
        } catch (ThemeNotFoundException $e) {
            // Theme not found, redirect to home page
            return $response->withHeader('Location', '/')->withStatus(302);
        }
    }
}
```

### Theme Resolver

You can use the `ThemeResolver` to resolve templates and get the current theme:

```php
use Slim4\Themes\Resolver\ThemeResolver;

class TemplateController
{
    private ThemeResolver $themeResolver;

    public function __construct(ThemeResolver $themeResolver)
    {
        $this->themeResolver = $themeResolver;
    }

    public function getTemplatePath(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $template = $args['template'] ?? 'home.twig';

        try {
            $templatePath = $this->themeResolver->resolveTemplate($template);
            $response->getBody()->write($templatePath);
            return $response;
        } catch (TemplateNotFoundException $e) {
            $response->getBody()->write('Template not found');
            return $response->withStatus(404);
        }
    }

    public function getCurrentTheme(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $theme = $this->themeResolver->getCurrentTheme();
        $response->getBody()->write($theme->getName());
        return $response;
    }

    public function getAvailableThemes(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $themes = $this->themeResolver->getAvailableThemes();
        $themeNames = array_map(function ($theme) {
            return $theme->getName();
        }, $themes);

        $response->getBody()->write(json_encode($themeNames));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
```

### Adding Global Variables

You can add global variables to the theme renderer:

```php
use Slim4\Themes\Interface\ThemeRendererInterface;

class GlobalVariablesController
{
    private ThemeRendererInterface $themeRenderer;

    public function __construct(ThemeRendererInterface $themeRenderer)
    {
        $this->themeRenderer = $themeRenderer;
    }

    public function addGlobalVariables(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->themeRenderer->addGlobal('app_name', 'My App');
        $this->themeRenderer->addGlobal('app_version', '1.0.0');
        $this->themeRenderer->addGlobal('app_author', 'Your Name');

        $response->getBody()->write('Global variables added');
        return $response;
    }
}
```

### Theme Assets

You can access theme assets in your templates:

#### Twig

```twig
<link rel="stylesheet" href="{{ theme.assetsPath }}/css/style.css">
<script src="{{ theme.assetsPath }}/js/script.js"></script>
<img src="{{ theme.assetsPath }}/images/logo.png" alt="Logo">
```

#### Latte

```latte
<link rel="stylesheet" href="{$theme->getAssetsPath()}/css/style.css">
<script src="{$theme->getAssetsPath()}/js/script.js"></script>
<img src="{$theme->getAssetsPath()}/images/logo.png" alt="Logo">
```

### Theme Configuration

You can access theme configuration in your templates:

#### Twig

```twig
<h1>{{ theme.config.name }}</h1>
<p>{{ theme.config.description }}</p>
<p>Version: {{ theme.config.version }}</p>
<p>Author: {{ theme.config.author }}</p>
```

#### Latte

```latte
<h1>{$theme->getConfig()['name']}</h1>
<p>{$theme->getConfig()['description']}</p>
<p>Version: {$theme->getConfig()['version']}</p>
<p>Author: {$theme->getConfig()['author']}</p>
```

### Theme Inheritance

You can create a theme that inherits from another theme:

```json
{
  "name": "Dark Theme",
  "description": "A dark theme for the application",
  "version": "1.0.0",
  "author": "Your Name",
  "parent": "default"
}
```

In this example, the `dark` theme inherits from the `default` theme. If a template is not found in the `dark` theme, the `default` theme is checked.
