# Implementation

This document describes the implementation details of the Slim4 Themes package.

## Interfaces

### ThemeInterface

The `ThemeInterface` defines the contract for themes:

```php
interface ThemeInterface
{
    public function getName(): string;
    public function getPath(): string;
    public function isDefault(): bool;
    public function getParentTheme(): ?string;
    public function getAssetsPath(): string;
    public function getTemplatesPath(): string;
    public function getConfig(): array;
}
```

### ThemeLoaderInterface

The `ThemeLoaderInterface` defines the contract for theme loaders:

```php
interface ThemeLoaderInterface
{
    public function load(string $themeName): ThemeInterface;
    public function getAvailableThemes(): array;
    public function getDefaultTheme(): ThemeInterface;
    public function themeExists(string $themeName): bool;
}
```

### ThemeRendererInterface

The `ThemeRendererInterface` defines the contract for theme renderers:

```php
interface ThemeRendererInterface
{
    public function setTheme(ThemeInterface $theme): void;
    public function getTheme(): ThemeInterface;
    public function render(string $template, array $data = []): string;
    public function templateExists(string $template): bool;
    public function getTemplatePath(string $template): string;
    public function addGlobal(string $name, $value): void;
}
```

### ThemeResponseInterface

The `ThemeResponseInterface` defines the contract for theme renderers that can render to a response:

```php
interface ThemeResponseInterface
{
    public function renderResponse(ResponseInterface $response, string $template, array $data = []): ResponseInterface;
}
```

## Implementations

### Twig

#### TwigTheme

The `TwigTheme` class implements the `ThemeInterface` for Twig:

```php
class TwigTheme implements ThemeInterface
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
    
    // Implementation of ThemeInterface methods
}
```

#### TwigThemeLoader

The `TwigThemeLoader` class implements the `ThemeLoaderInterface` for Twig:

```php
#[Singleton]
class TwigThemeLoader implements ThemeLoaderInterface
{
    private PathsInterface $paths;
    private array $themes = [];
    private ?string $defaultTheme = null;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
        $this->loadThemes();
    }
    
    // Implementation of ThemeLoaderInterface methods
    
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
            
            $theme = new TwigTheme(
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

#### TwigThemeRenderer

The `TwigThemeRenderer` class implements the `ThemeRendererInterface` and `ThemeResponseInterface` for Twig:

```php
class TwigThemeRenderer implements ThemeRendererInterface, ThemeResponseInterface
{
    private Environment $twig;
    private ThemeInterface $theme;
    private array $globals = [];
    private RouteParserInterface $routeParser;
    private bool $slimExtensionAdded = false;
    
    public function __construct(ThemeInterface $theme, RouteParserInterface $routeParser)
    {
        $this->theme = $theme;
        $this->routeParser = $routeParser;
        $this->initializeTwig();
    }
    
    // Implementation of ThemeRendererInterface and ThemeResponseInterface methods
    
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
```

#### SlimTwigExtension

The `SlimTwigExtension` class extends the Twig `AbstractExtension` class and provides Slim functions:

```php
class SlimTwigExtension extends AbstractExtension
{
    private RouteParserInterface $routeParser;
    
    public function __construct(RouteParserInterface $routeParser)
    {
        $this->routeParser = $routeParser;
    }
    
    public function getFunctions(): array
    {
        return [
            new TwigFunction('url_for', [$this, 'urlFor']),
            new TwigFunction('full_url_for', [$this, 'fullUrlFor']),
            new TwigFunction('is_current_url', [$this, 'isCurrentUrl']),
            new TwigFunction('current_url', [$this, 'currentUrl']),
        ];
    }
    
    // Implementation of Twig functions
}
```

### Latte

#### LatteTheme

The `LatteTheme` class implements the `ThemeInterface` for Latte:

```php
class LatteTheme implements ThemeInterface
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
    
    // Implementation of ThemeInterface methods
}
```

#### LatteThemeLoader

The `LatteThemeLoader` class implements the `ThemeLoaderInterface` for Latte:

```php
#[Singleton]
class LatteThemeLoader implements ThemeLoaderInterface
{
    private PathsInterface $paths;
    private array $themes = [];
    private ?string $defaultTheme = null;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
        $this->loadThemes();
    }
    
    // Implementation of ThemeLoaderInterface methods
    
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
            
            $theme = new LatteTheme(
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

#### LatteThemeRenderer

The `LatteThemeRenderer` class implements the `ThemeRendererInterface` and `ThemeResponseInterface` for Latte:

```php
class LatteThemeRenderer implements ThemeRendererInterface, ThemeResponseInterface
{
    private Engine $latte;
    private ThemeInterface $theme;
    private array $globals = [];
    private RouteParserInterface $routeParser;
    private bool $slimFunctionsAdded = false;
    
    public function __construct(ThemeInterface $theme, RouteParserInterface $routeParser)
    {
        $this->theme = $theme;
        $this->routeParser = $routeParser;
        $this->latte = new Engine();
        $this->initializeLatte();
    }
    
    // Implementation of ThemeRendererInterface and ThemeResponseInterface methods
    
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
            
            // Add more Slim functions
            
            $this->slimFunctionsAdded = true;
        }
    }
}
```

## Middleware

### ThemeMiddleware

The `ThemeMiddleware` class implements the PSR-15 `MiddlewareInterface`:

```php
class ThemeMiddleware implements MiddlewareInterface
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
        // Get theme from query parameter
        $queryParams = $request->getQueryParams();
        $themeName = $queryParams[$this->queryParam] ?? null;
        
        // Get theme from cookie if not in query parameter
        if ($themeName === null) {
            $cookies = $request->getCookieParams();
            $themeName = $cookies[$this->cookieName] ?? null;
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

## Resolver

### ThemeResolver

The `ThemeResolver` class provides a way to resolve themes:

```php
#[Singleton]
class ThemeResolver
{
    private ThemeLoaderInterface $themeLoader;
    private LoggerInterface $logger;
    private ?ThemeInterface $currentTheme = null;
    
    public function __construct(ThemeLoaderInterface $themeLoader, LoggerInterface $logger)
    {
        $this->themeLoader = $themeLoader;
        $this->logger = $logger;
    }
    
    public function resolveTemplate(string $template): string
    {
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
            
            throw new TemplateNotFoundException($template, $theme->getName());
        }
        
        return $templatePath;
    }
    
    public function getCurrentTheme(): ThemeInterface
    {
        if ($this->currentTheme === null) {
            $this->currentTheme = $this->themeLoader->getDefaultTheme();
        }
        
        return $this->currentTheme;
    }
    
    public function setCurrentTheme(string $themeName): void
    {
        try {
            $this->currentTheme = $this->themeLoader->load($themeName);
        } catch (TemplateNotFoundException $e) {
            $this->logger->warning(sprintf('Theme "%s" not found, using default theme', $themeName));
            $this->currentTheme = $this->themeLoader->getDefaultTheme();
        }
    }
    
    public function getAvailableThemes(): array
    {
        return $this->themeLoader->getAvailableThemes();
    }
}
```

## Attributes

### Singleton

The `Singleton` attribute marks a class as a singleton:

```php
#[Attribute(Attribute::TARGET_CLASS)]
class Singleton
{
}
```

## Exceptions

### TemplateNotFoundException

The `TemplateNotFoundException` is thrown when a template is not found:

```php
class TemplateNotFoundException extends RuntimeException
{
    public function __construct(string $template, string $themeName)
    {
        parent::__construct(sprintf('Template "%s" not found in theme "%s"', $template, $themeName));
    }
}
```

### ThemeNotFoundException

The `ThemeNotFoundException` is thrown when a theme is not found:

```php
class ThemeNotFoundException extends RuntimeException
{
    public function __construct(string $themeName)
    {
        parent::__construct(sprintf('Theme "%s" not found', $themeName));
    }
}
```
