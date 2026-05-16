<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Permission;
use App\Models\SysUser;

class ConsoleController extends BaseController
{
    #[Route(path: '/api/core/console/list', methods: ['GET'], name: 'console.list')]
    #[Permission(['core:console:list'])]
    public function list(Request $request): BaseJsonResponse
    {
        // 返回模拟数据，避免前端报错，如果后续有对应的Service可以替换为真实查询
        $data = [
            'user'    => SysUser::count(), // 或者写死比如 1024
            'attach'  => 120, // 附件数
            'login'   => 56,  // 登录数
            'operate' => 89   // 操作数
        ];

        return $this->success($data);
    }

    #[Route(path: '/api/core/console/login-bar', methods: ['GET'], name: 'console.login_bar')]
    #[Permission(['core:console:list'])]
    public function loginBar(Request $request): BaseJsonResponse
    {
        $data = [
            'x_data' => ['01/01', '01/02', '01/03', '01/04', '01/05', '01/06', '01/07'],
            'y_data' => [12, 34, 23, 45, 12, 65, 34]
        ];
        return $this->success($data);
    }

    #[Route(path: '/api/core/console/login-chart', methods: ['GET'], name: 'console.login_chart')]
    #[Permission(['core:console:list'])]
    public function loginChart(Request $request): BaseJsonResponse
    {
        $data = [
            'x_data' => ['01/01', '01/02', '01/03', '01/04', '01/05', '01/06', '01/07'],
            'y_data' => [12, 34, 23, 45, 12, 65, 34]
        ];
        return $this->success($data);
    }
}