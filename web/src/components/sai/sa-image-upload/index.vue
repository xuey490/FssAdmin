<template>
  <div class="sa-image-upload">
    <el-upload
      ref="uploadRef"
      :file-list="fileList"
      :limit="limit"
      :multiple="multiple"
      :accept="accept"
      :list-type="listType"
      :http-request="handleUpload"
      :before-upload="beforeUpload"
      :on-remove="handleRemove"
      :on-preview="handlePreview"
      :on-exceed="handleExceed"
      :disabled="disabled"
      class="upload-container"
      :class="{ 'is-round': round, 'hide-upload': hideUploadTrigger }"
    >
      <template #default>
        <div
          class="upload-trigger"
          :style="{ width: width + 'px', height: height + 'px' }"
          v-if="!hideUploadTrigger"
        >
          <el-icon class="upload-icon"><Plus /></el-icon>
          <div class="upload-text">上传图片</div>
        </div>
      </template>
      <template #tip>
        <div class="el-upload__tip" v-if="showTips">
          单个文件不超过 {{ maxSize }}MB，最多上传 {{ limit }} 张
        </div>
      </template>
    </el-upload>

    <!-- 图片预览器 -->
    <el-image-viewer
      v-if="previewVisible"
      :url-list="previewUrlList"
      :initial-index="previewIndex"
      @close="handleCloseViewer"
      :hide-on-click-modal="true"
      :teleported="true"
    />
  </div>
</template>

<script lang="ts" setup>
  import { ref, watch, computed } from 'vue'
  import { Plus } from '@element-plus/icons-vue'
  import { ElMessage } from 'element-plus'
  import type { UploadProps, UploadUserFile, UploadRequestOptions } from 'element-plus'
  import { uploadImage } from '@/api/auth'

  defineOptions({ name: 'SaImageUpload' })

  // 定义 Props
  interface Props {
    modelValue?: string | string[] // v-model 绑定值
    multiple?: boolean // 是否支持多选
    limit?: number // 最大上传数量
    maxSize?: number // 最大文件大小(MB)
    accept?: string // 接受的文件类型
    disabled?: boolean // 是否禁用
    listType?: 'text' | 'picture' | 'picture-card' // 文件列表类型
    width?: number // 上传区域宽度(px)
    height?: number // 上传区域高度(px)
    round?: boolean // 是否圆形
    showTips?: boolean // 是否显示上传提示
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
    multiple: false,
    limit: 1,
    maxSize: 5,
    accept: 'image/*',
    disabled: false,
    listType: 'picture-card',
    width: 148,
    height: 148,
    round: false,
    showTips: true
  })

  // 定义 Emits
  const emit = defineEmits<{
    'update:modelValue': [value: string | string[]]
    success: [response: any]
    error: [error: any]
    change: [value: string | string[]]
  }>()

  // 状态
  const uploadRef = ref()
  const fileList = ref<UploadUserFile[]>([])
  const previewVisible = ref(false)
  const previewIndex = ref(0)

  // 计算预览图片列表
  const previewUrlList = computed(() => {
    return fileList.value.map((file) => file.url).filter((url) => url) as string[]
  })

  // 计算是否隐藏上传按钮（单图片模式且已有图片时隐藏）
  const hideUploadTrigger = computed(() => {
    return !props.multiple && fileList.value.length >= props.limit
  })

  // 监听 modelValue 变化，同步到 fileList
  watch(
    () => props.modelValue,
    (newVal) => {
      if (!newVal || (Array.isArray(newVal) && newVal.length === 0)) {
        fileList.value = []
        uploadRef.value?.clearFiles()
        return
      }

      const urls = Array.isArray(newVal) ? newVal : [newVal]
      fileList.value = urls
        .filter((url) => url)
        .map((url, index) => ({
          name: `image-${index + 1}`,
          url: url,
          uid: Date.now() + index
        }))
    },
    { immediate: true }
  )

  // 上传前验证
  const beforeUpload: UploadProps['beforeUpload'] = (file) => {
    // 验证文件类型
    const isImage = file.type.startsWith('image/')
    if (!isImage) {
      ElMessage.error('只能上传图片文件!')
      return false
    }

    // 验证文件大小
    const isLtMaxSize = file.size / 1024 / 1024 < props.maxSize
    if (!isLtMaxSize) {
      ElMessage.error(`图片大小不能超过 ${props.maxSize}MB!`)
      return false
    }

    return true
  }

  // 自定义上传
  const handleUpload = async (options: UploadRequestOptions) => {
    const { file, onSuccess, onError } = options

    try {
      // 创建 FormData
      const formData = new FormData()
      formData.append('file', file)

      // 调用上传接口，request 已解包一层 data，response 即后端 data 字段
      const response: any = await uploadImage(formData)

      // 兼容后端返回 { url } 对象或直接返回 url 字符串
      const imageUrl = response?.url || (typeof response === 'string' ? response : '')

      if (!imageUrl) {
        throw new Error('上传失败，未返回图片地址')
      }

      // 用返回的真实 url 替换 el-upload 生成的临时 blob url
      const target = fileList.value.find((item) => item.uid === file.uid)
      if (target) {
        target.url = imageUrl
      } else {
        fileList.value.push({ name: file.name, url: imageUrl, uid: file.uid })
      }
      updateModelValue()

      // 触发成功回调
      onSuccess?.(response)
      emit('success', response)
      ElMessage.success('上传成功!')
    } catch (error: any) {
      console.error('上传失败:', error)
      onError?.(error)
      emit('error', error)
      ElMessage.error(error.message || '上传失败!')
    }
  }

  // 删除文件
  const handleRemove: UploadProps['onRemove'] = (file) => {
    const index = fileList.value.findIndex((item) => item.uid === file.uid)
    if (index > -1) {
      fileList.value.splice(index, 1)
      updateModelValue()
    }
  }

  // 超出限制提示
  const handleExceed: UploadProps['onExceed'] = () => {
    ElMessage.warning(`最多只能上传 ${props.limit} 张图片，请先删除已有图片后再上传`)
  }

  // 预览图片
  const handlePreview: UploadProps['onPreview'] = (file) => {
    const index = fileList.value.findIndex((item) => item.uid === file.uid)
    previewIndex.value = index > -1 ? index : 0
    previewVisible.value = true
  }

  // 关闭预览器
  const handleCloseViewer = () => {
    previewVisible.value = false
  }

  // 更新 v-model 值
  const updateModelValue = () => {
    const urls = fileList.value.map((file) => file.url).filter((url) => url) as string[]

    if (props.multiple) {
      emit('update:modelValue', urls)
      emit('change', urls)
    } else {
      emit('update:modelValue', urls[0] || '')
      emit('change', urls[0] || '')
    }
  }
</script>

<style scoped lang="scss">
  .sa-image-upload {
    .upload-container {
      :deep(.el-upload) {
        border: 1px dashed var(--el-border-color);
        border-radius: 6px;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: var(--el-transition-duration-fast);
        width: v-bind('width + "px"');
        height: v-bind('height + "px"');

        &:hover {
          border-color: var(--el-color-primary);
        }
      }

      :deep(.el-icon--close-tip) {
        display: none !important;
      }

      :deep(.el-upload-list--picture-card) {
        .el-upload-list__item {
          width: v-bind('width + "px"');
          height: v-bind('height + "px"');
          transition: all 0.3s;

          &:hover {
            transform: scale(1.05);
          }
        }
      }

      &.is-round {
        :deep(.el-upload) {
          border-radius: 50%;
        }

        :deep(.el-upload-list--picture-card) {
          .el-upload-list__item {
            border-radius: 50%;
          }
        }
      }

      &.hide-upload {
        :deep(.el-upload) {
          display: none;
        }
      }
    }

    .upload-trigger {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;

      .upload-icon {
        font-size: 28px;
        color: #8c939d;
        margin-bottom: 8px;
      }

      .upload-text {
        font-size: 14px;
        color: #606266;
      }
    }

    .el-upload__tip {
      font-size: 12px;
      color: #909399;
      margin-top: 7px;
      line-height: 1.5;
    }
  }
</style>
