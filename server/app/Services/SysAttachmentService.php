<?php

declare(strict_types=1);

/**
 * 附件管理服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use App\Models\SysAttachment;
use App\Models\SysAttachmentCategory;
use App\Models\SysConfig;
use App\Dao\SysAttachmentDao;
use App\Dao\SysAttachmentCategoryDao;
use Framework\Basic\BaseService;

/**
 * SysAttachmentService 附件管理服务
 */
class SysAttachmentService extends BaseService
{
    protected SysAttachmentDao $attachmentDao;
    protected SysAttachmentCategoryDao $categoryDao;

    public function __construct()
    {
        parent::__construct();
        $this->attachmentDao = new SysAttachmentDao();
        $this->categoryDao   = new SysAttachmentCategoryDao();
    }

    // ==================== 分类管理 ====================

    /**
     * 获取分类列表（带树形结构可选）
     */
    public function getCategoryList(array $params): array
    {
        // 树形结构
        if (!empty($params['tree'])) {
            return SysAttachmentCategory::buildTree(0);
        }

        $query = SysAttachmentCategory::query()->whereNull('delete_time');

        if (!empty($params['category_name'])) {
            $query->where('category_name', 'like', '%' . $params['category_name'] . '%');
        }

        return $query->orderBy('sort')->get()->map(function ($item) {
            $arr = $item->toArray();
            $arr['label'] = $item->category_name;
            $arr['value'] = $item->id;
            return $arr;
        })->toArray();
    }

    /**
     * 获取分类详情
     */
    public function getCategoryDetail(int $id): ?array
    {
        $category = SysAttachmentCategory::find($id);
        return $category ? $category->toArray() : null;
    }

    /**
     * 创建分类
     */
    public function createCategory(array $data, int $operator = 0): SysAttachmentCategory
    {
        // 计算 level
        $parentId = (int)($data['parent_id'] ?? 0);
        if ($parentId > 0) {
            $parent = SysAttachmentCategory::find($parentId);
            $data['level'] = ($parent ? $parent->level : '0,') . $parentId . ',';
        } else {
            $data['level'] = '0,';
        }

        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;

        return SysAttachmentCategory::create($data);
    }

    /**
     * 更新分类
     */
    public function updateCategory(int $id, array $data, int $operator = 0): bool
    {
        $category = SysAttachmentCategory::find($id);
        if (!$category) {
            throw new \Exception('分类不存在');
        }

        if (isset($data['parent_id'])) {
            $parentId = (int)$data['parent_id'];
            if ($parentId === $id) {
                throw new \Exception('上级分类不能是当前分类本身');
            }

            if ($parentId > 0) {
                $parent = SysAttachmentCategory::find($parentId);
                if ($parent) {
                    $currentLevelStr = $category->level . $category->id . ',';
                    if (str_starts_with($parent->level, $currentLevelStr)) {
                        throw new \Exception('上级分类不能是当前分类的子分类');
                    }
                    $data['level'] = $parent->level . $parentId . ',';
                } else {
                    $data['level'] = '0,';
                }
            } else {
                $data['level'] = '0,';
            }
        }

        $data['updated_by'] = $operator;
        $category->fill($data);
        return $category->save();
    }

    /**
     * 删除分类
     */
    public function deleteCategory(int|string $id): bool
    {
        $category = SysAttachmentCategory::find($id);
        if (!$category) {
            return false;
        }

        if ($category->hasChildren()) {
            throw new \Exception('该分类下存在子分类，无法删除');
        }

        if ($category->hasAttachments()) {
            throw new \Exception('该分类下存在附件，无法删除');
        }

        return (bool)$category->delete();
    }

    // ==================== 附件管理 ====================

    /**
     * 获取附件列表
     */
    public function getList(array $params): array
    {
        $page   = (int)($params['page'] ?? 1);
        $limit  = (int)($params['limit'] ?? 20);

        $query = SysAttachment::query();

        if (!empty($params['category_id'])) {
            $query->where('category_id', (int)$params['category_id']);
        }

        if (!empty($params['origin_name'])) {
            $query->where('origin_name', 'like', '%' . $params['origin_name'] . '%');
        }

        if (!empty($params['storage_mode'])) {
            $query->where('storage_mode', (int)$params['storage_mode']);
        }

        // 排序
        $orderField = $params['orderField'] ?? 'create_time';
        $orderType  = $params['orderType'] ?? 'desc';
        $allowOrder = ['create_time', 'size_byte', 'origin_name'];
        if (!in_array($orderField, $allowOrder)) {
            $orderField = 'create_time';
        }
        $query->orderBy($orderField, $orderType === 'asc' ? 'asc' : 'desc');

        $total = $query->count();
        $list  = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->toArray();

        return [
            'list'  => $list,
            'total' => $total,
            'page'  => $page,
            'size'  => $limit,
        ];
    }

    /**
     * 获取附件详情
     */
    public function getDetail(int $id): ?array
    {
        $attachment = SysAttachment::find($id);
        return $attachment ? $attachment->toArray() : null;
    }

    /**
     * 读取 group_id=2 的上传配置，返回 key=>value 映射
     */
    protected function getUploadConfig(): array
    {
        $rows = SysConfig::where('group_id', 2)->get();
        $cfg  = [];
        foreach ($rows as $row) {
            $cfg[$row->key] = $row->value;
        }
        return $cfg;
    }

    /**
     * 上传附件（接收 Symfony UploadedFile）
     * 校验规则从 sa_system_config group_id=2 读取
     *
     * @param mixed  $file       Symfony UploadedFile
     * @param int    $categoryId 分类ID
     * @param int    $operator   操作人ID
     * @param string $mode       'image' 仅图片 | 'file' 图片+文件合并
     */
    public function upload($file, int $categoryId = 0, int $operator = 0, string $mode = 'image'): array
    {
        if (!$file || !$file->isValid()) {
            throw new \Exception('无效的上传文件');
        }

        // 读取系统上传配置
        $cfg = $this->getUploadConfig();

        // 图片允许后缀
        $imageSuffixes = !empty($cfg['upload_allow_ext'])
            ? array_map('trim', explode(',', strtolower($cfg['upload_allow_ext'])))
            : ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // 图片允许 MIME
        $imageMimes = !empty($cfg['upload_allow_mime'])
            ? array_map('trim', explode(',', strtolower($cfg['upload_allow_mime'])))
            : ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

        // 文件模式：额外允许的后缀（逗号分隔）
        $fileSuffixes = !empty($cfg['upload_file_ext'])
            ? array_map('trim', explode(',', strtolower($cfg['upload_file_ext'])))
            : ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'mp4', 'mp3'];

        // 文件模式：额外允许的 MIME
        $fileMimes = !empty($cfg['upload_file_mime'])
            ? array_map('trim', explode(',', strtolower($cfg['upload_file_mime'])))
            : [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'application/zip',
                'application/x-rar-compressed',
                'application/x-rar',
                'video/mp4',
                'audio/mpeg',
            ];

        // 根据 mode 合并允许列表
        if ($mode === 'file') {
            $allowSuffixes = array_unique(array_merge($imageSuffixes, $fileSuffixes));
            $allowMimes    = array_unique(array_merge($imageMimes, $fileMimes));
        } else {
            // 默认 image 模式，只允许图片
            $allowSuffixes = $imageSuffixes;
            $allowMimes    = $imageMimes;
        }

        // 最大文件大小（单位 MB，默认 10）
        $maxSizeMB = isset($cfg['upload_max_size']) ? (float)$cfg['upload_max_size'] : 10;

        // 本地存储根路径（相对 public/，默认 uploads）
        $storageRoot = !empty($cfg['upload_local_path'])
            ? trim($cfg['upload_local_path'], '/')
            : 'uploads';

        // 访问域名（默认读 app.url）
        $domain = !empty($cfg['upload_local_domain'])
            ? rtrim($cfg['upload_local_domain'], '/')
            : rtrim(config('app.url', 'http://127.0.0.1:8000'), '/');

        // ---- 校验后缀 ----
        $suffix = strtolower($file->getClientOriginalExtension());
        if (!in_array($suffix, $allowSuffixes, true)) {
            throw new \Exception('不支持的文件类型，允许：' . implode(', ', $allowSuffixes));
        }

        // ---- 校验真实 MIME（finfo 读魔数，防伪造）----
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file->getPathname());
        if (!in_array(strtolower($mimeType), $allowMimes, true)) {
            throw new \Exception('文件 MIME 类型不被允许：' . $mimeType);
        }

        // ---- 校验文件大小 ----
        $sizeByte = $file->getSize();
        if ($sizeByte > $maxSizeMB * 1024 * 1024) {
            throw new \Exception("文件大小超出限制，最大允许 {$maxSizeMB}MB");
        }

        $originName = $file->getClientOriginalName();
        $sizeInfo   = $this->formatFileSize($sizeByte);
        $hash       = md5_file($file->getPathname());

        // 秒传：相同文件直接返回
        $existing = $this->attachmentDao->findByHash($hash);
        if ($existing) {
            return $existing->toArray();
        }

        // 生成存储路径
        $datePath    = date('Y/m/d');
        $objectName  = uniqid('', true) . '.' . $suffix;
        $storagePath = $storageRoot . '/' . $datePath;
        $absDir      = base_path('public/' . $storagePath);

        if (!is_dir($absDir)) {
            mkdir($absDir, 0755, true);
        }

        $file->move($absDir, $objectName);

        $url = $domain . '/' . $storagePath . '/' . $objectName;

        $attachment = new SysAttachment();
        $attachment->category_id = $categoryId ?: 1;
        $attachment->storage_mode = SysAttachment::STORAGE_LOCAL;
        $attachment->origin_name = $originName;
        $attachment->object_name = $objectName;
        $attachment->hash = $hash;
        $attachment->mime_type = $mimeType;
        $attachment->storage_path = $storagePath;
        $attachment->suffix = $suffix;
        $attachment->size_byte = $sizeByte;
        $attachment->size_info = $sizeInfo;
        $attachment->url = $url;
        $attachment->created_by = $operator;
        $attachment->updated_by = $operator;
        $attachment->save();

        return $attachment->toArray();
    }

    /**
     * 更新附件名称
     */
    public function updateName(int $id, string $originName, int $operator = 0): bool
    {
        $attachment = SysAttachment::find($id);
        if (!$attachment) {
            throw new \Exception('附件不存在');
        }

        $attachment->origin_name = $originName;
        $attachment->updated_by  = $operator;
        return $attachment->save();
    }

    /**
     * 批量移动附件到分类
     */
    public function moveToCategory(array $ids, int $categoryId, int $operator = 0): int
    {
        return SysAttachment::whereIn('id', $ids)->update([
            'category_id' => $categoryId,
            'updated_by'  => $operator,
        ]);
    }

    /**
     * 删除附件（含物理文件）
     */
    public function delete(int|string $id): bool
    {
        $attachment = SysAttachment::find($id);
        if (!$attachment) {
            return false;
        }

        // 删除物理文件
        if ($attachment->storage_mode === SysAttachment::STORAGE_LOCAL && $attachment->storage_path) {
            $filePath = base_path('public/' . $attachment->storage_path . '/' . $attachment->object_name);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        return (bool)$attachment->delete();
    }

    /**
     * 批量删除附件
     */
    public function batchDelete(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            if ($this->delete($id)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * 获取存储统计
     */
    public function getStorageStats(): array
    {
        $totalSize  = (int)SysAttachment::sum('size_byte');
        $totalCount = SysAttachment::count();

        $typeStats = SysAttachment::selectRaw('suffix, count(*) as count, sum(size_byte) as size')
            ->groupBy('suffix')
            ->orderByRaw('sum(size_byte) desc')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'total_size'     => $totalSize,
            'total_count'    => $totalCount,
            'formatted_size' => $this->formatFileSize($totalSize),
            'type_stats'     => $typeStats,
        ];
    }

    // ==================== 辅助方法 ====================

    protected function formatFileSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
}
