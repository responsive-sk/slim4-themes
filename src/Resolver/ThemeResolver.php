<?php

declare(strict_types=1);

namespace Slim4\Themes\Resolver;

use Psr\Log\LoggerInterface;
use Slim4\Themes\Attributes\Singleton;
use Slim4\Themes\Exception\TemplateNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;

/**
 * Theme resolver.
 */
#[Singleton]
class ThemeResolver
{
    /**
     * @var ThemeLoaderInterface The theme loader
     */
    private ThemeLoaderInterface $themeLoader;

    /**
     * @var LoggerInterface The logger
     */
    private LoggerInterface $logger;

    /**
     * @var ThemeInterface|null The current theme
     */
    private ?ThemeInterface $currentTheme = null;

    /**
     * Constructor.
     *
     * @param ThemeLoaderInterface $themeLoader The theme loader
     * @param LoggerInterface $logger The logger
     */
    public function __construct(ThemeLoaderInterface $themeLoader, LoggerInterface $logger)
    {
        $this->themeLoader = $themeLoader;
        $this->logger = $logger;
    }

    /**
     * Resolve a template path.
     *
     * @param string $template The template to resolve
     * @return string The resolved template path
     * @throws TemplateNotFoundException If the template is not found
     */
    public function resolveTemplate(string $template): string
    {
        $theme = $this->getCurrentTheme();
        $templatePath = $theme->getTemplatesPath() . '/' . $template;

        if (!file_exists($templatePath)) {
            // Try parent theme if exists
            if ($theme->getParentTheme() !== null) {
                $parentThemePath = dirname($theme->getPath()) . '/' . $theme->getParentTheme();
                $parentTemplatePath = $parentThemePath . '/templates/' . $template;

                if (file_exists($parentTemplatePath)) {
                    return $parentTemplatePath;
                }
            }

            throw new TemplateNotFoundException($template, $theme->getName());
        }

        return $templatePath;
    }

    /**
     * Get the current theme.
     *
     * @return ThemeInterface The current theme
     */
    public function getCurrentTheme(): ThemeInterface
    {
        if ($this->currentTheme === null) {
            $this->currentTheme = $this->themeLoader->getDefaultTheme();
        }

        return $this->currentTheme;
    }

    /**
     * Set the current theme.
     *
     * @param string $themeName The name of the theme
     * @return void
     */
    public function setCurrentTheme(string $themeName): void
    {
        try {
            $this->currentTheme = $this->themeLoader->load($themeName);
        } catch (\Slim4\Themes\Exception\ThemeNotFoundException $e) {
            $this->logger->warning(sprintf('Theme "%s" not found, using default theme', $themeName));
            $this->currentTheme = $this->themeLoader->getDefaultTheme();
        }
    }

    /**
     * Get all available themes.
     *
     * @return array<int, ThemeInterface> The available themes
     */
    public function getAvailableThemes(): array
    {
        return $this->themeLoader->getAvailableThemes();
    }
}
