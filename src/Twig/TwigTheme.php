<?php

declare(strict_types=1);

namespace Slim4\Themes\Twig;

use Slim4\Themes\Interface\ThemeInterface;

/**
 * Twig implementation of ThemeInterface.
 */
class TwigTheme implements ThemeInterface
{
    /**
     * @var string The name of the theme
     */
    private string $name;

    /**
     * @var string The path to the theme
     */
    private string $path;

    /**
     * @var bool Whether this is the default theme
     */
    private bool $isDefault;

    /**
     * @var string|null The parent theme name
     */
    private ?string $parentTheme;

    /**
     * @var array The theme configuration
     */
    private array $config;

    /**
     * Constructor.
     *
     * @param string $name The name of the theme
     * @param string $path The path to the theme
     * @param bool $isDefault Whether this is the default theme
     * @param string|null $parentTheme The parent theme name
     * @param array $config The theme configuration
     */
    public function __construct(
        string $name,
        string $path,
        bool $isDefault = false,
        ?string $parentTheme = null,
        array $config = []
    ) {
        $this->name = $name;
        $this->path = $path;
        $this->isDefault = $isDefault;
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
    public function isDefault(): bool
    {
        return $this->isDefault;
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
    public function getAssetsPath(): string
    {
        return $this->path . '/assets';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplatesPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
