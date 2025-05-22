<?php

declare(strict_types=1);

namespace Slim4\Themes\Provider;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;

/**
 * Provides the theme from the container
 */
class ThemeProvider implements ThemeInterface
{
    /**
     * @var ContainerInterface The container
     */
    private ContainerInterface $container;

    /**
     * @var ServerRequestInterface|null The current request
     */
    private ?ServerRequestInterface $request = null;

    /**
     * @var ThemeInterface|null The current theme
     */
    private ?ThemeInterface $theme = null;

    /**
     * Constructor
     *
     * @param ContainerInterface $container The container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Set the current request
     *
     * @param ServerRequestInterface $request The request
     * @return void
     */
    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * Get the theme
     *
     * @return ThemeInterface The theme
     */
    public function getTheme(): ThemeInterface
    {
        if ($this->theme === null) {
            /** @var ThemeResolver $themeResolver */
            $themeResolver = $this->container->get(ThemeResolver::class);
            
            // Resolve theme based on request
            $this->theme = $themeResolver->resolveTheme($this->request);
            
            // Set theme in renderer
            /** @var ThemeRendererInterface $themeRenderer */
            $themeRenderer = $this->container->get(ThemeRendererInterface::class);
            $themeRenderer->setTheme($this->theme);
        }
        
        return $this->theme;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->getTheme()->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->getTheme()->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function isDefault(): bool
    {
        return $this->getTheme()->isDefault();
    }

    /**
     * {@inheritdoc}
     */
    public function getParentTheme(): ?string
    {
        return $this->getTheme()->getParentTheme();
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetsPath(): string
    {
        return $this->getTheme()->getAssetsPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplatesPath(): string
    {
        return $this->getTheme()->getTemplatesPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return $this->getTheme()->getConfig();
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplatePath(string $template): string
    {
        return $this->getTheme()->getTemplatePath($template);
    }
}
