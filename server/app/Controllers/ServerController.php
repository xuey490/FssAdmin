<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ServerMonitorService;
use App\Services\RedisMonitorService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;

class ServerController extends BaseController
{
    protected ServerMonitorService $serverMonitorService;
    protected RedisMonitorService $redisMonitorService;

    protected function initialize(): void
    {
        $this->serverMonitorService = new ServerMonitorService();
        $this->redisMonitorService = new RedisMonitorService();
    }

    // ==================== 服务器监控 ====================

    #[Route(path: '/api/core/server/monitor', methods: ['GET'], name: 'server.monitor')]
    #[Auth(required: true)]
    #[Permission(['core:server:monitor'])]
    public function monitor(Request $request): BaseJsonResponse
    {
        $info = $this->serverMonitorService->getServerInfo();
        return $this->success($info);
    }

    // ==================== 缓存管理 ====================

    #[Route(path: '/api/core/server/cache', methods: ['GET'], name: 'server.cache.info')]
    #[Auth(required: true)]
    #[Permission(['core:server:cache'])]
    public function getCacheInfo(Request $request): BaseJsonResponse
    {
        $info = $this->serverMonitorService->getCacheInfo();
        return $this->success($info);
    }

    #[Route(path: '/api/core/server/cache', methods: ['DELETE'], name: 'server.cache.clear')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission(['core:server:clear'])]
    public function clearCache(Request $request): BaseJsonResponse
    {
        $result = $this->serverMonitorService->clearCache();
        return $this->success($result);
    }

    // ==================== Redis管理 ====================

    #[Route(path: '/api/core/server/redis', methods: ['GET'], name: 'server.redis.info')]
    #[Auth(required: true)]
    public function getRedisInfo(Request $request): BaseJsonResponse
    {
        if (!extension_loaded('redis') && !class_exists('\Predis\Client')) {
            return $this->fail('Redis扩展未安装');
        }

        $info = $this->redisMonitorService->getFullInfo();
        return $this->success($info);
    }

    #[Route(path: '/api/core/server/redis/browser/level1', methods: ['GET'], name: 'server.redis.browser.level1')]
    #[Auth(required: true)]
    public function getRedisBrowserLevel1(Request $request): BaseJsonResponse
    {
        if (!extension_loaded('redis') && !class_exists('\Predis\Client')) {
            return $this->fail('Redis扩展未安装');
        }

        $pattern = $request->query->get('pattern', '*');
        $keys = $this->redisMonitorService->getFirstLevelKeys($pattern);
        
        return $this->success($keys);
    }

    #[Route(path: '/api/core/server/redis/browser/level2', methods: ['GET'], name: 'server.redis.browser.level2')]
    #[Auth(required: true)]
    public function getRedisBrowserLevel2(Request $request): BaseJsonResponse
    {
        if (!extension_loaded('redis') && !class_exists('\Predis\Client')) {
            return $this->fail('Redis扩展未安装');
        }

        $prefix = $request->query->get('prefix', '');
        if (empty($prefix)) {
            return $this->fail('前缀不能为空');
        }

        $keys = $this->redisMonitorService->getSecondLevelKeys($prefix);
        
        return $this->success($keys);
    }

    #[Route(path: '/api/core/server/redis/browser/level3', methods: ['GET'], name: 'server.redis.browser.level3')]
    #[Auth(required: true)]
    public function getRedisBrowserLevel3(Request $request): BaseJsonResponse
    {
        if (!extension_loaded('redis') && !class_exists('\Predis\Client')) {
            return $this->fail('Redis扩展未安装');
        }

        $prefix = $request->query->get('prefix', '');
        if (empty($prefix)) {
            return $this->fail('前缀不能为空');
        }

        $keys = $this->redisMonitorService->getThirdLevelKeys($prefix);
        
        return $this->success($keys);
    }

    #[Route(path: '/api/core/server/redis/browser/key-info', methods: ['GET'], name: 'server.redis.browser.keyInfo')]
    #[Auth(required: true)]
    public function getRedisKeyInfo(Request $request): BaseJsonResponse
    {
        if (!extension_loaded('redis') && !class_exists('\Predis\Client')) {
            return $this->fail('Redis扩展未安装');
        }

        $key = $request->query->get('key', '');
        if (empty($key)) {
            return $this->fail('键名不能为空');
        }

        $info = $this->redisMonitorService->getKeyInfo($key);
        
        return $this->success($info);
    }

    #[Route(path: '/api/core/server/redis/browser/delete', methods: ['DELETE'], name: 'server.redis.browser.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function deleteRedisBrowserKey(Request $request): BaseJsonResponse
    {
        if (!extension_loaded('redis') && !class_exists('\Predis\Client')) {
            return $this->fail('Redis扩展未安装');
        }

        $body = $this->parseBody($request);
        $key = $body['key'] ?? '';
        $pattern = $body['pattern'] ?? '';

        if (empty($key) && empty($pattern)) {
            return $this->fail('键名或模式不能为空');
        }

        try {
            if (!empty($pattern)) {
                // 批量删除
                $count = $this->redisMonitorService->deleteKeysByPattern($pattern);
                return $this->success(['deleted' => $count], "成功删除 {$count} 个键");
            } else {
                // 单个删除
                $result = $this->redisMonitorService->deleteKey($key);
                return $result 
                    ? $this->success([], '删除成功')
                    : $this->fail('删除失败');
            }
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/core/server/redis/keys', methods: ['GET'], name: 'server.redis.keys')]
    #[Auth(required: true)]
    public function getRedisKeys(Request $request): BaseJsonResponse
    {
        if (!extension_loaded('redis') && !class_exists('\Predis\Client')) {
            return $this->fail('Redis扩展未安装');
        }

        $pattern = $request->query->get('pattern', '*');
        
        try {
            // 使用 SCAN 命令获取键列表（更安全）
            $keys = [];
            $cursor = 0;
            do {
                $result = $this->redisMonitorService->redis->scan($cursor, ['MATCH' => $pattern, 'COUNT' => 100]);
                $cursor = $result[0];
                $keys = array_merge($keys, $result[1]);
            } while ($cursor != 0);

            return $this->success(['keys' => $keys, 'total' => count($keys)]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/core/server/redis/value', methods: ['GET'], name: 'server.redis.value')]
    #[Auth(required: true)]
    public function getRedisValue(Request $request): BaseJsonResponse
    {
        if (!extension_loaded('redis') && !class_exists('\Predis\Client')) {
            return $this->fail('Redis扩展未安装');
        }

        $key = $request->query->get('key');
        if (empty($key)) {
            return $this->fail('键名不能为空');
        }

        try {
            $value = $this->redisMonitorService->redis->get($key);
            $type = $this->redisMonitorService->redis->type($key);
            $ttl = $this->redisMonitorService->redis->ttl($key);

            return $this->success([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'ttl' => $ttl,
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/core/server/redis/key', methods: ['DELETE'], name: 'server.redis.deleteKey')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function deleteRedisKey(Request $request): BaseJsonResponse
    {
        if (!extension_loaded('redis') && !class_exists('\Predis\Client')) {
            return $this->fail('Redis扩展未安装');
        }

        $body = $this->parseBody($request);
        $key = $body['key'] ?? '';

        if (empty($key)) {
            return $this->fail('键名不能为空');
        }

        try {
            $result = $this->redisMonitorService->redis->del($key);
            return $this->success(['deleted' => $result], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/core/server/redis/clear', methods: ['POST'], name: 'server.redis.clear')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function clearRedis(Request $request): BaseJsonResponse
    {
        if (!extension_loaded('redis') && !class_exists('\Predis\Client')) {
            return $this->fail('Redis扩展未安装');
        }

        try {
            $result = $this->redisMonitorService->flushAll();
            return $result 
                ? $this->success([], 'Redis数据库已清空')
                : $this->fail('清空失败');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 辅助方法 ====================

    private function parseBody(Request $request): array
    {
        $body = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }
        return array_merge($request->request->all(), $body);
    }
}
