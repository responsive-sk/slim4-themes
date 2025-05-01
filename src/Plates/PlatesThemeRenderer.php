<?php

declare(strict_types=1);

namespace Slim4\Themes\Plates;

use League\Plates\Engine;
use Slim4\Themes\Exception\TemplateNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;

/**
 * Plates implementation of ThemeRendererInterface.
 */
class PlatesThemeRenderer implements ThemeRendererInterface
{
    /**
     * @var ThemeInterface The current theme
     */
    private ThemeInterface $theme;

    /**
     * @var Engine|null The Plates engine
     */
    private ?Engine $plates = null;

    /**
     * @var array<string, mixed> The global variables
     */
    private array $globals = [];

    /**
     * Constructor.
     *
     * @param ThemeInterface $theme The current theme
     */
    public function __construct(ThemeInterface $theme)
    {
        $this->theme = $theme;
        $this->initializePlates();
    }

    /**
     * {@inheritdoc}
     */
    public function setTheme(ThemeInterface $theme): void
    {
        $this->theme = $theme;
        $this->initializePlates();
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

        // Render template
        if ($this->plates === null) {
            throw new \RuntimeException('Plates engine not initialized');
        }
        return $this->plates->render($template, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function templateExists(string $template): bool
    {
        if ($this->plates === null) {
            throw new \RuntimeException('Plates engine not initialized');
        }
        return $this->plates->exists($template);
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplatePath(string $template): string
    {
        if (!$this->templateExists($template)) {
            throw new TemplateNotFoundException($template, $this->theme->getName());
        }

        return $this->theme->getTemplatesPath() . '/' . $template;
    }

    /**
     * {@inheritdoc}
     */
    public function addGlobal(string $name, $value): void
    {
        $this->globals[$name] = $value;
        if ($this->plates !== null) {
            $this->plates->addData([$name => $value]);
        }
    }

    /**
     * Initialize Plates.
     *
     * @return void
     */
    private function initializePlates(): void
    {
        // Create new Plates instance
        $this->plates = new Engine($this->theme->getTemplatesPath());

        // Add parent theme templates directory if exists
        if ($this->theme->getParentTheme() !== null) {
            $parentThemePath = dirname($this->theme->getPath()) . '/' . $this->theme->getParentTheme() . '/templates';
            if (is_dir($parentThemePath)) {
                $this->plates->addFolder('parent', $parentThemePath);
            }
        }

        // Add globals
        foreach ($this->globals as $name => $value) {
            $this->plates->addData([$name => $value]);
        }

        // Add theme to globals
        $this->plates->addData(['theme' => $this->theme]);
    }
}
