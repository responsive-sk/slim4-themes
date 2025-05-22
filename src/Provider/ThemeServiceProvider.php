<?php

declare(strict_types=1);

namespace Slim4\Themes\Provider;

use Psr\Container\ContainerInterface;
use Slim4\Themes\Interface\ThemeInterface;
use Slim4\Themes\Interface\ThemeLoaderInterface;
use Slim4\Themes\Interface\ThemeRendererInterface;
use Slim4\Themes\Middleware\RequestAwareMiddleware;

/**
 * Service provider for theme services
 */
class ThemeServiceProvider
{
    /**
     * Register theme services in the container
     *
     * @param ContainerInterface $container The container
     * @param array<string, mixed> $config The configuration
     * @return void
     */
    public function register(ContainerInterface $container, array $config = []): void
    {
        // Get configuration
        $cookieName = $config['cookie_name'] ?? 'theme';
        $queryParam = $config['query_param'] ?? 'theme';

        // Register ThemeResolver
        if (!$this->has($container, ThemeResolver::class)) {
            $this->set($container, ThemeResolver::class, function (ContainerInterface $container) use ($cookieName, $queryParam) {
                return new ThemeResolver(
                    $container->get(ThemeLoaderInterface::class),
                    $cookieName,
                    $queryParam
                );
            });
        }

        // Register ThemeProvider
        if (!$this->has($container, ThemeProvider::class)) {
            $this->set($container, ThemeProvider::class, function (ContainerInterface $container) {
                return new ThemeProvider($container);
            });
        }

        // Register ThemeInterface
        if (!$this->has($container, ThemeInterface::class)) {
            $this->set($container, ThemeInterface::class, function (ContainerInterface $container) {
                return $container->get(ThemeProvider::class);
            });
        }

        // Register RequestAwareMiddleware
        if (!$this->has($container, RequestAwareMiddleware::class)) {
            $this->set($container, RequestAwareMiddleware::class, function (ContainerInterface $container) use ($cookieName, $queryParam) {
                return new RequestAwareMiddleware(
                    $container->get(ThemeProvider::class),
                    $cookieName,
                    $queryParam
                );
            });
        }
    }

    /**
     * Check if a service is registered in the container
     *
     * @param ContainerInterface $container The container
     * @param string $id The service ID
     * @return bool True if the service is registered
     */
    private function has(ContainerInterface $container, string $id): bool
    {
        // Check if the container has a has() method
        if (method_exists($container, 'has')) {
            return $container->has($id);
        }

        // Try to get the service
        try {
            $container->get($id);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Set a service in the container
     *
     * @param ContainerInterface $container The container
     * @param string $id The service ID
     * @param callable $factory The service factory
     * @return void
     */
    private function set(ContainerInterface $container, string $id, callable $factory): void
    {
        // Check if the container has a set() method
        if (method_exists($container, 'set')) {
            $container->set($id, $factory);
            return;
        }

        // Check if the container has a factory() method (Symfony)
        if (method_exists($container, 'factory')) {
            $container->factory($id, $factory);
            return;
        }

        // Check if the container has a define() method (PHP-DI)
        if (method_exists($container, 'define')) {
            $container->define($id, $factory);
            return;
        }

        // Throw an exception if the container doesn't support setting services
        throw new \RuntimeException(sprintf('Container of class %s does not support setting services', get_class($container)));
    }
}
