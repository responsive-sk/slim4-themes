<?php

declare(strict_types=1);

namespace Slim4\Themes\Interface;

use Psr\Http\Message\ResponseInterface;
use Slim4\Themes\Exception\TemplateNotFoundException;

/**
 * Interface for themes that can render to a response.
 */
interface ThemeResponseInterface
{
    /**
     * Render a template with the given data to a response.
     *
     * @param ResponseInterface $response The response to render to
     * @param string $template The template to render
     * @param array $data The data to pass to the template
     * @return ResponseInterface The rendered response
     * @throws TemplateNotFoundException If the template is not found
     */
    public function renderResponse(ResponseInterface $response, string $template, array $data = []): ResponseInterface;
}
