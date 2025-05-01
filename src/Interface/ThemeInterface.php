<?php

declare(strict_types=1);

namespace Slim4\Themes\Interface;

/**
 * Interface for themes.
 */
interface ThemeInterface
{
    /**
     * Get the name of the theme.
     *
     * @return string The name of the theme
     */
    public function getName(): string;

    /**
     * Get the path to the theme.
     *
     * @return string The path to the theme
     */
    public function getPath(): string;

    /**
     * Check if this is the default theme.
     *
     * @return bool True if this is the default theme
     */
    public function isDefault(): bool;

    /**
     * Get the parent theme name.
     *
     * @return string|null The parent theme name or null if no parent
     */
    public function getParentTheme(): ?string;

    /**
     * Get the path to the assets directory.
     *
     * @return string The path to the assets directory
     */
    public function getAssetsPath(): string;

    /**
     * Get the path to the templates directory.
     *
     * @return string The path to the templates directory
     */
    public function getTemplatesPath(): string;

    /**
     * Get the theme configuration.
     *
     * @return array<string, mixed> The theme configuration
     */
    public function getConfig(): array;
}
