<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @Filename: AttributeInjectionPass.php
 */

namespace Framework\Container\Compiler;

use Framework\DI\Attribute\Autowire;
use Framework\DI\Attribute\Inject;
use Framework\DI\AttributeInjector;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * 属性注入编译器通道
 *
 * 该类实现 Symfony 的 CompilerPassInterface 接口，
 * 用于在容器编译阶段自动扫描并配置带有 #[Inject] 或 #[Autowire] 注解的服务。
 *
 * 工作原理：
 * 1. 遍历容器中所有已定义的服务
 * 2. 通过反射检查服务的属性是否带有注入注解
 * 3. 对带有注解的服务配置 Configurator，使其在实例化后自动调用 AttributeInjector::inject()
 *
 * @package Framework\Container\Compiler
 */
class AttributeInjectionPass implements CompilerPassInterface
{
    /**
     * 处理容器编译
     *
     * 在容器编译阶段执行，扫描所有服务定义，
     * 为带有注入注解的服务配置自动注入机制。
     *
     * @param ContainerBuilder $container Symfony 容器构建器
     *
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        // 遍历容器中定义的所有服务
        foreach ($container->getDefinitions() as $id => $definition) {
            // 跳过抽象服务、合成服务或没有类名的服务
            if ($definition->isAbstract() || $definition->isSynthetic() || !$definition->getClass()) {
                continue;
            }

            $className = $definition->getClass();

            // 简单检查类是否存在
            if (!class_exists($className)) {
                continue;
            }

            // 如果该类有注入注解，配置 "Configurator"
            if ($this->hasInjectionAttributes($className)) {
                // setConfigurator 方法告诉 Symfony 容器：
                // 当这个服务实例化完成后，请调用 AttributeInjector::inject($instance)
                $definition->setConfigurator([AttributeInjector::class, 'inject']);
            }
        }
    }

    /**
     * 检查类是否包含注入注解
     *
     * 通过反射快速扫描类的所有属性，检查是否存在 #[Inject] 或 #[Autowire] 注解。
     * 此方法用于避免为不需要注入的服务配置不必要的 Configurator。
     *
     * @param string $className 要检查的类名
     *
     * @return bool 如果类包含注入注解返回 true，否则返回 false
     */
    private function hasInjectionAttributes(string $className): bool
    {
        try {
            $reflection = new ReflectionClass($className);

            // 扫描所有属性
            foreach ($reflection->getProperties() as $property) {
                if (!empty($property->getAttributes(Inject::class)) ||
                    !empty($property->getAttributes(Autowire::class))) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // 忽略反射错误
            return false;
        }
        return false;
    }
}
