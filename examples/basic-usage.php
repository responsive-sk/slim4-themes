<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use Slim4\Root\PathsInterface;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;
use Slim4\Themes\Middleware\ThemeMiddleware;
use Slim4\Themes\Twig\TwigTheme;
use Slim4\Themes\Twig\TwigThemeLoader;
use Slim4\Themes\Twig\TwigThemeRenderer;

require __DIR__ . '/../vendor/autoload.php';

// Create container
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    // Paths
    PathsInterface::class => function () {
        return new class implements PathsInterface {
            public function getRootPath(): string
            {
                return __DIR__;
            }

            public function getConfigPath(): string
            {
                return __DIR__ . '/config';
            }

            public function getPublicPath(): string
            {
                return __DIR__ . '/public';
            }

            public function getVarPath(): string
            {
                return __DIR__ . '/var';
            }

            public function getTempPath(): string
            {
                return __DIR__ . '/var/tmp';
            }

            public function getCachePath(): string
            {
                return __DIR__ . '/var/cache';
            }

            public function getLogPath(): string
            {
                return __DIR__ . '/var/log';
            }
        };
    },

    // Themes
    ThemeInterface::class => function (ContainerInterface $container) {
        return new TwigTheme(
            'default',
            __DIR__ . '/templates/themes/default',
            true
        );
    },

    ThemeLoaderInterface::class => function (ContainerInterface $container) {
        return new TwigThemeLoader(
            $container->get(PathsInterface::class),
            [
                'default' => 'default',
                'available' => ['default', 'dark']
            ]
        );
    },

    ThemeRendererInterface::class => function (ContainerInterface $container) {
        return new TwigThemeRenderer(
            $container->get(ThemeInterface::class),
            $container->get(RouteParserInterface::class)
        );
    },
]);

$container = $containerBuilder->build();

// Create app
$app = AppFactory::createFromContainer($container);

// Add theme middleware
$app->add(new ThemeMiddleware(
    $container->get(ThemeLoaderInterface::class),
    $container->get(ThemeRendererInterface::class),
    'theme',
    'theme'
));

// Add routes
$app->get('/', function ($request, $response) use ($container) {
    $themeRenderer = $container->get(ThemeRendererInterface::class);

    $data = [
        'title' => 'Home',
        'content' => 'Welcome to the home page!',
    ];

    $html = $themeRenderer->render('home.twig', $data);
    $response->getBody()->write($html);
    return $response;
})->setName('home');

$app->get('/about', function ($request, $response) use ($container) {
    $themeRenderer = $container->get(ThemeRendererInterface::class);

    $data = [
        'title' => 'About',
        'content' => 'This is the about page.',
    ];

    $html = $themeRenderer->render('about.twig', $data);
    $response->getBody()->write($html);
    return $response;
})->setName('about');

// Run app
$app->run();
