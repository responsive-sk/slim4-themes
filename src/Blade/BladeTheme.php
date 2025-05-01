<?php

declare(strict_types=1);

namespace Slim4\Themes\Blade;

use Slim4\Themes\Interface\ThemeInterface;

/**
 * Blade implementation of ThemeInterface.
 */
class BladeTheme implements ThemeInterface
{
    /**
     * @var string The theme name
     */
    private string $name;

    /**
     * @var string The theme path
     */
    private string $path;

    /**
     * @var string|null The parent theme name
     */
    private ?string $parentTheme;

    /**
     * @var bool Whether the theme is active
     */
    private bool $active;

    /**
     * @var array<string, mixed> The theme configuration
     */
    private array $config;

    /**
     * Constructor.
     *
     * @param string $name The theme name
     * @param string $path The theme path
     * @param bool $active Whether the theme is active
     * @param string|null $parentTheme The parent theme name
     * @param array<string, mixed> $config The theme configuration
     */
    public function __construct(
        string $name,
        string $path,
        bool $active = false,
        ?string $parentTheme = null,
        array $config = []
    ) {
        $this->name = $name;
        $this->path = $path;
        $this->active = $active;
        $this->parentTheme = $parentTheme;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * {@inheritdoc}
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentTheme(): ?string
    {
        return $this->parentTheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplatesPath(): string
    {
        // Return the path to the templates directory
        // This should be the complete path without adding '/templates'
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
