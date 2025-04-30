<?php

declare(strict_types=1);

namespace Slim4\Themes\Twig;

use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim4\Themes\Exception\TemplateNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;
use Slim4\Themes\Interface\ThemeResponseInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

/**
 * Twig implementation of ThemeRendererInterface.
 */
class TwigThemeRenderer implements ThemeRendererInterface, ThemeResponseInterface
{
    /**
     * @var Environment The Twig environment
     */
    private Environment $twig;
    
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
     * @var bool Whether the Slim extension has been added
     */
    private bool $slimExtensionAdded = false;
    
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
        $this->initializeTwig();
    }
    
    /**
     * {@inheritdoc}
     */
    public function setTheme(ThemeInterface $theme): void
    {
        $this->theme = $theme;
        $this->initializeTwig();
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
        
        // Render template
        return $this->twig->render($template, $data);
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
        
        // Render template
        $content = $this->twig->render($template, $data);
        $response->getBody()->write($content);
        
        return $response;
    }
    
    /**
     * {@inheritdoc}
     */
    public function templateExists(string $template): bool
    {
        try {
            $this->twig->getLoader()->getSourceContext($template);
            return true;
        } catch (LoaderError $e) {
            return false;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTemplatePath(string $template): string
    {
        if (!$this->templateExists($template)) {
            throw new TemplateNotFoundException($template, $this->theme->getName());
        }
        
        return $this->theme->getTemplatesPath() . '/' . $template;
    }
    
    /**
     * {@inheritdoc}
     */
    public function addGlobal(string $name, $value): void
    {
        $this->globals[$name] = $value;
        $this->twig->addGlobal($name, $value);
    }
    
    /**
     * Initialize Twig.
     *
     * @return void
     */
    private function initializeTwig(): void
    {
        // Create Twig loader
        $loader = new FilesystemLoader([$this->theme->getTemplatesPath()]);
        
        // Create Twig environment
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true,
            'auto_reload' => true,
        ]);
        
        // Add Slim extension if not already added
        if (!$this->slimExtensionAdded) {
            $this->twig->addExtension(new SlimTwigExtension($this->routeParser));
            $this->slimExtensionAdded = true;
        }
        
        // Add globals
        foreach ($this->globals as $name => $value) {
            $this->twig->addGlobal($name, $value);
        }
    }
}
