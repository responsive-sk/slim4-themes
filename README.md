# Slim4 Themes

A flexible theme system for Slim 4 applications.

## Features

- Support for multiple template engines (Twig, Latte)
- Easy theme switching
- Theme inheritance
- PSR-7 compatible
- Singleton attribute for dependency injection

## Installation

```bash
composer require responsive-sk/slim4-themes
```

## Usage

### Configuration

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
        __DIR__ . '/templates'
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

### Using in controllers

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

## Documentation

For more detailed documentation, see the [docs](docs) directory.

## Examples

Check out the [examples](examples) directory for working examples:

- [Basic Usage](examples/basic-usage.php) - A simple example of how to use the package with Twig.

To run the examples, you need to install the package and its dependencies:

```bash
composer install
```

Then, you can run the examples using PHP's built-in web server:

```bash
cd examples
php -S localhost:8000 basic-usage.php
```

Then, open your browser and navigate to http://localhost:8000.

## License

This package is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
