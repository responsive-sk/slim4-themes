<?php

declare(strict_types=1);

namespace Slim4\Themes\Latte;

use Latte\Engine;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Uri;
use Slim4\Themes\Exception\TemplateNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;
use Slim4\Themes\Interface\ThemeResponseInterface;

/**
 * Latte implementation of ThemeRendererInterface.
 */
class LatteThemeRenderer implements ThemeRendererInterface, ThemeResponseInterface
{
    /**
     * @var Engine The Latte engine
     */
    private Engine $latte;
    
    /**
     * @var ThemeInterface The current theme
     */
    private ThemeInterface $theme;
    
    /**
     * @var array The global variables
     */
    private array $globals = [];
    
    /**
     * @var RouteParserInterface The route parser
     */
    private RouteParserInterface $routeParser;
    
    /**
     * @var bool Whether the Slim functions have been added
     */
    private bool $slimFunctionsAdded = false;
    
    /**
     * Constructor.
     *
     * @param ThemeInterface $theme The theme
     * @param RouteParserInterface $routeParser The route parser
     */
    public function __construct(ThemeInterface $theme, RouteParserInterface $routeParser)
    {
        $this->theme = $theme;
        $this->routeParser = $routeParser;
        $this->latte = new Engine();
        $this->initializeLatte();
    }
    
    /**
     * {@inheritdoc}
     */
    public function setTheme(ThemeInterface $theme): void
    {
        $this->theme = $theme;
        $this->initializeLatte();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTheme(): ThemeInterface
    {
        return $this->theme;
    }
    
    /**
     * {@inheritdoc}
     */
    public function render(string $template, array $data = []): string
    {
        if (!$this->templateExists($template)) {
            throw new TemplateNotFoundException($template, $this->theme->getName());
        }
        
        // Add theme to data
        $data['theme'] = $this->theme;
        
        // Add globals to data
        foreach ($this->globals as $name => $value) {
            $data[$name] = $value;
        }
        
        // Render template
        return $this->latte->renderToString($this->getTemplatePath($template), $data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function renderResponse(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        if (!$this->templateExists($template)) {
            throw new TemplateNotFoundException($template, $this->theme->getName());
        }
        
        // Add theme to data
        $data['theme'] = $this->theme;
        
        // Add globals to data
        foreach ($this->globals as $name => $value) {
            $data[$name] = $value;
        }
        
        // Render template
        $content = $this->latte->renderToString($this->getTemplatePath($template), $data);
        $response->getBody()->write($content);
        
        return $response;
    }
    
    /**
     * {@inheritdoc}
     */
    public function templateExists(string $template): bool
    {
        return file_exists($this->getTemplatePath($template));
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTemplatePath(string $template): string
    {
        $templatePath = $this->theme->getTemplatesPath() . '/' . $template;
        
        if (!file_exists($templatePath)) {
            // Try parent theme if exists
            if ($this->theme->getParentTheme() !== null) {
                $parentThemePath = dirname($this->theme->getPath()) . '/' . $this->theme->getParentTheme();
                $parentTemplatePath = $parentThemePath . '/templates/' . $template;
                
                if (file_exists($parentTemplatePath)) {
                    return $parentTemplatePath;
                }
            }
            
            throw new TemplateNotFoundException($template, $this->theme->getName());
        }
        
        return $templatePath;
    }
    
    /**
     * {@inheritdoc}
     */
    public function addGlobal(string $name, $value): void
    {
        $this->globals[$name] = $value;
    }
    
    /**
     * Initialize Latte.
     *
     * @return void
     */
    private function initializeLatte(): void
    {
        // Set template paths
        $this->latte->setTempDirectory(sys_get_temp_dir() . '/latte');
        
        // In Latte 3.0, we need to use the parameters array when rendering
        // We'll pass the globals when rendering
        
        // Add Slim functions if not already added
        if (!$this->slimFunctionsAdded) {
            // Add Slim functions
            $this->latte->addFunction('url_for', function (string $routeName, array $data = [], array $queryParams = []) {
                return $this->routeParser->urlFor($routeName, $data, $queryParams);
            });
            
            $this->latte->addFunction('full_url_for', function (string $routeName, array $data = [], array $queryParams = []) {
                $uri = new Uri(
                    'http',
                    isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'
                );
                return $this->routeParser->fullUrlFor(
                    $uri,
                    $routeName,
                    $data,
                    $queryParams
                );
            });
            
            $this->latte->addFunction('is_current_url', function (string $routeName) {
                $currentUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
                $url = $this->routeParser->urlFor($routeName);
                
                return $currentUrl === $url;
            });
            
            $this->latte->addFunction('current_url', function () {
                return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
            });
            
            $this->slimFunctionsAdded = true;
        }
    }
}
