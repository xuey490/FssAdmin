<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: Container.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Container;

use ArrayAccess; // Import ArrayAccess for facade compatibility
use Framework\Container\Compiler\AttributeInjectionPass;
use Framework\DI\AttributeInjector;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Dotenv\Dotenv;

/**
 * A wrapper class for Symfony's Dependency Injection Container with extended functionality
 */
class Container implements SymfonyContainerInterface, ArrayAccess
{
    private const CACHE_FILE = BASE_PATH . '/storage/cache/container.php';
    private static ?SymfonyContainerInterface $container = null;
    private static ?ContainerProviders $providers = null;

    public static function init(array $parameters = []): void
    {
        if (self::$container !== null) {
            return;
        }

        $envFile = BASE_PATH . '/.env';
        if (file_exists($envFile)) {
            (new Dotenv())->load($envFile);
        }

        $env    = env('APP_ENV') ?: 'local';
        $isProd = $env === 'prod';
        $projectRoot = BASE_PATH;
        $configDir   = $projectRoot . '/config';

        if (! is_dir($configDir)) {
            throw new RuntimeException("Configuration directory does not exist: {$configDir}");
        }
        $servicesFile = $configDir . '/services.php';
        if (! file_exists($servicesFile)) {
            throw new RuntimeException("Services configuration file does not exist: {$servicesFile}");
        }

        $providersManager = new ContainerProviders();
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.project_dir', $projectRoot);
        $containerBuilder->setParameter('kernel.debug', (bool) getenv('APP_DEBUG'));
        $containerBuilder->setParameter('kernel.environment', $env);

        if (! empty($parameters)) {
            $containerBuilder->setParameter('config', $parameters);
        }

        $containerBuilder->addCompilerPass(new AttributeInjectionPass());
        $loader = new PhpFileLoader($containerBuilder, new FileLocator($configDir));
        $loader->load('services.php');
        $containerBuilder->compile();
        $providersManager->bootProviders($containerBuilder);

        if ($isProd) {
            $cacheDir = dirname(self::CACHE_FILE);
            if (! is_dir($cacheDir) && ! mkdir($cacheDir, 0777, true) && ! is_dir($cacheDir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $cacheDir));
            }
            $dumper       = new PhpDumper($containerBuilder);
            $cacheContent = $dumper->dump(['class' => 'ProjectServiceContainer']);
            file_put_contents(self::CACHE_FILE, $cacheContent);
            $loadedContainer = require self::CACHE_FILE;
            self::$container = $loadedContainer instanceof SymfonyContainerInterface
                ? $loadedContainer
                : $containerBuilder;
        } else {
            self::$container = $containerBuilder;
        }

        if (isset(self::$providers)) {
            self::$providers->bootProviders(self::$container);
        }
    }

    private function getBuilder(): ContainerBuilder
    {
        if (self::$container instanceof ContainerBuilder) {
            return self::$container;
        }
        throw new RuntimeException('Current container is not an instance of ContainerBuilder (it might be compiled or cached).');
    }

    private static function getContainer(): SymfonyContainerInterface
    {
        if (self::$container === null) {
            self::init();
        }
        return self::$container;
    }

    public function make(string $abstract, array $parameters = []): object
    {
        if (empty($parameters) && self::$container->has($abstract)) {
            return self::$container->get($abstract);
        }

        try {
            $reflector = new ReflectionClass($abstract);
            if (! $reflector->isInstantiable()) {
                throw new RuntimeException("Class [{$abstract}] is not instantiable.");
            }

            $constructor = $reflector->getConstructor();
            $instance = null;

            if (is_null($constructor)) {
                $instance = new $abstract();
            } else {
                $dependencies = [];
                foreach ($constructor->getParameters() as $parameter) {
                    $name = $parameter->getName();
                    if (array_key_exists($name, $parameters)) {
                        $dependencies[] = $parameters[$name];
                        continue;
                    }

                    $type = $parameter->getType();
                    if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                        $dependencyClassName = $type->getName();
                        if (self::$container->has($dependencyClassName)) {
                            $dependencies[] = self::$container->get($dependencyClassName);
                            continue;
                        }
                        if (class_exists($dependencyClassName)) {
                            $dependencies[] = $this->make($dependencyClassName);
                            continue;
                        }
                    }

                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        throw new RuntimeException("Unable to resolve dependency [{$parameter->name}] in class {$abstract}");
                    }
                }
                $instance = $reflector->newInstanceArgs($dependencies);
            }
            
            AttributeInjector::inject($instance);
            return $instance;

        } catch (ReflectionException $e) {
            throw new RuntimeException('Container make failed: ' . $e->getMessage());
        }
    }

    public function singleton(string $id, callable $factory): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }
        if (! self::$container instanceof ContainerBuilder) {
            throw new RuntimeException('Cannot register service. Current container is not a modifiable ContainerBuilder instance.');
        }
        $containerBuilder = $this->getBuilder();
        if ($containerBuilder->isCompiled()) {
            throw new RuntimeException('Container has already been compiled, cannot register new services.');
        }
        $definition = new Definition();
        $definition->setFactory($factory);
        $definition->setShared(true);
        $containerBuilder->setDefinition($id, $definition);
    }

    public function bind(string $abstract, string $concrete, bool $shared = false): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }
        if (! self::$container instanceof ContainerBuilder) {
            throw new RuntimeException('Current container does not support dynamic service registration.');
        }
        $containerBuilder = $this->getBuilder();
        if ($containerBuilder->isCompiled()) {
            throw new RuntimeException('Container has already been compiled, cannot register new services.');
        }
        $definition = new Definition($concrete);
        $definition->setShared($shared);
        $containerBuilder->setDefinition($abstract, $definition);
    }

    public function factory(string $id, callable $factory, bool $shared = false): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }
        if (! self::$container instanceof ContainerBuilder) {
            throw new RuntimeException('Current container does not support dynamic service registration.');
        }
        $containerBuilder = self::$container;
        if ($containerBuilder->isCompiled()) {
            throw new RuntimeException('Container has already been compiled, cannot register new services.');
        }
        $definition = new Definition();
        $definition->setFactory($factory);
        $definition->setShared($shared);
        $containerBuilder->setDefinition($id, $definition);
    }

    public function instance(string $id, object $instance): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }
        self::$container->set($id, $instance);
    }

    public function parameter(string $name, mixed $value): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }
        self::$container->setParameter($name, $value);
    }

    public function tag(string $id, string $tag, array $attributes = []): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }
        if (! self::$container instanceof ContainerBuilder) {
            throw new RuntimeException('Current container does not support dynamic service registration.');
        }
        $containerBuilder = self::$container;
        if ($containerBuilder->isCompiled()) {
            throw new RuntimeException('Container has already been compiled, cannot register new services.');
        }
        $definition = $containerBuilder->getDefinition($id);
        $definition->addTag($tag, $attributes);
    }

    public function lazy(string $id, string $concrete, bool $shared = true): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }
        if (! self::$container instanceof ContainerBuilder) {
            throw new RuntimeException('Current container does not support dynamic service registration.');
        }
        $containerBuilder = self::$container;
        if ($containerBuilder->isCompiled()) {
            throw new RuntimeException('Container has already been compiled, cannot register new services.');
        }
        $definition = new Definition($concrete);
        $definition->setShared($shared);
        $definition->setLazy(true);
        $containerBuilder->setDefinition($id, $definition);
    }

    public static function setProviderManager(ContainerProviders $p): void
    {
        self::$providers = $p;
    }

    public static function getInstance(): self
    {
        if (self::$container === null) {
            self::init();
        }
        return new self();
    }

    public function get(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {
        return self::$container->get($id, intval($invalidBehavior));
    }

    public function has(string $id): bool
    {
        return self::$container->has($id);
    }

    public function set(string $id, ?object $service): void
    {
        self::$container->set($id, $service);
    }

    public function initialized(string $id): bool
    {
        return self::$container->initialized($id);
    }

    public function getServiceIds(): array
    {
        return self::$container->getServiceIds();
    }

    public function setParameter(string $name, mixed $value): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }
        self::$container->setParameter($name, $value);
    }

    public function hasParameter(string $name): bool
    {
        return self::$container->hasParameter($name);
    }

    public function getParameter(string $name): array|bool|float|int|string|\UnitEnum|null
    {
        return self::$container->getParameter($name);
    }

    public function getParameterBag(): ParameterBagInterface
    {
        return self::$container->getParameterBag();
    }

    public function compile(bool $resolveEnvPlaceholders = false): void
    {
        if (self::$container instanceof ContainerBuilder) {
            self::$container->compile($resolveEnvPlaceholders);
        }
    }

    public function isCompiled(): bool
    {
        return self::$container->isCompiled();
    }

    public function getCompilerPassConfig(): PassConfig
    {
        return self::$container->getCompilerPassConfig();
    }

    public function addCompilerPass(CompilerPassInterface $pass, string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION, int $priority = 0): static
    {
        self::$container->addCompilerPass($pass, $type, $priority);
        return $this;
    }

    // ========== ArrayAccess Implementation for Facade Compatibility ==========

    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string) $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_object($value)) {
            $this->set((string) $offset, $value);
        } else {
            // If value is not object, assume it's a class string for binding? 
            // Or just fail? Facade usually sets instance.
            // But if bind() is needed, Facade doesn't support bind via array access usually.
            // For now, only support setting instances or ignoring if null
             if ($value === null) {
                 // Do nothing or unset? Symfony container doesn't really support unset easily at runtime
                 return;
             }
             throw new RuntimeException("Container array access set only supports objects.");
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        // Symfony container doesn't support unsetting easily
        // We can set to null if needed, but 'set' requires ?object
        $this->set((string) $offset, null);
    }
}