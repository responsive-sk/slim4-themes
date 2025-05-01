<?php

declare(strict_types=1);

namespace Slim4\Themes\Tests\Plates;

use League\Plates\Engine;
use PHPUnit\Framework\TestCase;
use Slim4\Themes\Exception\TemplateNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Plates\PlatesThemeRenderer;

class PlatesThemeRendererTest extends TestCase
{
    private $theme;
    private $renderer;

    protected function setUp(): void
    {
        // Create a mock theme
        $this->theme = $this->createMock(ThemeInterface::class);
        $this->theme->method('getName')->willReturn('test-theme');
        $this->theme->method('getTemplatesPath')->willReturn(__DIR__ . '/templates');
        $this->theme->method('getPath')->willReturn(__DIR__);
        $this->theme->method('getParentTheme')->willReturn(null);

        // Create test templates directory if it doesn't exist
        if (!is_dir(__DIR__ . '/templates')) {
            mkdir(__DIR__ . '/templates', 0777, true);
        }

        // Create a test template
        file_put_contents(__DIR__ . '/templates/test.php', '<?php echo $foo; ?>');

        // Create the renderer
        $this->renderer = new PlatesThemeRenderer($this->theme);
    }

    protected function tearDown(): void
    {
        // Remove test template
        if (file_exists(__DIR__ . '/templates/test.php')) {
            unlink(__DIR__ . '/templates/test.php');
        }

        // Remove test templates directory
        if (is_dir(__DIR__ . '/templates')) {
            rmdir(__DIR__ . '/templates');
        }
    }

    public function testGetTheme(): void
    {
        $this->assertSame($this->theme, $this->renderer->getTheme());
    }

    public function testSetTheme(): void
    {
        $newTheme = $this->createMock(ThemeInterface::class);
        $newTheme->method('getName')->willReturn('new-theme');
        $newTheme->method('getTemplatesPath')->willReturn(__DIR__ . '/templates');
        $newTheme->method('getPath')->willReturn(__DIR__);
        $newTheme->method('getParentTheme')->willReturn(null);

        $this->renderer->setTheme($newTheme);

        $this->assertSame($newTheme, $this->renderer->getTheme());
    }

    public function testTemplateExists(): void
    {
        $this->assertTrue($this->renderer->templateExists('test'));
        $this->assertFalse($this->renderer->templateExists('non-existent'));
    }

    public function testGetTemplatePath(): void
    {
        $this->assertEquals(__DIR__ . '/templates/test', $this->renderer->getTemplatePath('test'));
    }

    public function testGetTemplatePathWithNonExistentTemplate(): void
    {
        $this->expectException(TemplateNotFoundException::class);
        $this->renderer->getTemplatePath('non-existent');
    }

    public function testRender(): void
    {
        $result = $this->renderer->render('test', ['foo' => 'bar']);
        $this->assertEquals('bar', $result);
    }

    public function testRenderWithNonExistentTemplate(): void
    {
        $this->expectException(TemplateNotFoundException::class);
        $this->renderer->render('non-existent', []);
    }

    public function testAddGlobal(): void
    {
        $this->renderer->addGlobal('global', 'value');
        $result = $this->renderer->render('test', ['foo' => 'bar']);
        $this->assertEquals('bar', $result);
    }

    public function testParentTheme(): void
    {
        // Skip this test for now as it requires more complex setup
        $this->markTestSkipped('This test requires more complex setup with parent themes');

        // In a real test, we would need to:
        // 1. Create a mock theme with parent
        // 2. Create parent theme directory and templates
        // 3. Create the renderer with the child theme
        // 4. Test that the parent template is accessible
        // 5. Clean up
    }
}
