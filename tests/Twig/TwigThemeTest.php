<?php

declare(strict_types=1);

namespace Slim4\Themes\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Slim4\Themes\Twig\TwigTheme;

class TwigThemeTest extends TestCase
{
    public function testGetName(): void
    {
        $theme = new TwigTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertSame('test', $theme->getName());
    }

    public function testGetPath(): void
    {
        $theme = new TwigTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertSame('/path/to/theme', $theme->getPath());
    }

    public function testIsDefault(): void
    {
        $theme = new TwigTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertTrue($theme->isDefault());

        $theme = new TwigTheme('test', '/path/to/theme', false, 'parent', ['key' => 'value']);
        $this->assertFalse($theme->isDefault());
    }

    public function testGetParentTheme(): void
    {
        $theme = new TwigTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertSame('parent', $theme->getParentTheme());

        $theme = new TwigTheme('test', '/path/to/theme', true, null, ['key' => 'value']);
        $this->assertNull($theme->getParentTheme());
    }

    public function testGetAssetsPath(): void
    {
        $theme = new TwigTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertSame('/path/to/theme/assets', $theme->getAssetsPath());
    }

    public function testGetTemplatesPath(): void
    {
        $theme = new TwigTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertSame('/path/to/theme', $theme->getTemplatesPath());
    }

    public function testGetConfig(): void
    {
        $theme = new TwigTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertSame(['key' => 'value'], $theme->getConfig());
    }
}
