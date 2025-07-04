<?php

declare(strict_types=1);

namespace Slim4\Themes\Plates;

use Slim4\Root\PathsInterface;
use Slim4\Themes\Attributes\Singleton;
use Slim4\Themes\Exception\ThemeNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;

/**
 * Plates implementation of ThemeLoaderInterface.
 */
#[Singleton]
class PlatesThemeLoader implements ThemeLoaderInterface
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
    public function load(string $themeName): ThemeInterface
    {
        if (!$this->themeExists($themeName)) {
            throw new ThemeNotFoundException($themeName);
        }

        return $this->themes[$themeName];
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
    public function getDefaultTheme(): ThemeInterface
    {
        if ($this->defaultTheme === null) {
            throw new ThemeNotFoundException('default');
        }

        return $this->themes[$this->defaultTheme];
    }

    /**
     * {@inheritdoc}
     */
    public function themeExists(string $themeName): bool
    {
        return isset($this->themes[$themeName]);
    }

    /**
     * Load all themes.
     *
     * @return void
     */
    private function loadThemes(): void
    {
        $themesPath = $this->paths->getTemplatesPath() . '/plates';
        $themeDirectories = glob($themesPath . '/*', GLOB_ONLYDIR) ?: [];

        // Get default theme from settings
        $defaultTheme = $this->settings['default'] ?? 'default';

        // Get available themes from settings
        $availableThemes = $this->settings['available'] ?? [];

        foreach ($themeDirectories as $themeDirectory) {
            $themeName = basename($themeDirectory);

            // Skip themes that are not in the available themes list
            if (!empty($availableThemes) && is_array($availableThemes) && !in_array($themeName, $availableThemes)) {
                continue;
            }

            $isDefault = $themeName === $defaultTheme;
            $parentTheme = null;
            $config = [];

            // Load theme configuration
            $configFile = $themeDirectory . '/theme.json';
            if (file_exists($configFile)) {
                $configContent = file_get_contents($configFile);
                if ($configContent !== false) {
                    $configData = json_decode($configContent, true);
                    if (is_array($configData)) {
                        $parentTheme = $configData['parent'] ?? null;
                        $config = $configData;
                    }
                }
            }

            $theme = new PlatesTheme(
                $themeName,
                $themeDirectory,
                $isDefault,
                $parentTheme,
                $config
            );

            $this->themes[$themeName] = $theme;

            if ($isDefault) {
                $this->defaultTheme = $themeName;
            }
        }

        // If no default theme is set, use the first theme
        if ($this->defaultTheme === null && !empty($this->themes)) {
            $this->defaultTheme = array_key_first($this->themes);
            $this->themes[$this->defaultTheme] = new PlatesTheme(
                $this->defaultTheme,
                $this->themes[$this->defaultTheme]->getPath(),
                true,
                $this->themes[$this->defaultTheme]->getParentTheme(),
                $this->themes[$this->defaultTheme]->getConfig()
            );
        }
    }
}
