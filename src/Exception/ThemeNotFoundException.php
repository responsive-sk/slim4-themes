<?php

declare(strict_types=1);

namespace Slim4\Themes\Exception;

use RuntimeException;

/**
 * Exception thrown when a theme is not found.
 */
class ThemeNotFoundException extends RuntimeException
{
    /**
     * Constructor.
     *
     * @param string $themeName The theme name
     */
    public function __construct(string $themeName)
    {
        parent::__construct(sprintf('Theme "%s" not found', $themeName));
    }
}
