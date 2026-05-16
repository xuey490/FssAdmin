<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Utils;

use Symfony\Component\HttpFoundation\Request;

class Pagination
{
    private int $total;

    private int $size;

    private int $currentPage;

    private int $totalPages;

    private string $baseUrl;

    private array $baseQueryParams;

    public function __construct(
        int $total,
        Request $request,
        int $size = 10
    ) {
        $this->total      = max(0, $total);
        $this->size       = $size > 0 ? $size : 10;
        $this->totalPages = (int) ceil($this->total / $this->size);
        $this->totalPages = max(1, $this->totalPages);

        // 获取当前页码，默认为1
        $page              = (int) $request->query->get('page', 1);
        $this->currentPage = max(1, min($page, $this->totalPages));

        // 清理查询参数，移除非法的键（如路径）
        $queryParams = $request->query->all();
        $cleanParams = [];
        foreach ($queryParams as $key => $value) {
            if (! is_string($key) || $key === '' || str_starts_with($key, '/')) {
                continue;
            }
            $cleanParams[$key] = $value;
        }
        $this->baseQueryParams = $cleanParams;

        // 基础 URL（不含查询字符串）
        $this->baseUrl = $request->getSchemeAndHttpHost() . $request->getPathInfo();
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPageSize(): int
    {
        return $this->size;
    }

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->size;
    }

    public function getLimit(): int
    {
        return $this->size;
    }

    /**
     * 生成分页数据结构.
     *
     * @param int $radius 中间显示的页码半径（默认2，即左右各2页）
     */
    public function getData(int $radius = 2): array
    {
        $data = [
            'current_page' => $this->currentPage,
            'total_pages'  => $this->totalPages,
            'total_items'  => $this->total,
            'page_size'    => $this->size,
            'has_previous' => $this->currentPage > 1,
            'has_next'     => $this->currentPage < $this->totalPages,
            'previous_url' => null,
            'next_url'     => null,
            'pages'        => [], // 页码项列表
        ];

        if ($data['has_previous']) {
            $data['previous_url'] = $this->buildUrl($this->currentPage - 1);
        }
        if ($data['has_next']) {
            $data['next_url'] = $this->buildUrl($this->currentPage + 1);
        }

        // 如果只有一页，直接返回
        if ($this->totalPages <= 1) {
            $data['pages'] = $this->totalPages === 1 ? [['type' => 'page', 'number' => 1, 'url' => $this->buildUrl(1), 'is_current' => true]] : [];
            return $data;
        }

        $pages = [];

        // 第一页
        $pages[] = [
            'type'       => 'page',
            'number'     => 1,
            'url'        => $this->buildUrl(1),
            'is_current' => ($this->currentPage === 1),
        ];

        $start = max(2, $this->currentPage - $radius);
        $end   = min($this->totalPages - 1, $this->currentPage + $radius);

        // 省略号（前）
        if ($start > 2) {
            $pages[] = ['type' => 'ellipsis'];
        }

        // 中间页
        for ($i = $start; $i <= $end; ++$i) {
            $pages[] = [
                'type'       => 'page',
                'number'     => $i,
                'url'        => $this->buildUrl($i),
                'is_current' => ($i === $this->currentPage),
            ];
        }

        // 省略号（后）
        if ($end < $this->totalPages - 1) {
            $pages[] = ['type' => 'ellipsis'];
        }

        // 最后一页（如果总页数 > 1）
        if ($this->totalPages > 1) {
            $pages[] = [
                'type'       => 'page',
                'number'     => $this->totalPages,
                'url'        => $this->buildUrl($this->totalPages),
                'is_current' => ($this->totalPages === $this->currentPage),
            ];
        }

        $data['pages'] = $pages;

        return $data;
    }

    private function buildUrl(int $page): string
    {
        $params = $this->baseQueryParams;
        if ($page > 1) {
            $params['page'] = $page;
        } else {
            unset($params['page']); // 如果是第一页，不需要 page 参数
        }
        $queryString = http_build_query($params);
        return $this->baseUrl . ($queryString ? '?' . $queryString : '');
    }
}
