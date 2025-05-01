<?php

declare(strict_types=1);

namespace Slim4\Themes\Blade;

use Illuminate\View\Factory;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim4\Themes\Exception\TemplateNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;
use Slim4\Themes\Interface\ThemeResponseInterface;
use Slim4\Vite\BladeExtension;
use Slim4\Vite\ViteService;

/**
 * Blade implementation of ThemeRendererInterface.
 */
class BladeThemeRenderer implements ThemeRendererInterface, ThemeResponseInterface
{
    /**
     * @var Factory The Blade factory
     */
    private Factory $blade;

    /**
     * @var ThemeInterface The current theme
     */
    private ThemeInterface $theme;

    /**
     * @var array<string, mixed> The global variables
     */
    private array $globals = [];

    /**
     * @var RouteParserInterface The route parser
     */
    private RouteParserInterface $routeParser;

    /**
     * @var ViteService|null The Vite service
     */
    private ?ViteService $viteService = null;

    /**
     * Constructor.
     *
     * @param ThemeInterface $theme The theme
     * @param RouteParserInterface $routeParser The route parser
     * @param ViteService|null $viteService The Vite service
     */
    public function __construct(ThemeInterface $theme, RouteParserInterface $routeParser, ?ViteService $viteService = null)
    {
        $this->theme = $theme;
        $this->routeParser = $routeParser;
        $this->viteService = $viteService;
        $this->initializeBlade();
    }

    /**
     * {@inheritdoc}
     */
    public function setTheme(ThemeInterface $theme): void
    {
        $this->theme = $theme;
        $this->initializeBlade();
    }

    /**
     * {@inheritdoc}
     */
    public function getTheme(): ThemeInterface
    {
        return $this->theme;
    }

    /**
     * {@inheritdoc}
     */
    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        if (!$this->templateExists($template)) {
            throw new TemplateNotFoundException($template, $this->theme->getName());
        }

        // Add theme to data
        $data['theme'] = $this->theme;

        // Add globals to data
        $data = array_merge($this->globals, $data);

        // Add Vite service to data if available
        if ($this->viteService !== null) {
            $data['__vite'] = $this->viteService;
        }

        // Render template
        return $this->blade->make($template, $data)->render();
    }

    /**
     * {@inheritdoc}
     */
    /**
     * @param array<string, mixed> $data
     */
    public function renderResponse(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        if (!$this->templateExists($template)) {
            throw new TemplateNotFoundException($template, $this->theme->getName());
        }

        // Add theme to data
        $data['theme'] = $this->theme;

        // Add globals to data
        $data = array_merge($this->globals, $data);

        // Add Vite service to data if available
        if ($this->viteService !== null) {
            $data['__vite'] = $this->viteService;
        }

        // Render template
        $content = $this->blade->make($template, $data)->render();
        $response->getBody()->write($content);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function templateExists(string $template): bool
    {
        return $this->blade->exists($template);
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplatePath(string $template): string
    {
        if (!$this->templateExists($template)) {
            throw new TemplateNotFoundException($template, $this->theme->getName());
        }

        // Return the template path without adding a slash
        // The template path should already be complete from the configuration
        return $this->theme->getTemplatesPath() . '/' . $template;
    }

    /**
     * {@inheritdoc}
     */
    public function addGlobal(string $name, $value): void
    {
        $this->globals[$name] = $value;
        $this->blade->share($name, $value);
    }

    /**
     * Initialize Blade.
     *
     * @return void
     */
    private function initializeBlade(): void
    {
        // Create Blade factory
        $viewFinder = new \Illuminate\View\FileViewFinder(
            new \Illuminate\Filesystem\Filesystem(),
            [$this->theme->getTemplatesPath()]
        );

        $this->blade = new Factory(
            new \Illuminate\View\Engines\EngineResolver(),
            $viewFinder
        );

        // Add parent theme templates directory if exists
        if ($this->theme->getParentTheme() !== null) {
            $parentThemePath = dirname($this->theme->getPath()) . '/' . $this->theme->getParentTheme();
            if (is_dir($parentThemePath)) {
                $viewFinder->addLocation($parentThemePath);
            }
        }

        // Add globals
        foreach ($this->globals as $name => $value) {
            $this->blade->share($name, $value);
        }

        // Add theme to globals
        $this->blade->share('theme', $this->theme);

        // Add Vite extension if Vite service is available
        if ($this->viteService !== null) {
            $extension = new BladeExtension($this->viteService);
            $extension->register($this->blade);
            $this->blade->share('__vite', $this->viteService);
        }
    }
}
