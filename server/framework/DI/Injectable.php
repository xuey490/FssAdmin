<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-12-19
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\DI;

use Framework\DI\Attribute\Inject;
use Framework\DI\Attribute\Autowire;
use Framework\DI\Attribute\Context;
use Framework\Core\App;
use Framework\Utils\ReflectionTypes;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

trait Injectable
{
    /**
     * 反射元数据缓存，避免重复反射同一个类
     * 格式: [ ClassName => [ [property_name, attribute_instance, type_name], ...  ] ]
     */
    /** @var array<mixed> */
    protected static array $injectionMetaCache = [];

    /**
     * 执行依赖注入
     */
    protected function inject(): void
    {
        $class = static::class;

        // 1. 如果缓存中没有该类的元数据，先进行解析
        if (!isset(self::$injectionMetaCache[$class])) {
            self::$injectionMetaCache[$class] = $this->parseInjectionMeta($class);
        }

        // 2. 遍历元数据，进行注入
        foreach (self::$injectionMetaCache[$class] as $meta) {
            $propertyName = $meta['property'];
            $attr = $meta['attr'];
            
            // 如果属性已经有值（比如在构造函数中手动赋值了），则跳过
            // 注意：需要 PHP 7.4+ 支持
            if (isset($this->{$propertyName})) {
                continue;
            }

            // 解析值
            $value = $this->resolveDependency($attr, $meta['type']);

            // 赋值 (对于 protected/private 属性，parseInjectionMeta 已经处理了 setAccessible)
            // 使用 ReflectionProperty 赋值比 $this->$name 更安全，尤其是 private 属性
            $meta['reflection_property']->setValue($this, $value);
        }
    }

    /**
     * 解析类的属性元数据
     * @return array<mixed>
 */
    protected function parseInjectionMeta(string $className): array
    {
        $metaList = [];
        $reflection = new ReflectionClass($className);

        // 获取所有属性 (包括 private, protected, public)
        foreach ($reflection->getProperties() as $property) {
            // 获取属性上的 Attribute
            $attributes = $property->getAttributes();
            
            foreach ($attributes as $attribute) {
                $inst = $attribute->newInstance();

                if ($inst instanceof Inject || $inst instanceof Autowire || $inst instanceof Context) {
                    $property->setAccessible(true); // 允许访问 protected/private

                    $metaList[] = [
                        'reflection_property' => $property,
                        'property' => $property->getName(),
                        'attr'     => $inst,
                        'type'     => ReflectionTypes::asNamed($property->getType())?->getName(),
                    ];
                    // 一个属性只处理一个注入注解，处理完即跳出内层循环
                    break;
                }
            }
        }

        return $metaList;
    }

    /**
     * 根据注解类型解析依赖
     */
    protected function resolveDependency(object $attr, ?string $type): mixed
    {

        // 1. 处理 #[Context]
        if ($attr instanceof Context) {
            if (!ContextBag::has($attr->key)) {
                // 🔥 修改这里：不要返回 null，而是抛出异常
                throw new RuntimeException(sprintf(
                    "Context Injection Failed: Key '%s' not found in ContextBag. Did you register ContextInitMiddleware?", 
                    $attr->key
                ));
            }
            return ContextBag::get($attr->key);
        }
		
		

        // 2. 处理 #[Inject]
        if ($attr instanceof Inject) {
            // 如果指定了 id 则用 id，否则尝试用属性类型
            $serviceId = $attr->id ?? $type;
            
            if (!$serviceId) {
                throw new RuntimeException("Cannot inject property without type or ID.");
            }
            
            return App::get($serviceId);
        }

        // 3. 处理 #[Autowire]
        if ($attr instanceof Autowire) {
            if (!$type) {
                throw new RuntimeException("Autowire requires a typed property.");
            }
            // 自动装配直接使用类型名去容器找
            return App::get($type);
        }

        return null;
    }
}