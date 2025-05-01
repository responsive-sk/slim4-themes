<?php

declare(strict_types=1);

namespace Slim4\Themes\Twig;

use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Uri;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Slim Twig extension.
 */
class SlimTwigExtension extends AbstractExtension
{
    /**
     * @var RouteParserInterface The route parser
     */
    private RouteParserInterface $routeParser;

    /**
     * Constructor.
     *
     * @param RouteParserInterface $routeParser The route parser
     */
    public function __construct(RouteParserInterface $routeParser)
    {
        $this->routeParser = $routeParser;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            // Make sure these functions are properly registered
            new TwigFunction('url_for', [$this, 'urlFor']),
            new TwigFunction('full_url_for', [$this, 'fullUrlFor']),
            new TwigFunction('is_current_url', [$this, 'isCurrentUrl']),
            new TwigFunction('current_url', [$this, 'currentUrl']),
        ];
    }

    /**
     * Get URL for a named route.
     *
     * @param string $routeName The route name
     * @param array<string, mixed> $data The route data
     * @param array<string, mixed> $queryParams The query parameters
     * @return string The URL
     */
    public function urlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->urlFor($routeName, $data, $queryParams);
    }

    /**
     * Get full URL for a named route.
     *
     * @param string $routeName The route name
     * @param array<string, mixed> $data The route data
     * @param array<string, mixed> $queryParams The query parameters
     * @return string The full URL
     */
    public function fullUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        // Create a URI instance using Nyholm PSR-7 implementation
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $uri = new \Nyholm\Psr7\Uri('http://' . $host);

        return $this->routeParser->fullUrlFor($uri, $routeName, $data, $queryParams);
    }

    /**
     * Check if the current URL matches the given route name.
     *
     * @param string $routeName The route name
     * @return bool True if the current URL matches the route name
     */
    public function isCurrentUrl(string $routeName): bool
    {
        $currentUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $url = $this->routeParser->urlFor($routeName);

        return $currentUrl === $url;
    }

    /**
     * Get the current URL.
     *
     * @return string The current URL
     */
    public function currentUrl(): string
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    }
}
