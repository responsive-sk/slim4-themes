<?php

declare(strict_types=1);

namespace Slim4\Themes\Interface;

use Slim4\Themes\Exception\ThemeNotFoundException;

/**
 * Interface for theme loaders.
 */
interface ThemeLoaderInterface
{
    /**
     * Load a theme by name.
     *
     * @param string $themeName The name of the theme
     * @return ThemeInterface The theme
     * @throws ThemeNotFoundException If the theme is not found
     */
    public function load(string $themeName): ThemeInterface;

    /**
     * Get all available themes.
     *
     * @return ThemeInterface[] The available themes
     */
    public function getAvailableThemes(): array;

    /**
     * Get the default theme.
     *
     * @return ThemeInterface The default theme
     * @throws ThemeNotFoundException If no default theme is found
     */
    public function getDefaultTheme(): ThemeInterface;

    /**
     * Check if a theme exists.
     *
     * @param string $themeName The name of the theme
     * @return bool True if the theme exists
     */
    public function themeExists(string $themeName): bool;
}
