<?php

declare(strict_types=1);

namespace App\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Framework\Attributes\Validate;
#use Framework\Attributes\ValidationException;
use RuntimeException;

class ValidateMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // 1. 获取注解配置
        // (假设 MiddlewareDispatcher 已经将注解实例注入到了 request attributes)
        /** @var Validate|null $attr */
        //$attr = $request->attributes->get(Validate::class);
		
        $attributes = $request->attributes->get('_attributes', []);

        $attr = $attributes[Validate::class] ?? null;
		

        if (!$attr) {
            return $next($request);
        }

        // 2. 准备要校验的数据
        // 获取 Query(GET), Request(POST), 以及 JSON Body (如果是 json 请求)
        $data = $this->getRequestData($request);

        // 3. 实例化验证器
        $validatorClass = $attr->validator;
        if (!class_exists($validatorClass)) {
            throw new RuntimeException("Validator class '{$validatorClass}' not found.");
        }

        // 假设你的验证器继承自 think\Validate 或 Framework\Validation\Validate
        $validator = new $validatorClass();

        // 4. 配置验证器 (场景 & 批量模式)
        if ($attr->scene && method_exists($validator, 'scene')) {
            $validator->scene($attr->scene);
        }
        
        if ($attr->batch && method_exists($validator, 'batch')) {
            $validator->batch(true);
        }

        // 5. 执行校验
		if (!$validator->check($data)) {
			// 抛出异常，而不是直接返回 Response
			return $this->errorResponse($validator->getError());
			#throw new \Framework\Validation\ValidationException($validator->getError());
		}

        // 6. 校验通过，继续执行
        return $next($request);
    }

    /**
     * 获取请求数据 (兼容 GET/POST/JSON)
     */
    private function getRequestData(Request $request): array
    {
        // 获取 GET 和 POST
        $data = array_merge($request->query->all(), $request->request->all());

        // 如果是 JSON 请求，合并 JSON 数据
        if ($request->getContentTypeFormat() === 'json') {
            try {
                $json = $request->toArray();
                $data = array_merge($data, $json);
            } catch (\Exception $e) {
                // JSON 解析失败忽略
            }
        }

        return $data;
    }

    /**
     * 生成错误响应
     */
    private function errorResponse(mixed $error): Response
    {
        // 推荐使用 422 Unprocessable Entity 状态码
        return new JsonResponse([
            'code'    => 422,
            'message' => '参数校验失败',
            'errors'  => $error // string 或 array (如果是 batch 模式)
        ], 422);
    }
}