# sa-chunk-upload 切片上传组件

一个支持大文件分片上传的 Vue 3 组件，基于 Element Plus 的 el-upload 封装。

## 功能特性

- ✅ **分片上传**: 自动将大文件切分成小块上传，支持自定义分片大小
- ✅ **MD5 校验**: 自动计算文件 MD5 哈希值，用于文件去重和完整性校验
- ✅ **进度跟踪**: 实时显示上传进度、当前分片、上传速度
- ✅ **断点续传**: 支持上传失败后重试（需后端配合）
- ✅ **取消上传**: 可随时取消正在进行的上传任务
- ✅ **拖拽上传**: 支持拖拽文件到指定区域上传
- ✅ **文件验证**: 支持文件类型和大小验证
- ✅ **并发控制**: 串行上传多个文件，避免服务器压力过大

## Props 参数

| 参数 | 说明 | 类型 | 默认值 |
|------|------|------|--------|
| modelValue | v-model 绑定值，文件 URL | `string \| string[]` | `[]` |
| multiple | 是否支持多选文件 | `boolean` | `false` |
| limit | 最大上传文件数量 | `number` | `1` |
| maxSize | 单个文件最大大小（MB） | `number` | `1024` |
| chunkSize | 分片大小（MB） | `number` | `5` |
| accept | 接受的文件类型 | `string` | `'*'` |
| acceptHint | 文件类型提示文本 | `string` | `''` |
| disabled | 是否禁用 | `boolean` | `false` |
| drag | 是否启用拖拽上传 | `boolean` | `true` |
| buttonText | 按钮文本 | `string` | `'选择文件'` |
| autoUpload | 是否自动上传 | `boolean` | `false` |

## Events 事件

| 事件名 | 说明 | 回调参数 |
|--------|------|----------|
| update:modelValue | 文件 URL 更新 | `(value: string \| string[])` |
| success | 上传成功 | `(response: any)` |
| error | 上传失败 | `(error: any)` |
| progress | 上传进度更新 | `(progress: number)` |

## 基本用法

```vue
<template>
  <sa-chunk-upload v-model="fileUrl" />
</template>

<script setup>
import { ref } from 'vue'

const fileUrl = ref('')
</script>
```

## 高级用法

### 1. 大视频文件上传

```vue
<template>
  <sa-chunk-upload
    v-model="videoUrl"
    accept="video/*"
    accept-hint="MP4、AVI、MOV"
    :max-size="2048"
    :chunk-size="10"
    :drag="true"
    @success="handleSuccess"
    @progress="handleProgress"
  />
</template>

<script setup>
import { ref } from 'vue'

const videoUrl = ref('')

const handleSuccess = (response) => {
  console.log('上传成功:', response)
}

const handleProgress = (progress) => {
  console.log('上传进度:', progress + '%')
}
</script>
```

### 2. 多文件上传

```vue
<template>
  <sa-chunk-upload
    v-model="fileUrls"
    :multiple="true"
    :limit="5"
    :chunk-size="5"
  />
</template>

<script setup>
import { ref } from 'vue'

const fileUrls = ref([])
</script>
```

### 3. 限制文件类型

```vue
<template>
  <!-- 只允许上传压缩文件 -->
  <sa-chunk-upload
    v-model="zipUrl"
    accept=".zip,.rar,.7z"
    accept-hint="ZIP、RAR、7Z"
    :max-size="500"
  />

  <!-- 只允许上传 PDF -->
  <sa-chunk-upload
    v-model="pdfUrl"
    accept=".pdf"
    accept-hint="PDF"
  />
</template>
```

### 4. 自动上传模式

```vue
<template>
  <sa-chunk-upload
    v-model="fileUrl"
    :auto-upload="true"
  />
</template>
```

### 5. 手动控制上传

```vue
<template>
  <sa-chunk-upload
    v-model="fileUrl"
    :auto-upload="false"
  />
  <!-- 组件会自动显示"开始上传"按钮 -->
</template>
```

## 后端接口要求

组件会调用 `chunkUpload` API，每个分片上传时会发送以下参数：

```typescript
{
  file: Blob,           // 分片文件数据
  hash: string,         // 文件 MD5 哈希值
  chunkIndex: number,   // 当前分片索引（从 0 开始）
  totalChunks: number,  // 总分片数
  fileName: string      // 原始文件名
}
```

### 后端实现建议

1. **接收分片**: 根据 `hash` 和 `chunkIndex` 保存分片
2. **合并文件**: 当接收到所有分片后（`chunkIndex === totalChunks - 1`），合并所有分片
3. **返回 URL**: 合并完成后返回文件访问 URL
4. **断点续传**: 可以实现接口检查已上传的分片，避免重复上传

### 示例后端响应

```json
{
  "code": 200,
  "data": {
    "url": "/uploads/abc123def456/example.mp4",
    "hash": "abc123def456"
  },
  "message": "上传成功"
}
```

## 工作原理

1. **文件选择**: 用户选择文件后，组件计算文件需要分成多少片
2. **MD5 计算**: 计算整个文件的 MD5 哈希值（用于去重和校验）
3. **分片上传**: 按顺序上传每个分片，实时更新进度
4. **进度显示**: 显示当前分片、总分片数、上传速度
5. **完成处理**: 所有分片上传完成后，更新 v-model 值

## 性能优化建议

1. **分片大小**: 
   - 网络较好: 可设置 10-20MB
   - 网络一般: 建议 5-10MB
   - 网络较差: 建议 2-5MB

2. **文件大小限制**: 根据实际需求设置合理的 `maxSize`

3. **并发控制**: 组件默认串行上传文件，避免同时上传多个大文件

## 注意事项

1. 需要安装依赖: `spark-md5` 和 `@types/spark-md5`
2. 后端需要实现分片接收和合并逻辑
3. 建议在后端实现文件去重（通过 MD5 哈希）
4. 大文件上传时注意服务器超时设置

## 依赖安装

```bash
pnpm add spark-md5
pnpm add -D @types/spark-md5
```
