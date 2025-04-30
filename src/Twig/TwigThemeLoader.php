<?php

declare(strict_types=1);

namespace Slim4\Themes\Twig;

use Slim4\Root\PathsInterface;
use Slim4\Themes\Attributes\Singleton;
use Slim4\Themes\Exception\ThemeNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;

/**
 * Twig implementation of ThemeLoaderInterface.
 */
#[Singleton]
class TwigThemeLoader implements ThemeLoaderInterface
{
    /**
     * @var PathsInterface The paths
     */
    private PathsInterface $paths;
    
    /**
     * @var array The loaded themes
     */
    private array $themes = [];
    
    /**
     * @var string|null The default theme name
     */
    private ?string $defaultTheme = null;
    
    /**
     * Constructor.
     *
     * @param PathsInterface $paths The paths
     */
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
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
        $themesPath = $this->paths->getRootPath() . '/templates/themes';
        $themeDirectories = glob($themesPath . '/*', GLOB_ONLYDIR);
        
        foreach ($themeDirectories as $themeDirectory) {
            $themeName = basename($themeDirectory);
            $isDefault = file_exists($themeDirectory . '/.default');
            $parentTheme = null;
            $config = [];
            
            // Load theme configuration
            $configFile = $themeDirectory . '/theme.json';
            if (file_exists($configFile)) {
                $configData = json_decode(file_get_contents($configFile), true);
                $parentTheme = $configData['parent'] ?? null;
                $config = $configData;
            }
            
            $theme = new TwigTheme(
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
    }
}
