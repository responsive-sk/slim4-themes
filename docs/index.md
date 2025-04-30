# Slim4 Themes Documentation

Welcome to the Slim4 Themes documentation. This package provides a flexible theme system for Slim 4 applications.

## Table of Contents

1. [Installation](#installation)
2. [Configuration](#configuration)
3. [Usage](#usage)
4. [Extending](#extending)

## Installation

Install the package via Composer:

```bash
composer require responsive-sk/slim4-themes
```

## Configuration

### Basic Configuration

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
        $container->get(Slim4\Root\PathsInterface::class)
    );
});

$container->set(Slim4\Themes\Interface\ThemeRendererInterface::class, function (ContainerInterface $container) {
    return new Slim4\Themes\Twig\TwigThemeRenderer(
        $container->get(Slim4\Themes\Interface\ThemeInterface::class),
        $container->get(Slim\Interfaces\RouteParserInterface::class)
    );
});

// Add theme middleware
$app->add(new Slim4\Themes\Middleware\ThemeMiddleware(
    $container->get(Slim4\Themes\Interface\ThemeLoaderInterface::class),
    $container->get(Slim4\Themes\Interface\ThemeRendererInterface::class),
    'theme',
    'theme'
));
```

### Using with PHP-DI

```php
return [
    // Themes
    Slim4\Themes\Interface\ThemeInterface::class => function (ContainerInterface $container) {
        return new Slim4\Themes\Twig\TwigTheme(
            'default',
            $container->get('settings')['templates']['themes'] . '/default',
            true
        );
    },

    Slim4\Themes\Interface\ThemeLoaderInterface::class => function (ContainerInterface $container) {
        return new Slim4\Themes\Twig\TwigThemeLoader(
            $container->get(Slim4\Root\PathsInterface::class)
        );
    },

    Slim4\Themes\Interface\ThemeRendererInterface::class => function (ContainerInterface $container) {
        return new Slim4\Themes\Twig\TwigThemeRenderer(
            $container->get(Slim4\Themes\Interface\ThemeInterface::class),
            $container->get(Slim\Interfaces\RouteParserInterface::class)
        );
    },
];
```

### Using with Neon

```neon
services:
    # Themes
    Slim4\Themes\Interface\ThemeInterface:
        factory: Slim4\Themes\Twig\TwigTheme
        arguments:
            name: default
            path: %root_dir%/templates/themes/default
            isDefault: true
        tags: [theme]

    Slim4\Themes\Interface\ThemeLoaderInterface:
        factory: Slim4\Themes\Twig\TwigThemeLoader
        arguments:
            paths: @Slim4\Root\PathsInterface
        tags: [theme]

    Slim4\Themes\Interface\ThemeRendererInterface:
        factory: Slim4\Themes\Twig\TwigThemeRenderer
        arguments:
            theme: @Slim4\Themes\Interface\ThemeInterface
            routeParser: @Slim\Interfaces\RouteParserInterface
        tags: [theme]
```

## Usage

### Using in Controllers

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

        return $this->themeRenderer->renderResponse($response, 'home.twig', $data);
    }
}
```

### Using in Templates

#### Twig

```twig
{% extends "layout.twig" %}

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
{extends "layout.latte"}

{block content}
    <h1>{$title}</h1>
    <p>{$content}</p>

    <p>Current theme: {$theme->getName()}</p>

    <a href="{url_for('home')}">Home</a>
    <a href="{url_for('about')}">About</a>
{/block}
```

### Theme Switching

You can switch themes by adding a query parameter to the URL:

```
https://example.com/?theme=dark
```

This will set a cookie with the theme name, so the theme will be remembered for future requests.

## Extending

### Creating a Custom Theme

```php
use Slim4\Themes\Interface\ThemeInterface;

class CustomTheme implements ThemeInterface
{
    private string $name;
    private string $path;
    private bool $isDefault;
    private ?string $parentTheme;
    private array $config;

    public function __construct(
        string $name,
        string $path,
        bool $isDefault = false,
        ?string $parentTheme = null,
        array $config = []
    ) {
        $this->name = $name;
        $this->path = $path;
        $this->isDefault = $isDefault;
        $this->parentTheme = $parentTheme;
        $this->config = $config;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getParentTheme(): ?string
    {
        return $this->parentTheme;
    }

    public function getAssetsPath(): string
    {
        return $this->path . '/assets';
    }

    public function getTemplatesPath(): string
    {
        return $this->path . '/templates';
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
```

### Creating a Custom Theme Loader

```php
use Slim4\Root\PathsInterface;
use Slim4\Themes\Attributes\Singleton;
use Slim4\Themes\Exception\ThemeNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;

#[Singleton]
class CustomThemeLoader implements ThemeLoaderInterface
{
    private PathsInterface $paths;
    private array $themes = [];
    private ?string $defaultTheme = null;

    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
        $this->loadThemes();
    }

    public function load(string $themeName): ThemeInterface
    {
        if (!$this->themeExists($themeName)) {
            throw new ThemeNotFoundException($themeName);
        }

        return $this->themes[$themeName];
    }

    public function getAvailableThemes(): array
    {
        return array_values($this->themes);
    }

    public function getDefaultTheme(): ThemeInterface
    {
        if ($this->defaultTheme === null) {
            throw new ThemeNotFoundException('default');
        }

        return $this->themes[$this->defaultTheme];
    }

    public function themeExists(string $themeName): bool
    {
        return isset($this->themes[$themeName]);
    }

    private function loadThemes(): void
    {
        // Load themes from a custom source
    }
}
```

### Creating a Custom Theme Renderer

```php
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim4\Themes\Exception\TemplateNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;
use Slim4\Themes\Interface\ThemeResponseInterface;

class CustomThemeRenderer implements ThemeRendererInterface, ThemeResponseInterface
{
    private ThemeInterface $theme;
    private array $globals = [];
    private RouteParserInterface $routeParser;

    public function __construct(ThemeInterface $theme, RouteParserInterface $routeParser)
    {
        $this->theme = $theme;
        $this->routeParser = $routeParser;
    }

    public function setTheme(ThemeInterface $theme): void
    {
        $this->theme = $theme;
    }

    public function getTheme(): ThemeInterface
    {
        return $this->theme;
    }

    public function render(string $template, array $data = []): string
    {
        if (!$this->templateExists($template)) {
            throw new TemplateNotFoundException($template, $this->theme->getName());
        }

        // Add theme to data
        $data['theme'] = $this->theme;

        // Add globals to data
        foreach ($this->globals as $name => $value) {
            $data[$name] = $value;
        }

        // Render template using a custom renderer
        return 'Rendered template';
    }

    public function renderResponse(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        if (!$this->templateExists($template)) {
            throw new TemplateNotFoundException($template, $this->theme->getName());
        }

        // Add theme to data
        $data['theme'] = $this->theme;

        // Add globals to data
        foreach ($this->globals as $name => $value) {
            $data[$name] = $value;
        }

        // Render template using a custom renderer
        $content = 'Rendered template';
        $response->getBody()->write($content);

        return $response;
    }

    public function templateExists(string $template): bool
    {
        return file_exists($this->getTemplatePath($template));
    }

    public function getTemplatePath(string $template): string
    {
        $templatePath = $this->theme->getTemplatesPath() . '/' . $template;

        if (!file_exists($templatePath)) {
            // Try parent theme if exists
            if ($this->theme->getParentTheme() !== null) {
                $parentThemePath = dirname($this->theme->getPath()) . '/' . $this->theme->getParentTheme();
                $parentTemplatePath = $parentThemePath . '/templates/' . $template;

                if (file_exists($parentTemplatePath)) {
                    return $parentTemplatePath;
                }
            }

            throw new TemplateNotFoundException($template, $this->theme->getName());
        }

        return $templatePath;
    }

    public function addGlobal(string $name, $value): void
    {
        $this->globals[$name] = $value;
    }
}
```
