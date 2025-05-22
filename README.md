# Slim4 Themes

Theme handling for Slim 4 applications.

## Version 2.0 Changes

Version 2.0 introduces a new approach to theme handling using Dependency Injection (DI) instead of middleware. This provides several advantages:

1. **Separation of concerns**: Theme handling is a cross-cutting concern that should be handled at the container level, not in the HTTP request pipeline.
2. **Performance**: DI-based theme handling is more efficient because it doesn't require processing the request for every request.
3. **Flexibility**: DI-based theme handling allows you to use the theme in any part of your application, not just in the HTTP request pipeline.
4. **Testability**: DI-based theme handling is easier to test because you can mock the theme provider.

## Installation

```bash
composer require responsive-sk/slim4-themes
```

## Usage

### 1. Register the services

#### Using PHP-DI

```php
use Slim4\Themes\Provider\ThemeServiceProvider;

// Create container
$container = new \DI\Container();

// Register theme services
$themeServiceProvider = new ThemeServiceProvider();
$themeServiceProvider->register($container, [
    'cookie_name' => 'theme',
    'query_param' => 'theme',
]);
```

#### Using Symfony Container

```php
use Slim4\Themes\Provider\ThemeResolver;
use Slim4\Themes\Provider\ThemeProvider;
use Slim4\Themes\Middleware\RequestAwareMiddleware;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;

// Register theme services
$containerBuilder->register(ThemeResolver::class)
    ->setArguments([
        new Reference(ThemeLoaderInterface::class),
        '%theme.cookie_name%',
        '%theme.query_param%'
    ])
    ->setPublic(true);

$containerBuilder->register(ThemeProvider::class)
    ->setArguments([new Reference('service_container')])
    ->setPublic(true);

$containerBuilder->register(RequestAwareMiddleware::class)
    ->setArguments([
        new Reference(ThemeProvider::class),
        '%theme.cookie_name%',
        '%theme.query_param%'
    ])
    ->setPublic(true);

$containerBuilder->setAlias(ThemeInterface::class, ThemeProvider::class)
    ->setPublic(true);
```

### 2. Add the middleware

```php
// Add middleware
$app->add($container->get(RequestAwareMiddleware::class));
```

### 3. Use the theme in your application

```php
// Get theme from container
$theme = $container->get(ThemeInterface::class);

// Use theme
$templatePath = $theme->getTemplatePath('home.twig');
```

## Components

### ThemeResolver

The `ThemeResolver` class is responsible for resolving the theme based on the request, cookies, or default theme.

```php
use Slim4\Themes\Provider\ThemeResolver;

// Create theme resolver
$themeResolver = new ThemeResolver(
    $themeLoader,
    'theme',
    'theme'
);

// Resolve theme
$theme = $themeResolver->resolveTheme($request);
```

### ThemeProvider

The `ThemeProvider` class is responsible for providing the theme from the container.

```php
use Slim4\Themes\Provider\ThemeProvider;

// Create theme provider
$themeProvider = new ThemeProvider($container);

// Set request
$themeProvider->setRequest($request);

// Get theme
$theme = $themeProvider->getTheme();
```

### RequestAwareMiddleware

The `RequestAwareMiddleware` class is responsible for making the request available to the `ThemeProvider`.

```php
use Slim4\Themes\Middleware\RequestAwareMiddleware;

// Create middleware
$middleware = new RequestAwareMiddleware(
    $themeProvider,
    'theme',
    'theme'
);

// Add middleware
$app->add($middleware);
```

### ThemeServiceProvider

The `ThemeServiceProvider` class is responsible for registering theme services in the container.

```php
use Slim4\Themes\Provider\ThemeServiceProvider;

// Create service provider
$serviceProvider = new ThemeServiceProvider();

// Register services
$serviceProvider->register($container, [
    'cookie_name' => 'theme',
    'query_param' => 'theme',
]);
```

## Migrating from Version 1.x

If you're migrating from version 1.x, here are the main changes:

1. Replace `ThemeMiddleware` with `RequestAwareMiddleware`:

```php
// Before
$app->add($container->get(\Slim4\Themes\Middleware\ThemeMiddleware::class));

// After
$app->add($container->get(\Slim4\Themes\Middleware\RequestAwareMiddleware::class));
```

2. Get the theme from the container instead of the request attribute:

```php
// Before
$theme = $request->getAttribute('theme');

// After
$theme = $container->get(\Slim4\Themes\Interface\ThemeInterface::class);
```

3. Register the new services in your container:

```php
// Register theme services
$themeServiceProvider = new \Slim4\Themes\Provider\ThemeServiceProvider();
$themeServiceProvider->register($container, [
    'cookie_name' => 'theme',
    'query_param' => 'theme',
]);
```

## License

MIT
