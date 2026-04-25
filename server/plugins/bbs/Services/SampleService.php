<?php

declare(strict_types=1);

namespace Plugins\Bbs\Services;

use Framework\Basic\BaseService;

/**
 * 示例服务
 *
 * 请根据实际需求修改此服务。
 */
class SampleService extends BaseService
{
    /**
     * 获取示例数据
     *
     * @return array
     */
    public function getSamples(): array
    {
        return [
            ['id' => 1, 'name' => 'Sample 1'],
            ['id' => 2, 'name' => 'Sample 2'],
        ];
    }
}