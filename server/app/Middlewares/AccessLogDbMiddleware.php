<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Models\SysAccessLog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessLogDbMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $startTime = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        try {
            $currentUser = $request->attributes->get('current_user');

            $params = $this->getFilteredParams($request);
            $requestBody = empty($params) ? null : json_encode($params, JSON_UNESCAPED_UNICODE);

            SysAccessLog::create([
                'user_id' => $currentUser?->user_id ?? null,
                'user_name' => $currentUser?->user_name ?? null,
                'ip' => (string) ($request->getClientIp() ?? ''),
                'method' => strtoupper((string) $request->getMethod()),
                'path' => (string) $request->getPathInfo(),
                'query_string' => $request->getQueryString(),
                'status_code' => (int) $response->getStatusCode(),
                'duration_ms' => $durationMs,
                'user_agent' => $request->headers->get('User-Agent'),
                'referer' => $request->headers->get('Referer'),
                'request_body' => $requestBody,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
        }

        return $response;
    }

    private function getFilteredParams(Request $request): array
    {
        $params = $request->query->all() + $request->request->all();

        if ($request->getContentTypeFormat() === 'json') {
            try {
                $json = $request->toArray();
                $params = array_merge($params, $json);
            } catch (\Exception $e) {
            }
        }

        $sensitiveKeys = ['password', 'pwd', 'secret', 'token', 'authorization', 'card_number', 'refresh_token', 'access_token'];
        foreach ($params as $key => &$value) {
            if (in_array(strtolower((string) $key), $sensitiveKeys, true)) {
                $value = '******';
            }
        }

        return $params;
    }
}

