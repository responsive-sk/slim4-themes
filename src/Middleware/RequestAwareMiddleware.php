<?php

declare(strict_types=1);

namespace Slim4\Themes\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim4\Themes\Provider\ThemeProvider;

/**
 * Middleware that makes the request available to the ThemeProvider
 */
class RequestAwareMiddleware implements MiddlewareInterface
{
    /**
     * @var ThemeProvider The theme provider
     */
    private ThemeProvider $themeProvider;

    /**
     * @var string The cookie name
     */
    private string $cookieName;

    /**
     * @var string The query parameter name
     */
    private string $queryParam;

    /**
     * Constructor
     *
     * @param ThemeProvider $themeProvider The theme provider
     * @param string $cookieName The cookie name
     * @param string $queryParam The query parameter name
     */
    public function __construct(
        ThemeProvider $themeProvider,
        string $cookieName = 'theme',
        string $queryParam = 'theme'
    ) {
        $this->themeProvider = $themeProvider;
        $this->cookieName = $cookieName;
        $this->queryParam = $queryParam;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Set request in theme provider
        $this->themeProvider->setRequest($request);

        // Get theme
        $theme = $this->themeProvider->getTheme();

        // Add theme to request attributes
        $request = $request->withAttribute('theme', $theme);

        // Process request
        $response = $handler->handle($request);

        // Set theme cookie if theme is specified in query parameter
        $queryParams = $request->getQueryParams();
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
