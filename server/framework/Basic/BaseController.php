<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: BaseController.php
 * @Date: 2025-12-10
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Framework\Core\App;
use Framework\Basic\Traits\CrudActionTrait;
use Framework\Basic\Traits\CrudFilterTrait;
use Framework\Basic\Traits\CrudFormatterTrait;
use Framework\Basic\Traits\CrudQueryTrait;
use Framework\Database\DatabaseFactory;
use Framework\DI\Injectable;
use Symfony\Component\HttpFoundation\Request;


abstract class BaseController
{
    use CrudActionTrait;
    use CrudFilterTrait;
    use CrudFormatterTrait;
    use CrudQueryTrait;

    // 引入注入能力
    use Injectable;

    protected Request $request;

    protected DatabaseFactory $db;

    protected object $service;

    // 子类只需要定义这个属性
    protected string $serviceClass = '';
	
	protected string $daoClass = ''; // 新增	
	
	protected string $validatorClass ='';

    protected ?object $validator = null;

	protected ContainerInterface $container;

    // 构造函数不接受参数，完全由内部解决
    public function __construct()
    {
        $this->inject();
		
		$this->container = app();
		
        // 1. 获取全局 Request - 优先从 ContextBag 获取，再从容器获取
        $this->request = \Framework\DI\ContextBag::get('request') ?? app('request');

        // 2. 获取全局 DB
        $this->db = app('db');

        // 3. 【自动初始化 Service】
        // 只有子类定义了 serviceClass 父类自动帮你实例化，没定义就算了，说明这个控制器不需要通用CRUD
        // 场景1：定义了具体的业务 Service (如 ProductService)
        if (!empty($this->serviceClass)) {
            $this->service = App()->make($this->serviceClass);
        } 
        // 场景2：只定义了 DAO，没有定义 Service -> 使用通用 Service 包装 DAO
        elseif (!empty($this->daoClass)) {
            // 实例化通用服务
            $genericService = App()->make(\Framework\Basic\GenericService::class);
            
            // 实例化 DAO
            $dao = App()->make($this->daoClass);
            
            // 手动注入 DAO 到通用服务中 (需要在 BaseService 提供一个 setDao 方法或者通过反射/属性赋值)
            // 假设 BaseService 继承的 Injectable 或本身有 setDao
            if (method_exists($genericService, 'setDao')) {
                $genericService->setDao($dao);
            } else {
                 // 简单粗暴的属性注入（如果是 protected 需要想办法，或者把 BaseService::$dao 改为 public/setter）
                 // 或者利用框架的容器去绑定
            }
            
            $this->service = $genericService;
        }
		
        if (!empty($this->validatorClass)) {
            $this->validator = App()->make($this->validatorClass);
        } 
		
        // 4. 钩子
        $this->initialize();
    }

    /**
     * 子类可根据需要覆盖 lifecycle.
     */
    protected function initialize(): void
    {
    }

    /**
     * 返回成功 JSON.
     */
    protected function success(mixed $data = [], string $msg = 'success'): BaseJsonResponse
    {
        return BaseJsonResponse::success($data, $msg);
    }

    /**
     * 返回失败 JSON.
     */
    protected function fail(string $msg = 'error', int $code = 1): BaseJsonResponse
    {
        return BaseJsonResponse::fail($msg, [], $code);
    }

    /**
     * 服务端错误.
     */
    protected function error(string $msg = 'server error'): BaseJsonResponse
    {
        return BaseJsonResponse::error($msg, 500);
    }

    /**
     * 缓存解析后的 JSON body
     * @var array|null
     */
    protected ?array $jsonBodyCache = null;

    /**
     * 获取当前请求对象
     * 优先使用方法参数传入的 $request，其次使用构造函数中的全局 $request
     *
     * @param Request|null $request
     * @return Request
     */
    protected function getCurrentRequest(?Request $request = null): Request
    {
        return $request ?? $this->request;
    }

    /**
     * 获取请求参数，并支持可选的 XSS 过滤.
     *
     * 支持从以下来源获取参数：
     * 1. Query 参数 (GET)
     * 2. Request 参数 (POST 表单)
     * 3. JSON Body (PUT/POST JSON 请求)
     *
     * @param string $key     参数名
     * @param mixed  $default 默认值
     * @param bool   $filter  是否开启 XSS 过滤（默认开启）
     * @param Request|null $request 请求对象（可选）
     */
    protected function input(string $key, mixed $default = null, bool $filter = true, ?Request $request = null): mixed
    {
        $req = $this->getCurrentRequest($request);

        // 1. 优先从 Query (GET) 获取
        $value = $req->query->get($key);

        // 2. 从 Request (POST 表单) 获取
        if ($value === null) {
            $value = $req->request->get($key);
        }

        // 3. 从 JSON Body 获取（支持 PUT/POST JSON 请求）
        if ($value === null) {
            $jsonBody = $this->getJsonBody($req);
            if (isset($jsonBody[$key])) {
                $value = $jsonBody[$key];
            }
        }

        // 4. 如果所有来源都没有，使用默认值
        if ($value === null) {
            return $default;
        }

        // 5. 如果开启过滤，且值是字符串，则进行转义
        if ($filter && is_string($value)) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return $value;
    }

    /**
     * 获取所有请求参数（合并 GET、POST、JSON Body）
     *
     * @param Request|null $request 请求对象（可选）
     * @return array
     */
    protected function inputAll(?Request $request = null): array
    {
        $req = $this->getCurrentRequest($request);
        return array_merge(
            $req->query->all(),
            $req->request->all(),
            $this->getJsonBody($req)
        );
    }

    /**
     * 获取并缓存 JSON Body
     *
     * @param Request|null $request 请求对象（可选）
     * @return array
     */
    protected function getJsonBody(?Request $request = null): array
    {
        $req = $this->getCurrentRequest($request);

        // 使用请求对象的哈希作为缓存键，确保不同请求有独立缓存
        $cacheKey = spl_object_id($req);

        static $cache = [];

        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $cache[$cacheKey] = [];
        $content = $req->getContent();

        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $cache[$cacheKey] = $decoded;
            }
        }

        return $cache[$cacheKey];
    }


}
