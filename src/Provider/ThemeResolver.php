<?php

declare(strict_types=1);

namespace Slim4\Themes\Provider;

use Psr\Http\Message\ServerRequestInterface;
use Slim4\Themes\Exception\ThemeNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;

/**
 * Resolves the theme based on configuration, cookies, or query parameters
 */
class ThemeResolver
{
    /**
     * @var ThemeLoaderInterface The theme loader
     */
    private ThemeLoaderInterface $themeLoader;

    /**
     * @var string The cookie name
     */
    private string $cookieName;

    /**
     * @var string The query parameter name
     */
    private string $queryParam;

    /**
     * @var ThemeInterface|null The resolved theme
     */
    private ?ThemeInterface $resolvedTheme = null;

    /**
     * Constructor
     *
     * @param ThemeLoaderInterface $themeLoader The theme loader
     * @param string $cookieName The cookie name
     * @param string $queryParam The query parameter name
     */
    public function __construct(
        ThemeLoaderInterface $themeLoader,
        string $cookieName = 'theme',
        string $queryParam = 'theme'
    ) {
        $this->themeLoader = $themeLoader;
        $this->cookieName = $cookieName;
        $this->queryParam = $queryParam;
    }

    /**
     * Resolve the theme based on the request
     *
     * @param ServerRequestInterface|null $request The request
     * @return ThemeInterface The resolved theme
     */
    public function resolveTheme(?ServerRequestInterface $request = null): ThemeInterface
    {
        // If theme is already resolved, return it
        if ($this->resolvedTheme !== null) {
            return $this->resolvedTheme;
        }

        // If no request is provided, use the default theme
        if ($request === null) {
            $this->resolvedTheme = $this->themeLoader->getDefaultTheme();
            return $this->resolvedTheme;
        }

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
            $this->resolvedTheme = $this->themeLoader->getDefaultTheme();
            return $this->resolvedTheme;
        }

        // Try to load theme
        try {
            $this->resolvedTheme = $this->themeLoader->load($themeName);
        } catch (ThemeNotFoundException $e) {
            // Use default theme if theme not found
            $this->resolvedTheme = $this->themeLoader->getDefaultTheme();
        }

        return $this->resolvedTheme;
    }

    /**
     * Get the resolved theme
     *
     * @return ThemeInterface|null The resolved theme
     */
    public function getResolvedTheme(): ?ThemeInterface
    {
        return $this->resolvedTheme;
    }

    /**
     * Reset the resolved theme
     *
     * @return void
     */
    public function resetResolvedTheme(): void
    {
        $this->resolvedTheme = null;
    }
}
