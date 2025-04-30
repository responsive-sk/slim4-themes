<?php

declare(strict_types=1);

namespace Slim4\Themes\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim4\Themes\Exception\ThemeNotFoundException;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;
use Slim4\Themes\Middleware\ThemeMiddleware;

class ThemeMiddlewareTest extends TestCase
{
    public function testProcessWithDefaultTheme(): void
    {
        // Create mocks
        $themeLoader = $this->createMock(ThemeLoaderInterface::class);
        $themeRenderer = $this->createMock(ThemeRendererInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $theme = $this->createMock(ThemeInterface::class);
        
        // Configure mocks
        $request->method('getQueryParams')->willReturn([]);
        $request->method('getCookieParams')->willReturn([]);
        $request->method('withAttribute')->willReturn($request);
        $themeLoader->method('getDefaultTheme')->willReturn($theme);
        $handler->method('handle')->willReturn($response);
        
        // Create middleware
        $middleware = new ThemeMiddleware($themeLoader, $themeRenderer);
        
        // Process request
        $result = $middleware->process($request, $handler);
        
        // Assert result
        $this->assertSame($response, $result);
    }
    
    public function testProcessWithThemeFromQueryParam(): void
    {
        // Create mocks
        $themeLoader = $this->createMock(ThemeLoaderInterface::class);
        $themeRenderer = $this->createMock(ThemeRendererInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $theme = $this->createMock(ThemeInterface::class);
        
        // Configure mocks
        $request->method('getQueryParams')->willReturn(['theme' => 'test']);
        $request->method('withAttribute')->willReturn($request);
        $themeLoader->method('load')->with('test')->willReturn($theme);
        $handler->method('handle')->willReturn($response);
        $response->method('withHeader')->willReturn($response);
        
        // Create middleware
        $middleware = new ThemeMiddleware($themeLoader, $themeRenderer);
        
        // Process request
        $result = $middleware->process($request, $handler);
        
        // Assert result
        $this->assertSame($response, $result);
    }
    
    public function testProcessWithThemeFromCookie(): void
    {
        // Create mocks
        $themeLoader = $this->createMock(ThemeLoaderInterface::class);
        $themeRenderer = $this->createMock(ThemeRendererInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $theme = $this->createMock(ThemeInterface::class);
        
        // Configure mocks
        $request->method('getQueryParams')->willReturn([]);
        $request->method('getCookieParams')->willReturn(['theme' => 'test']);
        $request->method('withAttribute')->willReturn($request);
        $themeLoader->method('load')->with('test')->willReturn($theme);
        $handler->method('handle')->willReturn($response);
        
        // Create middleware
        $middleware = new ThemeMiddleware($themeLoader, $themeRenderer);
        
        // Process request
        $result = $middleware->process($request, $handler);
        
        // Assert result
        $this->assertSame($response, $result);
    }
    
    public function testProcessWithThemeNotFound(): void
    {
        // Create mocks
        $themeLoader = $this->createMock(ThemeLoaderInterface::class);
        $themeRenderer = $this->createMock(ThemeRendererInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $theme = $this->createMock(ThemeInterface::class);
        
        // Configure mocks
        $request->method('getQueryParams')->willReturn(['theme' => 'test']);
        $request->method('withAttribute')->willReturn($request);
        $themeLoader->method('load')->with('test')->willThrowException(new ThemeNotFoundException('test'));
        $themeLoader->method('getDefaultTheme')->willReturn($theme);
        $handler->method('handle')->willReturn($response);
        $response->method('withHeader')->willReturn($response);
        
        // Create middleware
        $middleware = new ThemeMiddleware($themeLoader, $themeRenderer);
        
        // Process request
        $result = $middleware->process($request, $handler);
        
        // Assert result
        $this->assertSame($response, $result);
    }
}
