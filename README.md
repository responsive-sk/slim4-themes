# Slim4 Themes

A flexible theme system for Slim 4 applications.

## Features

- Support for multiple template engines (Plates, Twig, Latte)
- Easy theme switching
- Theme inheritance
- Engine-specific configuration
- PSR-7 compatible
- Singleton attribute for dependency injection

## Installation

```bash
composer require responsive-sk/slim4-themes
```

## Usage

### Configuration

#### Basic Configuration

```php
// Settings
$settings = [
    'theme' => [
        'default' => 'default',
        'available' => ['default', 'dark'],
        'cookie_name' => 'theme',
        'query_param' => 'theme',
        'engine' => 'plates', // Available: 'plates', 'latte', 'twig'
        'templates_path' => 'templates', // Custom path to templates directory
    ],
];

// Register theme services
$container->set(Slim4\Themes\Interface\ThemeLoaderInterface::class, function (ContainerInterface $container) use ($settings) {
    return new Slim4\Themes\Plates\PlatesThemeLoader(
        $container->get(Slim4\Root\PathsInterface::class),
        $settings['theme']
    );
});

$container->set(Slim4\Themes\Interface\ThemeInterface::class, function (ContainerInterface $container) {
    return $container->get(Slim4\Themes\Interface\ThemeLoaderInterface::class)->getDefaultTheme();
});

$container->set(Slim4\Themes\Interface\ThemeRendererInterface::class, function (ContainerInterface $container) {
    return new Slim4\Themes\Plates\PlatesThemeRenderer(
        $container->get(Slim4\Themes\Interface\ThemeInterface::class)
    );
});

// Add theme middleware
$app->add(new Slim4\Themes\Middleware\ThemeMiddleware(
    $container->get(Slim4\Themes\Interface\ThemeLoaderInterface::class),
    $container->get(Slim4\Themes\Interface\ThemeRendererInterface::class),
    $settings['theme']['cookie_name'] ?? 'theme',
    $settings['theme']['query_param'] ?? 'theme'
));
```

#### Engine-specific Configuration

You can configure each template engine separately:

```php
// Settings with engine-specific configuration
$settings = [
    'theme' => [
        'default' => 'default',
        'available' => ['default', 'dark'],
        'cookie_name' => 'theme',
        'query_param' => 'theme',
        'engine' => 'plates', // Available: 'plates', 'latte', 'twig'
        'templates_path' => 'templates', // Custom path to templates directory

        // Engine-specific settings
        'engines' => [
            'plates' => [
                'templates_path' => 'templates/plates', // Complete path to Plates templates directory
                'cookie_name' => 'plates_theme',
                'query_param' => 'plates_theme',
            ],
            'latte' => [
                'templates_path' => 'templates/latte', // Complete path to Latte templates directory
                'cookie_name' => 'latte_theme',
                'query_param' => 'latte_theme',
            ],
            'twig' => [
                'templates_path' => 'templates/twig', // Complete path to Twig templates directory
                'cookie_name' => 'twig_theme',
                'query_param' => 'twig_theme',
            ],
        ],
    ],
];
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

        // Note: No need to specify file extension (.php, .twig, .latte)
        $html = $this->themeRenderer->render('home/index', $data);
        $response->getBody()->write($html);
        return $response;
    }
}
```

## Documentation

For more detailed documentation, see the [docs](docs) directory.

## Examples

Check out the [examples](examples) directory for working examples:

- [Basic Usage](examples/basic-usage.php) - A simple example of how to use the package with Twig.
- [Multi-Engine Configuration](examples/multi-engine-config.php) - An example of how to configure multiple template engines.

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
