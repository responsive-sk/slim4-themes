<?php

declare(strict_types=1);

namespace Slim4\Themes\Tests\Plates;

use PHPUnit\Framework\TestCase;
use Slim4\Themes\Plates\PlatesTheme;

class PlatesThemeTest extends TestCase
{
    public function testGetName(): void
    {
        $theme = new PlatesTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertSame('test', $theme->getName());
    }
    
    public function testGetPath(): void
    {
        $theme = new PlatesTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertSame('/path/to/theme', $theme->getPath());
    }
    
    public function testIsDefault(): void
    {
        $theme = new PlatesTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertTrue($theme->isDefault());
        
        $theme = new PlatesTheme('test', '/path/to/theme', false, 'parent', ['key' => 'value']);
        $this->assertFalse($theme->isDefault());
    }
    
    public function testGetParentTheme(): void
    {
        $theme = new PlatesTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertSame('parent', $theme->getParentTheme());
        
        $theme = new PlatesTheme('test', '/path/to/theme', true, null, ['key' => 'value']);
        $this->assertNull($theme->getParentTheme());
    }
    
    public function testGetAssetsPath(): void
    {
        $theme = new PlatesTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertSame('/path/to/theme/assets', $theme->getAssetsPath());
    }
    
    public function testGetTemplatesPath(): void
    {
        $theme = new PlatesTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertSame('/path/to/theme/templates', $theme->getTemplatesPath());
    }
    
    public function testGetConfig(): void
    {
        $theme = new PlatesTheme('test', '/path/to/theme', true, 'parent', ['key' => 'value']);
        $this->assertSame(['key' => 'value'], $theme->getConfig());
    }
}
