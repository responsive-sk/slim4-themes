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
     * @param string $name The name of the theme to load
     * @return ThemeInterface The loaded theme
     * @throws ThemeNotFoundException If the theme could not be found
     */
    public function load(string $name): ThemeInterface;

    /**
     * Get a theme by name.
     *
     * @param string $name The name of the theme to get
     * @return ThemeInterface|null The theme or null if not found
     */
    public function getTheme(string $name): ?ThemeInterface;

    /**
     * Get the default theme.
     *
     * @return ThemeInterface The default theme
     */
    public function getDefaultTheme(): ThemeInterface;

    /**
     * Get all available themes.
     *
     * @return array<string, ThemeInterface> The available themes
     */
    public function getThemes(): array;
}
