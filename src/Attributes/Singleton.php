<?php

declare(strict_types=1);

namespace Slim4\Themes\Attributes;

use Attribute;

/**
 * Marks a class as a singleton.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Singleton
{
}
