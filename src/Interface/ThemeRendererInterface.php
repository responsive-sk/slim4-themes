<?php

declare(strict_types=1);

namespace Slim4\Themes\Interface;

use Slim4\Themes\Exception\TemplateNotFoundException;

/**
 * Interface for theme renderers.
 */
interface ThemeRendererInterface
{
    /**
     * Set the current theme.
     *
     * @param ThemeInterface $theme The theme
     * @return void
     */
    public function setTheme(ThemeInterface $theme): void;

    /**
     * Get the current theme.
     *
     * @return ThemeInterface The current theme
     */
    public function getTheme(): ThemeInterface;

    /**
     * Render a template with the given data.
     *
     * @param string $template The template to render
     * @param array<string, mixed> $data The data to pass to the template
     * @return string The rendered template
     * @throws TemplateNotFoundException If the template is not found
     */
    public function render(string $template, array $data = []): string;

    /**
     * Check if a template exists.
     *
     * @param string $template The template to check
     * @return bool True if the template exists
     */
    public function templateExists(string $template): bool;

    /**
     * Get the path to a template.
     *
     * @param string $template The template
     * @return string The path to the template
     * @throws TemplateNotFoundException If the template is not found
     */
    public function getTemplatePath(string $template): string;

    /**
     * Add a global variable.
     *
     * @param string $name The name of the variable
     * @param mixed $value The value of the variable
     * @return void
     */
    public function addGlobal(string $name, $value): void;
}
