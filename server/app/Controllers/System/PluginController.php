<?php

declare(strict_types=1);

namespace App\Controllers\System;

use App\Services\PluginService;
use Framework\Attributes\Auth;
use Framework\Attributes\Route;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PluginController extends BaseController
{
    protected PluginService $pluginService;

    protected function initialize(): void
    {
        $this->pluginService = new PluginService();
    }

    #[Route(path: '/api/system/plugin/list', methods: ['GET'], name: 'plugin.list')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function list(Request $request): BaseJsonResponse
    {
        return $this->success($this->pluginService->getList());
    }

    #[Route(path: '/api/system/plugin/detail/{name}', methods: ['GET'], name: 'plugin.detail')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function detail(Request $request): BaseJsonResponse
    {
        $name = (string)$request->attributes->get('name', '');
        if ($name === '') {
            return $this->fail('插件名不能为空');
        }

        try {
            return $this->success($this->pluginService->getDetail($name));
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/system/plugin/create', methods: ['POST'], name: 'plugin.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function create(Request $request): BaseJsonResponse
    {
        $payload = $this->getJsonBody($request);
        $result = $this->pluginService->createPlugin($payload);

        return $result['success']
            ? $this->success([], $result['message'])
            : $this->fail($result['message']);
    }

    #[Route(path: '/api/system/plugin/install', methods: ['POST'], name: 'plugin.install')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function install(Request $request): BaseJsonResponse
    {
        $payload = $this->getJsonBody($request);
        $name = (string)($payload['name'] ?? '');
        $autoInstallDependencies = (bool)($payload['auto_install_dependencies'] ?? true);
        $force = (bool)($payload['force'] ?? false);

        if ($name === '') {
            return $this->fail('插件名不能为空');
        }

        $result = $this->pluginService->install($name, $autoInstallDependencies, $force);

        return ($result['success'] ?? false)
            ? $this->success($result, $result['message'] ?? '安装成功')
            : $this->fail($result['message'] ?? '安装失败');
    }

    #[Route(path: '/api/system/plugin/uninstall', methods: ['POST'], name: 'plugin.uninstall')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function uninstall(Request $request): BaseJsonResponse
    {
        $payload = $this->getJsonBody($request);
        $name = (string)($payload['name'] ?? '');
        $force = (bool)($payload['force'] ?? false);

        if ($name === '') {
            return $this->fail('插件名不能为空');
        }

        $result = $this->pluginService->uninstall($name, $force);

        return ($result['success'] ?? false)
            ? $this->success($result, $result['message'] ?? '卸载成功')
            : $this->fail($result['message'] ?? '卸载失败');
    }

    #[Route(path: '/api/system/plugin/enable/{name}', methods: ['PUT'], name: 'plugin.enable')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function enable(Request $request): BaseJsonResponse
    {
        $name = (string)$request->attributes->get('name', '');
        if ($name === '') {
            return $this->fail('插件名不能为空');
        }

        $result = $this->pluginService->enable($name);
        return ($result['success'] ?? false)
            ? $this->success($result, $result['message'] ?? '启用成功')
            : $this->fail($result['message'] ?? '启用失败');
    }

    #[Route(path: '/api/system/plugin/disable/{name}', methods: ['PUT'], name: 'plugin.disable')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function disable(Request $request): BaseJsonResponse
    {
        $name = (string)$request->attributes->get('name', '');
        if ($name === '') {
            return $this->fail('插件名不能为空');
        }

        $result = $this->pluginService->disable($name);
        return ($result['success'] ?? false)
            ? $this->success($result, $result['message'] ?? '禁用成功')
            : $this->fail($result['message'] ?? '禁用失败');
    }

    #[Route(path: '/api/system/plugin/config/{name}', methods: ['GET'], name: 'plugin.config.get')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function getConfig(Request $request): BaseJsonResponse
    {
        $name = (string)$request->attributes->get('name', '');
        if ($name === '') {
            return $this->fail('插件名不能为空');
        }

        try {
            $config = $this->pluginService->getConfig($name);
            return $this->success($config);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/system/plugin/config/{name}', methods: ['PUT'], name: 'plugin.config.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function updateConfig(Request $request): BaseJsonResponse
    {
        $name = (string)$request->attributes->get('name', '');
        if ($name === '') {
            return $this->fail('插件名不能为空');
        }

        $payload = $this->getJsonBody($request);
        $config = $payload['config'] ?? [];
        if (!is_array($config)) {
            return $this->fail('配置格式不正确');
        }

        $result = $this->pluginService->updateConfig($name, $config);

        return ($result['success'] ?? false)
            ? $this->success([], $result['message'] ?? '更新成功')
            : $this->fail($result['message'] ?? '更新失败');
    }

    #[Route(path: '/api/system/plugin/doctor', methods: ['GET'], name: 'plugin.doctor')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function doctor(Request $request): BaseJsonResponse
    {
        $name = (string)$request->query->get('name', '');
        $name = trim($name);

        try {
            $data = $this->pluginService->doctor($name === '' ? null : $name);
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }
}
