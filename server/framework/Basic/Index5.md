基于框架架构，这套设计体现了典型的 **分层架构（Layered Architecture）**，通过 **Traits** 复用 CRUD 逻辑，通过 **Adapter 模式** 抹平 ORM 差异。

以下是基于 `product`（产品表）的具体实现代码，包含 **模型层 (Model)**、**数据访问层 (DAO)**、**服务层 (Service)** 和 **控制器层 (Controller)**。

### 1. 目录结构示例

假设你的应用代码在 `App` 命名空间下：

```text
App/
├── Controller/
│   └── ProductController.php
├── Service/
│   └── ProductService.php
├── Dao/
│   └── ProductDao.php
├── Model/
│   └── ProductModel.php
```

---

### 2. 模型层 (Model)

这是底层的 ORM 模型。因为 `BaseDao` 会根据配置动态加载 Laravel 或 ThinkPHP 的适配器，通常你需要根据你主要使用的 ORM 编写模型。

假设你主要使用 **ThinkORM** 风格（或者兼容两者通用写法）：

```php
<?php

declare(strict_types=1);

namespace App\Model;

use think\Model; 
// 如果是 Laravel 则是 use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    // 表名
    protected $table = 'product';

    // 主键
    protected $pk = 'id';

    // 自动时间戳 (ThinkPHP: autoWriteTimestamp, Laravel: timestamps)
    protected $autoWriteTimestamp = true;

    // 允许写入的字段 (ThinkPHP不需要严格定义，Laravel需要 fillable)
    // 为了兼容性建议定义
    protected $fillable = ['name', 'price', 'stock', 'description', 'status'];
}
```

---

### 3. 数据访问层 (DAO)

`ProductDao` 继承 `BaseDao`，核心任务是指定它要代理哪个模型类。

```php
<?php

declare(strict_types=1);

namespace App\Dao;

use Framework\Basic\BaseDao;
use App\Model\ProductModel;

class ProductDao extends BaseDao
{
    /**
     * 设置当前 DAO 对应的模型类
     * @return string
     */
    protected function setModel(): string
    {
        return ProductModel::class;
    }

    /**
     * 你可以在这里扩展特定于数据库查询的方法
     * 例如：获取库存不足的产品
     */
    public function getLowStockProducts(int $threshold = 10)
    {
        // 调用 BaseDao 的魔法方法，最终转发给 ORM Adapter
        return $this->search(['stock', '<', $threshold], false);
    }
}
```

---

### 4. 服务层 (Service)

`ProductService` 继承 `BaseService`。利用 `Injectable` 特性注入 `ProductDao`。这里处理业务逻辑（如库存检查、价格计算）。

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Framework\Basic\BaseService;
use App\Dao\ProductDao;
use RuntimeException;

/**
 * 声明注入的属性类型，以便 IDE 提示
 * @property ProductDao $dao
 */
class ProductService extends BaseService
{
    // 通过 Injectable 注入 DAO
    // 假设你的 DI 容器支持属性注入 (#[Inject] 或 类型提示自动注入)
    // 如果框架是通过属性名自动注入，需确保属性名与注册名称一致或在 initialize 中赋值
    //protected ?ProductDao $dao = null; 


    // 容器会自动注入 ProductDao
	/*
    public function __construct(ProductDao $dao)
    {
        // 1. 赋值给父类的 $this->dao 属性
        $this->dao = $dao;

        // 2. 【必须】调用父类构造函数
        // 虽然父类构造函数没有参数，但必须调用以执行 $this->inject() 等逻辑
        parent::__construct();
    }
	*/
	/*	或者
    public function __construct()
    {
		parent::__construct();
        $this->dao = App::make(ProductDao::class);
		
    }
	*/	


    /**
     * 初始化 (如果依赖注入没有自动处理，可以在这里手动获取)
     */
    protected function initialize(): void
    {
        // 假设容器通过类型提示能自动注入，这一步可能由 inject() 完成
        // 如果没有自动注入，可以使用:
        if ($this->dao === null) {
            $this->dao = app(ProductDao::class);
        }
    }

    /**
     * 覆写 save 方法，添加业务逻辑
     * 例如：创建产品时，如果价格小于0抛出异常
     */
    public function save(array $data)
    {
        if (isset($data['price']) && $data['price'] < 0) {
            throw new RuntimeException('产品价格不能为负数');
        }
        
        // 调用父类代理到 DAO -> ORM
        return parent::save($data);
    }

    /**
     * 自定义业务方法：上架产品
     */
    public function onSale(int $id)
    {
        // 使用事务，确保数据安全
        return $this->transaction(function () use ($id) {
            $product = $this->dao->get($id);
            if (!$product) {
                throw new RuntimeException('产品不存在');
            }
            
            return $this->dao->update($id, ['status' => 1]);
        });
    }
}
```

---

### 5. 应用层 (Controller)

`ProductController` 继承 `BaseController`。
**最关键的一步**：定义 `protected string $serviceClass`。父类的构造函数会自动实例化该 Service 并赋值给 `$this->service`。
由于父类使用了 `CrudActionTrait`，你甚至不需要写 `index`, `store` 等方法，它们已经自动生效了。

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Framework\Basic\BaseController;
use App\Service\ProductService;
use Symfony\Component\HttpFoundation\Request;
use Framework\Utils\Json; // 假设你有这个工具类

class ProductController extends BaseController
{
    // 【核心配置】指定该控制器使用的 Service 类
    // 父类构造函数会自动执行：$this->service = App()->make(ProductService::class);
    protected string $serviceClass = ProductService::class;

    /**
     * 初始化钩子
     * 可以在这里配置验证器、权限筛选等
     */
    protected function initialize(): void
    {
        // 配置查询字段映射（对应 CrudQueryTrait 中的逻辑）
        // 假设 trait 里用 $this->queryFields 来过滤前端传参
        // $this->queryFields = ['name', 'status']; 
        
        // 配置验证器 (假设你有验证器工厂)
        // $this->validator = app('validator')->make(ProductValidate::class);
    }

    /**
     * 自定义接口：产品上架
     * 路由示例：POST /product/on_sale
     */
    public function onSale(Request $request)
    {
        try {
            $id = $request->get('id');
            if (!$id) {
                return $this->fail('ID不能为空');
            }

            // 调用具体的 Service 业务方法
            /** @var ProductService $service */
            $service = $this->service;
            $service->onSale((int)$id);

            return Json::success([], '上架成功');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    // index, show, store, update, destroy 方法
    // 已由 Framework\Basic\Traits\CrudActionTrait 自动实现
}
```

### 总结与调用流程

1.  **Request**: 用户发起 `POST /product/store`。
2.  **Controller**: `ProductController` 继承了 `CrudActionTrait`，命中 `store()` 方法。
3.  **Controller**: `store()` 方法内部调用 `$this->service->save($data)`。
    *   这里 `$this->service` 已经在 `BaseController::__construct` 中被初始化为 `ProductService` 的实例。
4.  **Service**: `ProductService::save()` 被调用（这里我们覆写了它加入了价格检查逻辑）。
    *   检查通过后，调用 `parent::save()`，也就是 `BaseService::__call('save', ...)`。
5.  **Service Proxy**: `BaseService` 代理调用 `$this->dao->save()`。
6.  **DAO**: `ProductDao` 没有覆写 `save`，于是命中 `BaseDao::__call('save', ...)`。
7.  **DAO Proxy**: `BaseDao` 将调用转发给底层的 ORM Adapter（如 `ThinkphpORMFactory` 实例）。
8.  **ORM Adapter**: 最终执行 `ProductModel::create($data)` 或相应的 SQL 操作。

这样设计的好处是：**ProductController 代码极少**，只关注特定业务入口；**ProductService** 承载业务逻辑；**ProductDao** 承载数据定义；底层 ORM 可以随时切换而不影响上层代码。