<?php

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use Slim4\Root\Paths;
use Slim4\Root\PathsInterface;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;
use Slim4\Themes\Middleware\ThemeMiddleware;
use Slim4\Themes\Plates\PlatesThemeLoader;
use Slim4\Themes\Plates\PlatesThemeRenderer;
use Slim4\Themes\Latte\LatteThemeLoader;
use Slim4\Themes\Latte\LatteThemeRenderer;
use Slim4\Themes\Twig\TwigThemeLoader;
use Slim4\Themes\Twig\TwigThemeRenderer;

require_once __DIR__ . '/../vendor/autoload.php';

// Create container
$containerBuilder = new ContainerBuilder();

// Define container definitions
$containerBuilder->addDefinitions([
    // Paths
    PathsInterface::class => function () {
        return new Paths(__DIR__ . '/..');
    },

    // Settings
    'settings' => [
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
    ],

    // Theme services
    ThemeLoaderInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['theme'];
        $engine = $settings['engine'] ?? 'plates';

        return match ($engine) {
            'twig' => new TwigThemeLoader(
                $container->get(PathsInterface::class),
                $settings
            ),
            'latte' => new LatteThemeLoader(
                $container->get(PathsInterface::class),
                $settings
            ),
            default => new PlatesThemeLoader(
                $container->get(PathsInterface::class),
                $settings
            ),
        };
    },

    ThemeInterface::class => function (ContainerInterface $container) {
        return $container->get(ThemeLoaderInterface::class)->getDefaultTheme();
    },

    ThemeRendererInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['theme'];
        $engine = $settings['engine'] ?? 'plates';

        return match ($engine) {
            'twig' => new TwigThemeRenderer(
                $container->get(ThemeInterface::class),
                $container->get(RouteParserInterface::class)
            ),
            'latte' => new LatteThemeRenderer(
                $container->get(ThemeInterface::class),
                $container->get(RouteParserInterface::class)
            ),
            default => new PlatesThemeRenderer(
                $container->get(ThemeInterface::class)
            ),
        };
    },
]);

// Build container
$container = $containerBuilder->build();

// Create app
$app = AppFactory::createFromContainer($container);

// Add theme middleware
$app->add(new ThemeMiddleware(
    $container->get(ThemeLoaderInterface::class),
    $container->get(ThemeRendererInterface::class),
    $container->get('settings')['theme']['cookie_name'] ?? 'theme',
    $container->get('settings')['theme']['query_param'] ?? 'theme'
));

// Define routes
$app->get('/', function ($request, $response) use ($container) {
    /** @var ThemeRendererInterface $themeRenderer */
    $themeRenderer = $container->get(ThemeRendererInterface::class);
    
    $data = [
        'title' => 'Home',
        'content' => 'Welcome to the home page!',
    ];
    
    $html = $themeRenderer->render('home/index', $data);
    $response->getBody()->write($html);
    
    return $response;
})->setName('home');

// Run app
$app->run();
