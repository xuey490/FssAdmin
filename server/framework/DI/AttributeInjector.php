<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * This class implements a dependency injection mechanism based on PHP 8 attributes,
 * which automatically injects dependencies into class properties marked with #[Inject], #[Autowire] or #[Context] attributes.
 * It uses PHP Reflection API to inspect class properties and their attributes,
 * then resolves and injects the required dependencies from the DI container or context bag.
 *
 * @Filename: AttributeInjector.php
 * @Purpose: Core implementation of attribute-based dependency injection
 * @Author: FssPHP Framework Team
 * @Version: 1.0
 */

namespace Framework\DI;

use Framework\Core\App;
use Framework\DI\Attribute\Autowire;
use Framework\DI\Attribute\Inject;
use Framework\DI\Attribute\Context;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;

/**
 * Attribute-based Dependency Injector
 * 
 * This class provides the core functionality for injecting dependencies into object properties
 * by parsing PHP 8 attributes. It supports three types of injection:
 * 1. #[Context]: Injects values from the application context bag (e.g., request context)
 * 2. #[Inject]: Injects services from the DI container by ID or type
 * 3. #[Autowire]: Automatically resolves and injects dependencies by type
 * 
 * Key features:
 * - Reflection metadata caching to avoid repeated reflection overhead
 * - Support for private/protected property injection
 * - Skip injection if property is already initialized
 * - Comprehensive error handling for missing dependencies
 */
class AttributeInjector
{
    /**
     * Reflection metadata cache to avoid repeated reflection of the same class
     * 
     * Cache structure:
     * [
     *     ClassName::class => [
     *         [
     *             'reflection' => ReflectionProperty instance,
     *             'property'   => string (property name),
     *             'attr'       => Attribute instance (Inject/Autowire/Context),
     *             'type'       => string|null (property type hint)
     *         ],
     *         ...
     *     ],
     *     ...
     * ]
     *
     * @var array<string, array<array{reflection: ReflectionProperty, property: string, attr: object, type: string|null}>>
     */
    protected static array $metadataCache = [];

    /**
     * Inject dependencies into an already instantiated object's properties
     * 
     * This method is the entry point for attribute-based injection. It:
     * 1. Retrieves or parses reflection metadata for the object's class
     * 2. Iterates through all injectable properties
     * 3. Skips properties that are already initialized with non-null values
     * 4. Resolves the required dependency value
     * 5. Sets the resolved value to the target property
     *
     * @param object $instance The target object instance to inject dependencies into
     * @throws RuntimeException If reflection fails or dependency resolution fails
     */
    public static function inject(object $instance): void
    {
        $className = get_class($instance);
		
		// 增加类名验证
		if (!class_exists($className)) {
			throw new RuntimeException("Class {$className} does not exist");
		}
		
        // 1. Get injection metadata (parse first if not in cache)
        // Using cache to optimize performance for multiple instances of the same class
        if (!isset(self::$metadataCache[$className])) {
            self::$metadataCache[$className] = self::parseMetadata($className);
        }

        // 2. Iterate through metadata and perform injection for each property
        foreach (self::$metadataCache[$className] as $meta) {
            /** @var ReflectionProperty $reflectionProperty The reflection object of the target property */
            $reflectionProperty = $meta['reflection'];
            $propertyName = $meta['property']; // Name of the target property
			
            // Skip injection if property is already initialized with a non-null value
            // This prevents overwriting manually assigned values (e.g., from constructor)
            // Note: isInitialized() requires PHP 7.4 or higher
            if ($reflectionProperty->isInitialized($instance) && $reflectionProperty->getValue($instance) !== null) {
                continue;
            }

            // Resolve the dependency value based on attribute type and property metadata
            $value = self::resolveDependency($meta['attr'], $meta['type']);

            // Inject the resolved value into the property if it's not null
            if ($value !== null) {
                $reflectionProperty->setValue($instance, $value);
            }
        }
    }

    /**
     * Parse reflection metadata for a class's injectable properties
     * 
     * This method uses PHP Reflection API to:
     * 1. Inspect all properties (public/protected/private) of the target class
     * 2. Check for injection-related attributes on each property
     * 3. Collect metadata for properties marked with #[Inject], #[Autowire] or #[Context]
     * 4. Make private/protected properties accessible for injection
     *
     * @param string $className Fully qualified class name to parse
     * @return array Array of injection metadata for the class's properties
     * @throws RuntimeException If reflection fails (e.g., class does not exist)
     */
    protected static function parseMetadata(string $className): array
    {
        $metaList = [];
        try {
            // Create reflection object for the target class
            $reflection = new ReflectionClass($className);

            // Get all properties (including private, protected, public)
            // Note: This implementation handles only properties visible to the current class
            // To handle inherited private properties, additional logic for parent classes is required
            foreach ($reflection->getProperties() as $property) {
                // Get all attributes applied to the current property
                $attributes = $property->getAttributes();
                
                foreach ($attributes as $attribute) {
                    // Create an instance of the attribute class to inspect its properties
                    $inst = $attribute->newInstance();

                    // Only process injection-related attributes
                    if ($inst instanceof Inject || $inst instanceof Autowire || $inst instanceof Context) {
                        // Make private/protected properties accessible for injection
                        $property->setAccessible(true);

                        // Collect metadata for later injection
                        $metaList[] = [
                            'reflection' => $property,          // ReflectionProperty instance
                            'property'   => $property->getName(), // Property name as string
                            'attr'       => $inst,              // Attribute instance (Inject/Autowire/Context)
                            'type'       => $property->getType()?->getName(), // Property type hint (nullable)
                        ];
                        // Only process the first injection attribute per property
                        // This prevents conflicts if multiple injection attributes are applied
                        break;
                    }
                }
            }
        } catch (ReflectionException $e) {
            // Reflection exceptions typically occur when the class name is invalid/non-existent
            throw new RuntimeException("Reflection failed for class {$className}: " . $e->getMessage(), 0, $e);
        }

        return $metaList;
    }

    /**
     * Resolve dependency value based on attribute type and property metadata
     * 
     * This method handles three types of dependency resolution:
     * 1. Context injection: Retrieves values from the application context bag
     * 2. Inject attribute: Retrieves services from DI container by ID or type
     * 3. Autowire attribute: Automatically resolves and instantiates dependencies by type
     *
     * @param object $attr The attribute instance (Inject/Autowire/Context)
     * @param string|null $type The property's type hint (from reflection)
     * @return mixed The resolved dependency value
     * @throws RuntimeException If dependency resolution fails (e.g., missing context key, unresolvable type)
     */
    protected static function resolveDependency(object $attr, ?string $type): mixed
    {
        // 1. Handle #[Context] attribute - inject values from application context
        // Context injection is typically used for request-scoped values (e.g., current user, request data)
        if ($attr instanceof Context) {
            // Throw exception instead of returning null for missing context keys
            // This makes errors more visible and helps with debugging
            if (!ContextBag::has($attr->key)) {
                throw new RuntimeException(sprintf(
                    "Context Injection Failed: Key '%s' not found in ContextBag. Did you register ContextInitMiddleware?", 
                    $attr->key
                ));
            }
            // Retrieve and return the value from the context bag
            return ContextBag::get($attr->key);
        }	
		
        // 2. Handle #[Inject] attribute - inject services from DI container
        // Supports both explicit ID injection and type-based injection
        if ($attr instanceof Inject) {
            // Use explicit ID if provided, otherwise fall back to property type hint
            $serviceId = $attr->id ?? $type;
            
            // Reject injection if neither ID nor type is available
            if (!$serviceId) {
                throw new RuntimeException("Cannot inject property without type hint or explicit ID in #[Inject] attribute.");
            }

            // First try to get the service from the DI container by ID
            if (App::has($serviceId)) {
                return App::get($serviceId);
            }
            
            // Fallback strategy: If no explicit ID was provided (using type as ID)
            // and the service doesn't exist in container, try to auto-instantiate the class
            if ($attr->id === null && class_exists($serviceId)) {
                return App::make($serviceId);
            }

            // Return null if service cannot be resolved (alternative: throw exception for stricter injection)
            return null;
        }

        // 3. Handle #[Autowire] attribute - automatic dependency resolution by type
        // Autowire requires explicit type hint and will always try to instantiate the class if not in container
        if ($attr instanceof Autowire) {
            // Autowire cannot work without a type hint (unlike Inject which supports explicit ID)
            if (!$type) {
                throw new RuntimeException("#[Autowire] attribute requires a typed property (type hint must be specified).");
            }
            
            // First try to get existing instance from DI container
            if (App::has($type)) {
                return App::get($type);
            }

            // Auto-instantiate the class if not found in container
            // This will resolve nested dependencies recursively
            return App::make($type);
        }

        // Return null for unrecognized attribute types (should not happen with proper filtering)
        return null;
    }
}