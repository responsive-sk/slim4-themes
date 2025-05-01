<?php

declare(strict_types=1);

namespace Slim4\Themes\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim4\Themes\Exception\ThemeNotFoundException;
use Slim4\Themes\Interface\ThemeLoaderInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;

/**
 * Middleware for theme handling.
 */
class ThemeMiddleware implements MiddlewareInterface
{
    /**
     * @var ThemeLoaderInterface The theme loader
     */
    private ThemeLoaderInterface $themeLoader;

    /**
     * @var ThemeRendererInterface The theme renderer
     */
    private ThemeRendererInterface $themeRenderer;

    /**
     * @var string The cookie name
     */
    private string $cookieName;

    /**
     * @var string The query parameter name
     */
    private string $queryParam;

    /**
     * Constructor.
     *
     * @param ThemeLoaderInterface $themeLoader The theme loader
     * @param ThemeRendererInterface $themeRenderer The theme renderer
     * @param string $cookieName The cookie name
     * @param string $queryParam The query parameter name
     */
    public function __construct(
        ThemeLoaderInterface $themeLoader,
        ThemeRendererInterface $themeRenderer,
        string $cookieName = 'theme',
        string $queryParam = 'theme'
    ) {
        $this->themeLoader = $themeLoader;
        $this->themeRenderer = $themeRenderer;
        $this->cookieName = $cookieName;
        $this->queryParam = $queryParam;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get theme from query parameter
        $queryParams = $request->getQueryParams();
        $themeName = $queryParams[$this->queryParam] ?? null;

        // Get theme from cookie if not in query parameter
        if ($themeName === null) {
            $cookies = $request->getCookieParams();
            $themeName = $cookies[$this->cookieName] ?? null;
        }

        // Get default theme if no theme is specified
        if ($themeName === null) {
            $theme = $this->themeLoader->getDefaultTheme();
        } else {
            // Try to load theme
            try {
                $theme = $this->themeLoader->load($themeName);
            } catch (ThemeNotFoundException $e) {
                // Use default theme if theme not found
                $theme = $this->themeLoader->getDefaultTheme();
            }
        }

        // Set theme in renderer
        $this->themeRenderer->setTheme($theme);

        // Add theme to request attributes
        $request = $request->withAttribute('theme', $theme);

        // Process request
        $response = $handler->handle($request);

        // Set theme cookie if theme is specified in query parameter
        if (isset($queryParams[$this->queryParam])) {
            $response = $response->withHeader(
                'Set-Cookie',
                sprintf(
                    '%s=%s; Path=/; HttpOnly; SameSite=Lax',
                    $this->cookieName,
                    $queryParams[$this->queryParam]
                )
            );
        }

        return $response;
    }
}
