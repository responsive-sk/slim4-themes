<?php

declare(strict_types=1);

namespace Slim4\Themes\Interface;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface for theme renderers.
 */
interface ThemeRendererInterface
{
    /**
     * Set the current theme.
     *
     * @param ThemeInterface $theme The theme to set
     * @return void
     */
    public function setTheme(ThemeInterface $theme): void;

    /**
     * Get the current theme.
     *
     * @return ThemeInterface|null The current theme or null if not set
     */
    public function getTheme(): ?ThemeInterface;

    /**
     * Render a template.
     *
     * @param ResponseInterface $response The response to render to
     * @param string $template The template to render
     * @param array<string, mixed> $data The data to pass to the template
     * @return ResponseInterface The rendered response
     */
    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface;
}
