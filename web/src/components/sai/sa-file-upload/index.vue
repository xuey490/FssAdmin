<template>
  <div class="sa-file-upload">
    <el-upload
      ref="uploadRef"
      :file-list="fileList"
      :limit="limit"
      :multiple="multiple"
      :accept="accept"
      :http-request="handleUpload"
      :before-upload="beforeUpload"
      :on-remove="handleRemove"
      :on-preview="handlePreview"
      :on-exceed="handleExceed"
      :disabled="disabled"
      :drag="drag"
      class="upload-container"
    >
      <template #default>
        <div v-if="drag" class="upload-dragger">
          <el-icon class="upload-icon"><UploadFilled /></el-icon>
          <div class="upload-text">将文件拖到此处，或<em>点击上传</em></div>
        </div>
        <el-button v-else type="primary" :icon="Upload">
          {{ buttonText }}
        </el-button>
      </template>
      <template #tip>
        <div class="el-upload__tip">
          <span v-if="acceptHint">支持 {{ acceptHint }} 格式，</span>
          单个文件不超过 {{ maxSize }}MB，最多上传 {{ limit }} 个文件
        </div>
      </template>
    </el-upload>
  </div>
</template>

<script lang="ts" setup>
  import { ref, watch } from 'vue'
  import { Upload, UploadFilled } from '@element-plus/icons-vue'
  import { ElMessage } from 'element-plus'
  import type { UploadProps, UploadUserFile, UploadRequestOptions } from 'element-plus'
  import { uploadFile } from '@/api/auth'

  defineOptions({ name: 'SaFileUpload' })

  // 定义 Props
  interface Props {
    modelValue?: string | string[] // v-model 绑定值
    multiple?: boolean // 是否支持多选
    limit?: number // 最大上传数量
    maxSize?: number // 最大文件大小(MB)
    accept?: string // 接受的文件类型
    acceptHint?: string // 接受文件类型的提示文本
    disabled?: boolean // 是否禁用
    drag?: boolean // 是否启用拖拽上传
    buttonText?: string // 按钮文本
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
    multiple: false,
    limit: 1,
    maxSize: 10,
    accept: '*',
    acceptHint: '',
    disabled: false,
    drag: false,
    buttonText: '选择文件'
  })

  // 定义 Emits
  const emit = defineEmits<{
    'update:modelValue': [value: string | string[]]
    success: [response: any]
    error: [error: any]
  }>()

  // 状态
  const uploadRef = ref()
  const fileList = ref<UploadUserFile[]>([])

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
        .map((url, index) => {
          // 从 URL 中提取文件名
          const fileName = url.split('/').pop() || `file-${index + 1}`
          return {
            name: fileName,
            url: url,
            uid: Date.now() + index
          }
        })
    },
    { immediate: true }
  )

  // 上传前验证
  const beforeUpload: UploadProps['beforeUpload'] = (file) => {
    // 验证文件类型（如果指定了 accept）
    if (props.accept && props.accept !== '*') {
      const acceptTypes = props.accept.split(',').map((type) => type.trim())
      const fileExtension = '.' + file.name.split('.').pop()?.toLowerCase()
      const fileMimeType = file.type

      const isAccepted = acceptTypes.some((type) => {
        if (type.startsWith('.')) {
          return fileExtension === type.toLowerCase()
        }
        if (type.includes('/*')) {
          const mainType = type.split('/')[0]
          return fileMimeType.startsWith(mainType)
        }
        return fileMimeType === type
      })

      if (!isAccepted) {
        ElMessage.error(
          `不支持的文件类型！${props.acceptHint ? '请上传 ' + props.acceptHint + ' 格式的文件' : ''}`
        )
        return false
      }
    }

    // 验证文件大小
    const isLtMaxSize = file.size / 1024 / 1024 < props.maxSize
    if (!isLtMaxSize) {
      ElMessage.error(`文件大小不能超过 ${props.maxSize}MB!`)
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
      const response: any = await uploadFile(formData)

      // 兼容后端返回 { url } 对象或直接返回 url 字符串
      const fileUrl = response?.url || (typeof response === 'string' ? response : '')

      if (!fileUrl) {
        throw new Error('上传失败，未返回文件地址')
      }

      // 用返回的真实 url 替换 el-upload 生成的临时 blob url
      const target = fileList.value.find((item) => item.uid === file.uid)
      if (target) {
        target.url = fileUrl
      } else {
        fileList.value.push({ name: file.name, url: fileUrl, uid: file.uid })
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
    ElMessage.warning(`最多只能上传 ${props.limit} 个文件，请先删除已有文件后再上传`)
  }

  // 预览文件
  const handlePreview: UploadProps['onPreview'] = (file) => {
    if (file.url) {
      // 在新窗口打开文件
      window.open(file.url, '_blank')
    }
  }

  // 更新 v-model 值
  const updateModelValue = () => {
    const urls = fileList.value.map((file) => file.url).filter((url) => url) as string[]

    if (props.multiple) {
      emit('update:modelValue', urls)
    } else {
      emit('update:modelValue', urls[0] || '')
    }
  }
</script>

<style scoped lang="scss">
  .sa-file-upload {
    width: 100%;
    .upload-container {
      :deep(.el-upload) {
        width: 250px;
        justify-content: start;
      }

      :deep(.el-upload-dragger) {
        width: 250px;
        padding: 20px 10px;
      }

      :deep(.el-upload-list) {
        margin-top: 10px;
      }
    }

    .upload-dragger {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;

      .upload-icon {
        font-size: 48px;
        color: #c0c4cc;
        margin-bottom: 16px;
      }

      .upload-text {
        font-size: 14px;
        color: #606266;

        em {
          color: var(--el-color-primary);
          font-style: normal;
        }
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
