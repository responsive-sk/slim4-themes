<?php

declare(strict_types=1);

namespace Slim4\Themes\Exception;

use RuntimeException;

/**
 * Exception thrown when a template is not found.
 */
class TemplateNotFoundException extends RuntimeException
{
    /**
     * Constructor.
     *
     * @param string $template The template name
     * @param string $themeName The theme name
     */
    public function __construct(string $template, string $themeName)
    {
        parent::__construct(sprintf('Template "%s" not found in theme "%s"', $template, $themeName));
    }
}
