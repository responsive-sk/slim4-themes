# Extending

This document describes how to extend the Slim4 Themes package.

## Creating a Custom Theme

You can create a custom theme by implementing the `ThemeInterface`:

```php
use Slim4\Themes\Interface\ThemeInterface;

class CustomTheme implements ThemeInterface
{
    private string $name;
    private string $path;
    private bool $isDefault;
    private ?string $parentTheme;
    private array $config;
    
    public function __construct(
        string $name,
        string $path,
        bool $isDefault = false,
        ?string $parentTheme = null,
        array $config = []
    ) {
        $this->name = $name;
        $this->path = $path;
        $this->isDefault = $isDefault;
        $this->parentTheme = $parentTheme;
        $this->config = $config;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getPath(): string
    {
        return $this->path;
    }
    
    public function isDefault(): bool
    {
        return $this->isDefault;
    }
    
    public function getParentTheme(): ?string
    {
        return $this->parentTheme;
    }
    
    public function getAssetsPath(): string
    {
        return $this->path . '/assets';
    }
    
    public function getTemplatesPath(): string
    {
        return $this->path . '/templates';
    }
    
    public function getConfig(): array
    {
        return $this->config;
    }
}
```

## Creating a Custom Theme Loader

You can create a custom theme loader by implementing the `ThemeLoaderInterface`:

```php
use Slim4\Root\PathsInterface;
use Slim4\Themes\Attributes\Singleton;
use Slim4\Themes\Exception\ThemeNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;

#[Singleton]
class CustomThemeLoader implements ThemeLoaderInterface
{
    private PathsInterface $paths;
    private array $themes = [];
    private ?string $defaultTheme = null;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
        $this->loadThemes();
    }
    
    public function load(string $themeName): ThemeInterface
    {
        if (!$this->themeExists($themeName)) {
            throw new ThemeNotFoundException($themeName);
        }
        
        return $this->themes[$themeName];
    }
    
    public function getAvailableThemes(): array
    {
        return array_values($this->themes);
    }
    
    public function getDefaultTheme(): ThemeInterface
    {
        if ($this->defaultTheme === null) {
            throw new ThemeNotFoundException('default');
        }
        
        return $this->themes[$this->defaultTheme];
    }
    
    public function themeExists(string $themeName): bool
    {
        return isset($this->themes[$themeName]);
    }
    
    private function loadThemes(): void
    {
        // Load themes from a custom source
        // For example, from a database
        
        // Example:
        $themesPath = $this->paths->getRootPath() . '/templates/themes';
        $themeDirectories = glob($themesPath . '/*', GLOB_ONLYDIR);
        
        foreach ($themeDirectories as $themeDirectory) {
            $themeName = basename($themeDirectory);
            $isDefault = file_exists($themeDirectory . '/.default');
            $parentTheme = null;
            $config = [];
            
            // Load theme configuration
            $configFile = $themeDirectory . '/theme.json';
            if (file_exists($configFile)) {
                $configData = json_decode(file_get_contents($configFile), true);
                $parentTheme = $configData['parent'] ?? null;
                $config = $configData;
            }
            
            $theme = new CustomTheme(
                $themeName,
                $themeDirectory,
                $isDefault,
                $parentTheme,
                $config
            );
            
            $this->themes[$themeName] = $theme;
            
            if ($isDefault) {
                $this->defaultTheme = $themeName;
            }
        }
    }
}
```

## Creating a Custom Theme Renderer

You can create a custom theme renderer by implementing the `ThemeRendererInterface` and `ThemeResponseInterface`:

```php
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim4\Themes\Exception\TemplateNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;
use Slim4\Themes\Interface\ThemeResponseInterface;

class CustomThemeRenderer implements ThemeRendererInterface, ThemeResponseInterface
{
    private ThemeInterface $theme;
    private array $globals = [];
    private RouteParserInterface $routeParser;
    
    public function __construct(ThemeInterface $theme, RouteParserInterface $routeParser)
    {
        $this->theme = $theme;
        $this->routeParser = $routeParser;
    }
    
    public function setTheme(ThemeInterface $theme): void
    {
        $this->theme = $theme;
    }
    
    public function getTheme(): ThemeInterface
    {
        return $this->theme;
    }
    
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
        
        // Render template using a custom renderer
        // For example, using a custom template engine
        
        // Example:
        $templatePath = $this->getTemplatePath($template);
        $content = file_get_contents($templatePath);
        
        // Replace placeholders with data
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $content = str_replace('{{' . $key . '}}', $value, $content);
            }
        }
        
        return $content;
    }
    
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
        
        // Render template using a custom renderer
        // For example, using a custom template engine
        
        // Example:
        $content = $this->render($template, $data);
        $response->getBody()->write($content);
        
        return $response;
    }
    
    public function templateExists(string $template): bool
    {
        return file_exists($this->getTemplatePath($template));
    }
    
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
    
    public function addGlobal(string $name, $value): void
    {
        $this->globals[$name] = $value;
    }
}
```

## Adding Support for a New Template Engine

To add support for a new template engine, you need to create the following classes:

1. A theme class that implements `ThemeInterface`
2. A theme loader class that implements `ThemeLoaderInterface`
3. A theme renderer class that implements `ThemeRendererInterface` and `ThemeResponseInterface`

### Example: Adding Support for Blade

#### BladeTheme

```php
use Slim4\Themes\Interface\ThemeInterface;

class BladeTheme implements ThemeInterface
{
    private string $name;
    private string $path;
    private bool $isDefault;
    private ?string $parentTheme;
    private array $config;
    
    public function __construct(
        string $name,
        string $path,
        bool $isDefault = false,
        ?string $parentTheme = null,
        array $config = []
    ) {
        $this->name = $name;
        $this->path = $path;
        $this->isDefault = $isDefault;
        $this->parentTheme = $parentTheme;
        $this->config = $config;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getPath(): string
    {
        return $this->path;
    }
    
    public function isDefault(): bool
    {
        return $this->isDefault;
    }
    
    public function getParentTheme(): ?string
    {
        return $this->parentTheme;
    }
    
    public function getAssetsPath(): string
    {
        return $this->path . '/assets';
    }
    
    public function getTemplatesPath(): string
    {
        return $this->path . '/templates';
    }
    
    public function getConfig(): array
    {
        return $this->config;
    }
}
```

#### BladeThemeLoader

```php
use Slim4\Root\PathsInterface;
use Slim4\Themes\Attributes\Singleton;
use Slim4\Themes\Exception\ThemeNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;

#[Singleton]
class BladeThemeLoader implements ThemeLoaderInterface
{
    private PathsInterface $paths;
    private array $themes = [];
    private ?string $defaultTheme = null;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
        $this->loadThemes();
    }
    
    public function load(string $themeName): ThemeInterface
    {
        if (!$this->themeExists($themeName)) {
            throw new ThemeNotFoundException($themeName);
        }
        
        return $this->themes[$themeName];
    }
    
    public function getAvailableThemes(): array
    {
        return array_values($this->themes);
    }
    
    public function getDefaultTheme(): ThemeInterface
    {
        if ($this->defaultTheme === null) {
            throw new ThemeNotFoundException('default');
        }
        
        return $this->themes[$this->defaultTheme];
    }
    
    public function themeExists(string $themeName): bool
    {
        return isset($this->themes[$themeName]);
    }
    
    private function loadThemes(): void
    {
        $themesPath = $this->paths->getRootPath() . '/templates/themes';
        $themeDirectories = glob($themesPath . '/*', GLOB_ONLYDIR);
        
        foreach ($themeDirectories as $themeDirectory) {
            $themeName = basename($themeDirectory);
            $isDefault = file_exists($themeDirectory . '/.default');
            $parentTheme = null;
            $config = [];
            
            // Load theme configuration
            $configFile = $themeDirectory . '/theme.json';
            if (file_exists($configFile)) {
                $configData = json_decode(file_get_contents($configFile), true);
                $parentTheme = $configData['parent'] ?? null;
                $config = $configData;
            }
            
            $theme = new BladeTheme(
                $themeName,
                $themeDirectory,
                $isDefault,
                $parentTheme,
                $config
            );
            
            $this->themes[$themeName] = $theme;
            
            if ($isDefault) {
                $this->defaultTheme = $themeName;
            }
        }
    }
}
```

#### BladeThemeRenderer

```php
use Illuminate\View\Factory;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Uri;
use Slim4\Themes\Exception\TemplateNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;
use Slim4\Themes\Interface\ThemeResponseInterface;

class BladeThemeRenderer implements ThemeRendererInterface, ThemeResponseInterface
{
    private Factory $blade;
    private ThemeInterface $theme;
    private array $globals = [];
    private RouteParserInterface $routeParser;
    private bool $slimFunctionsAdded = false;
    
    public function __construct(ThemeInterface $theme, RouteParserInterface $routeParser, Factory $blade)
    {
        $this->theme = $theme;
        $this->routeParser = $routeParser;
        $this->blade = $blade;
        $this->initializeBlade();
    }
    
    public function setTheme(ThemeInterface $theme): void
    {
        $this->theme = $theme;
        $this->initializeBlade();
    }
    
    public function getTheme(): ThemeInterface
    {
        return $this->theme;
    }
    
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
        return $this->blade->make($template, $data)->render();
    }
    
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
        $content = $this->blade->make($template, $data)->render();
        $response->getBody()->write($content);
        
        return $response;
    }
    
    public function templateExists(string $template): bool
    {
        return $this->blade->exists($template);
    }
    
    public function getTemplatePath(string $template): string
    {
        if (!$this->templateExists($template)) {
            throw new TemplateNotFoundException($template, $this->theme->getName());
        }
        
        return $this->theme->getTemplatesPath() . '/' . $template . '.blade.php';
    }
    
    public function addGlobal(string $name, $value): void
    {
        $this->globals[$name] = $value;
        $this->blade->share($name, $value);
    }
    
    private function initializeBlade(): void
    {
        // Set template paths
        $this->blade->addNamespace('theme', $this->theme->getTemplatesPath());
        
        // Add Slim functions if not already added
        if (!$this->slimFunctionsAdded) {
            // Add Slim functions
            $this->blade->directive('url_for', function ($expression) {
                return "<?php echo \$this->routeParser->urlFor($expression); ?>";
            });
            
            $this->blade->directive('full_url_for', function ($expression) {
                return "<?php
                    \$uri = new \\Slim\\Psr7\\Uri(
                        'http',
                        isset(\$_SERVER['HTTP_HOST']) ? \$_SERVER['HTTP_HOST'] : 'localhost'
                    );
                    echo \$this->routeParser->fullUrlFor(\$uri, $expression);
                ?>";
            });
            
            $this->blade->directive('is_current_url', function ($expression) {
                return "<?php
                    \$currentUrl = isset(\$_SERVER['REQUEST_URI']) ? \$_SERVER['REQUEST_URI'] : '/';
                    \$url = \$this->routeParser->urlFor($expression);
                    echo \$currentUrl === \$url ? 'true' : 'false';
                ?>";
            });
            
            $this->blade->directive('current_url', function () {
                return "<?php echo isset(\$_SERVER['REQUEST_URI']) ? \$_SERVER['REQUEST_URI'] : '/'; ?>";
            });
            
            $this->slimFunctionsAdded = true;
        }
        
        // Add globals
        foreach ($this->globals as $name => $value) {
            $this->blade->share($name, $value);
        }
    }
}
```

## Creating a Custom Middleware

You can create a custom middleware by implementing the PSR-15 `MiddlewareInterface`:

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim4\Themes\Exception\ThemeNotFoundException;
use Slim4\Themes\Interface\ThemeLoaderInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;

class CustomThemeMiddleware implements MiddlewareInterface
{
    private ThemeLoaderInterface $themeLoader;
    private ThemeRendererInterface $themeRenderer;
    private string $cookieName;
    private string $queryParam;
    
    public function __construct(
        ThemeLoaderInterface $themeLoader,
        ThemeRendererInterface $themeRenderer,
        string $cookieName = 'theme',
        string $queryParam = 'theme'
    ) {
        $this->themeLoader = $themeLoader;
        $this->themeRenderer = $themeRenderer;
        $this->cookieName = $cookieName;
        $this->queryParam = $queryParam;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Custom theme resolution logic
        
        // Example:
        // Get theme from query parameter
        $queryParams = $request->getQueryParams();
        $themeName = $queryParams[$this->queryParam] ?? null;
        
        // Get theme from cookie if not in query parameter
        if ($themeName === null) {
            $cookies = $request->getCookieParams();
            $themeName = $cookies[$this->cookieName] ?? null;
        }
        
        // Get theme from user preferences if not in query parameter or cookie
        if ($themeName === null) {
            $user = $request->getAttribute('user');
            if ($user !== null && isset($user['theme'])) {
                $themeName = $user['theme'];
            }
        }
        
        // Get default theme if no theme is specified
        if ($themeName === null) {
            $theme = $this->themeLoader->getDefaultTheme();
        } else {
            // Try to load theme
            try {
                $theme = $this->themeLoader->load($themeName);
            } catch (ThemeNotFoundException $e) {
                // Use default theme if theme not found
                $theme = $this->themeLoader->getDefaultTheme();
            }
        }
        
        // Set theme in renderer
        $this->themeRenderer->setTheme($theme);
        
        // Add theme to request attributes
        $request = $request->withAttribute('theme', $theme);
        
        // Process request
        $response = $handler->handle($request);
        
        // Set theme cookie if theme is specified in query parameter
        if (isset($queryParams[$this->queryParam])) {
            $response = $response->withHeader(
                'Set-Cookie',
                sprintf(
                    '%s=%s; Path=/; HttpOnly; SameSite=Lax',
                    $this->cookieName,
                    $queryParams[$this->queryParam]
                )
            );
        }
        
        return $response;
    }
}
```

## Creating a Custom Resolver

You can create a custom resolver by extending the `ThemeResolver` class:

```php
use Psr\Log\LoggerInterface;
use Slim4\Themes\Attributes\Singleton;
use Slim4\Themes\Exception\TemplateNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;
use Slim4\Themes\Resolver\ThemeResolver;

#[Singleton]
class CustomThemeResolver extends ThemeResolver
{
    private ThemeLoaderInterface $themeLoader;
    private LoggerInterface $logger;
    private ?ThemeInterface $currentTheme = null;
    
    public function __construct(ThemeLoaderInterface $themeLoader, LoggerInterface $logger)
    {
        parent::__construct($themeLoader, $logger);
        $this->themeLoader = $themeLoader;
        $this->logger = $logger;
    }
    
    public function resolveTemplate(string $template): string
    {
        // Custom template resolution logic
        
        // Example:
        $theme = $this->getCurrentTheme();
        $templatePath = $theme->getTemplatesPath() . '/' . $template;
        
        if (!file_exists($templatePath)) {
            // Try parent theme if exists
            if ($theme->getParentTheme() !== null) {
                $parentThemePath = dirname($theme->getPath()) . '/' . $theme->getParentTheme();
                $parentTemplatePath = $parentThemePath . '/templates/' . $template;
                
                if (file_exists($parentTemplatePath)) {
                    return $parentTemplatePath;
                }
            }
            
            // Try default theme if not found in parent theme
            if (!$theme->isDefault()) {
                $defaultTheme = $this->themeLoader->getDefaultTheme();
                $defaultTemplatePath = $defaultTheme->getTemplatesPath() . '/' . $template;
                
                if (file_exists($defaultTemplatePath)) {
                    return $defaultTemplatePath;
                }
            }
            
            throw new TemplateNotFoundException($template, $theme->getName());
        }
        
        return $templatePath;
    }
}
```
