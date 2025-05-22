<?php

declare(strict_types=1);

namespace Slim4\Themes\Exception;

use Exception;

/**
 * Exception thrown when a theme could not be found.
 */
class ThemeNotFoundException extends Exception
{
    /**
     * @param string $themeName The name of the theme that could not be found
     * @param int $code The exception code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(string $themeName, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Theme "%s" could not be found', $themeName), $code, $previous);
    }
}
