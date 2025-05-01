<?php

declare(strict_types=1);

namespace Slim4\Themes\Blade;

use Slim4\Root\PathsInterface;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;

/**
 * Blade implementation of ThemeLoaderInterface.
 */
class BladeThemeLoader implements ThemeLoaderInterface
{
    /**
     * @var PathsInterface The paths
     */
    private PathsInterface $paths;

    /**
     * @var array<string, ThemeInterface> The loaded themes
     */
    private array $themes = [];

    /**
     * @var string|null The default theme name
     */
    private ?string $defaultTheme = null;

    /**
     * @var array<string, mixed> The theme settings
     */
    private array $settings = [];

    /**
     * Constructor.
     *
     * @param PathsInterface $paths The paths
     * @param array<string, mixed> $settings The theme settings
     */
    public function __construct(PathsInterface $paths, array $settings = [])
    {
        $this->paths = $paths;
        $this->settings = $settings;
        $this->loadThemes();
    }

    /**
     * {@inheritdoc}
     */
    public function getTheme(string $name): ?ThemeInterface
    {
        return $this->themes[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultTheme(): ThemeInterface
    {
        if ($this->defaultTheme === null) {
            throw new \RuntimeException('No default theme set');
        }

        $theme = $this->getTheme($this->defaultTheme);
        if ($theme === null) {
            throw new \RuntimeException(sprintf('Default theme "%s" not found', $this->defaultTheme));
        }

        return $theme;
    }

    /**
     * {@inheritdoc}
     */
    /**
     * @return array<int, ThemeInterface>
     */
    public function getAvailableThemes(): array
    {
        return array_values($this->themes);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultTheme(string $name): void
    {
        $this->defaultTheme = $name;
    }

    /**
     * Load themes from the themes directory.
     *
     * @return void
     */
    private function loadThemes(): void
    {
        // Get available themes from settings
        $availableThemes = $this->settings['available'] ?? [];

        // Get default theme from settings
        $this->defaultTheme = $this->settings['default'] ?? null;

        // Get engine-specific settings or use global settings
        $engineSettings = $this->settings['engines']['blade'] ?? [];
        
        // Get templates path from engine settings, global settings, or use default
        $templatesPath = $engineSettings['templates_path'] ?? $this->settings['templates_path'] ?? 'templates';
        $themesPath = $this->paths->getRootPath() . '/' . $templatesPath;

        // Check if themes directory exists
        if (!is_dir($themesPath)) {
            return;
        }

        // Get all directories in themes directory
        $themeDirectories = glob($themesPath . '/*', GLOB_ONLYDIR) ?: [];

        // Loop through theme directories
        foreach ($themeDirectories as $themeDirectory) {
            // Get theme name from directory name
            $themeName = basename($themeDirectory);

            // Skip themes that are not in the available themes list
            if (!empty($availableThemes) && is_array($availableThemes) && !in_array($themeName, $availableThemes)) {
                continue;
            }

            // Create theme
            $theme = new BladeTheme(
                $themeName,
                $themeDirectory,
                $themeName === $this->defaultTheme
            );

            // Check if theme has a config file
            $configFile = $themeDirectory . '/theme.json';
            if (file_exists($configFile)) {
                $configContent = file_get_contents($configFile);
                if ($configContent !== false) {
                    $configData = json_decode($configContent, true);
                    if (is_array($configData)) {
                        // Set parent theme
                        $parentTheme = $configData['parent'] ?? null;
                        if ($parentTheme !== null) {
                            $theme = new BladeTheme(
                                $themeName,
                                $themeDirectory,
                                $themeName === $this->defaultTheme,
                                $parentTheme,
                                $configData
                            );
                        }
                    }
                }
            }

            // Add theme to themes array
            $this->themes[$themeName] = $theme;
        }
    }
}
