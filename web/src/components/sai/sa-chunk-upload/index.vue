<template>
  <div class="sa-chunk-upload">
    <el-upload
      ref="uploadRef"
      :file-list="fileList"
      :limit="limit"
      :multiple="multiple"
      :accept="accept"
      :auto-upload="false"
      :on-change="handleFileChange"
      :on-remove="handleRemove"
      :on-exceed="handleExceed"
      :disabled="disabled || uploading"
      :drag="drag"
      class="upload-container"
    >
      <template #default>
        <div v-if="drag" class="upload-dragger">
          <el-icon class="upload-icon"><UploadFilled /></el-icon>
          <div class="upload-text">将文件拖到此处，或<em>点击选择</em></div>
          <div class="upload-hint">支持大文件上传，自动分片处理</div>
        </div>
        <el-button v-else type="primary" :icon="Upload" :disabled="disabled || uploading">
          {{ buttonText }}
        </el-button>
      </template>
      <template #tip>
        <div class="el-upload__tip">
          <span v-if="acceptHint">支持 {{ acceptHint }} 格式，</span>
          单个文件不超过 {{ maxSize }}MB，最多上传 {{ limit }} 个文件
          <span v-if="chunkSize">（分片大小: {{ chunkSize }}MB）</span>
        </div>
      </template>
    </el-upload>

    <!-- 上传进度 -->
    <div v-if="uploadingFiles.length > 0" class="upload-progress-list">
      <div v-for="item in uploadingFiles" :key="item.uid" class="upload-progress-item">
        <div class="file-info">
          <el-icon class="file-icon"><Document /></el-icon>
          <span class="file-name">{{ item.name }}</span>
          <span class="file-size">{{ formatFileSize(item.size) }}</span>
        </div>
        <div class="progress-bar">
          <el-progress
            :percentage="item.progress"
            :status="getProgressStatus(item.status)"
            :stroke-width="8"
          />
        </div>
        <div class="progress-info">
          <span class="progress-text">
            {{ item.currentChunk }}/{{ item.totalChunks }} 分片
            <span v-if="item.speed"> - {{ item.speed }}</span>
          </span>
          <div class="action-buttons">
            <el-button
              v-if="item.status === 'exception'"
              type="text"
              size="small"
              @click="retryUpload(item)"
            >
              重试
            </el-button>
            <el-button
              v-else-if="item.status !== 'success'"
              type="text"
              size="small"
              @click="cancelUpload(item)"
            >
              取消
            </el-button>
            <el-button type="text" size="small" @click="removeUploadingFile(item)">
              删除
            </el-button>
          </div>
        </div>
      </div>
    </div>

    <!-- 开始上传按钮 -->
    <div v-if="pendingFiles.length > 0 && !uploading" class="upload-actions">
      <el-button type="primary" @click="startUpload">
        开始上传 ({{ pendingFiles.length }} 个文件)
      </el-button>
      <el-button @click="clearPending">清空列表</el-button>
    </div>
  </div>
</template>

<script lang="ts" setup>
  import { ref, computed } from 'vue'
  import { Upload, UploadFilled, Document } from '@element-plus/icons-vue'
  import { ElMessage } from 'element-plus'
  import type { UploadProps, UploadUserFile, UploadFile } from 'element-plus'
  import { chunkUpload } from '@/api/auth'
  import SparkMD5 from 'spark-md5'

  defineOptions({ name: 'SaChunkUpload' })

  // 定义 Props
  interface Props {
    modelValue?: string | string[] // v-model 绑定值
    multiple?: boolean // 是否支持多选
    limit?: number // 最大上传数量
    maxSize?: number // 最大文件大小(MB)
    chunkSize?: number // 分片大小(MB)，默认 5MB
    accept?: string // 接受的文件类型
    acceptHint?: string // 接受文件类型的提示文本
    disabled?: boolean // 是否禁用
    drag?: boolean // 是否启用拖拽上传
    buttonText?: string // 按钮文本
    autoUpload?: boolean // 是否自动上传
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
    multiple: false,
    limit: 1,
    maxSize: 1024, // 默认最大 1GB
    chunkSize: 5, // 默认 5MB 分片
    accept: '*',
    acceptHint: '',
    disabled: false,
    drag: true,
    buttonText: '选择文件',
    autoUpload: false
  })

  // 定义 Emits
  const emit = defineEmits<{
    'update:modelValue': [value: string | string[]]
    success: [response: any]
    error: [error: any]
    progress: [progress: number]
  }>()

  // 上传文件信息接口
  interface UploadingFile {
    uid: number
    name: string
    size: number
    file: File
    progress: number
    status: 'ready' | 'uploading' | 'success' | 'exception'
    currentChunk: number
    totalChunks: number
    speed: string
    hash?: string
    uploadedChunks: Set<number>
    canceled: boolean
  }

  // 状态
  const uploadRef = ref()
  const fileList = ref<UploadUserFile[]>([])
  const uploadingFiles = ref<UploadingFile[]>([])
  const uploading = ref(false)
  const ext = ref<string | Blob>('')

  // 待上传文件
  const pendingFiles = computed(() => {
    return uploadingFiles.value.filter((f) => f.status === 'ready')
  })

  // 将上传状态映射到进度条状态
  const getProgressStatus = (status: 'ready' | 'uploading' | 'success' | 'exception') => {
    if (status === 'success') return 'success'
    if (status === 'exception') return 'exception'
    return undefined // ready 和 uploading 使用默认状态
  }

  // 监听 modelValue 变化，同步组件状态
  watch(
    () => props.modelValue,
    (newVal) => {
      // 如果 modelValue 被清空（表单重置），清空所有状态
      if (!newVal || (Array.isArray(newVal) && newVal.length === 0)) {
        uploadingFiles.value = []
        fileList.value = []
        uploadRef.value?.clearFiles()
        return
      }

      // 如果 modelValue 有值，同步到 fileList（用于编辑场景）
      const urls = Array.isArray(newVal) ? newVal : [newVal]
      const existingUrls = fileList.value.map((f) => f.url)

      // 只添加新的 URL，避免重复
      urls
        .filter((url) => url && !existingUrls.includes(url))
        .forEach((url, index) => {
          const fileName = url.split('/').pop() || `file-${index + 1}`
          fileList.value.push({
            name: fileName,
            url: url,
            uid: Date.now() + index
          })
        })
    },
    { immediate: true }
  )

  // 文件选择变化
  const handleFileChange: UploadProps['onChange'] = (uploadFile: UploadFile) => {
    const file = uploadFile.raw
    if (!file) return

    // 验证文件大小
    const isLtMaxSize = file.size / 1024 / 1024 < props.maxSize
    if (!isLtMaxSize) {
      ElMessage.error(`文件大小不能超过 ${props.maxSize}MB!`)
      return
    }

    ext.value = '' + file.name.split('.').pop()?.toLowerCase()

    // 验证文件类型
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
        return
      }
    }

    // 计算分片数量
    const chunkSizeBytes = props.chunkSize * 1024 * 1024
    const totalChunks = Math.ceil(file.size / chunkSizeBytes)

    // 添加到上传列表
    const uploadingFile: UploadingFile = {
      uid: uploadFile.uid,
      name: file.name,
      size: file.size,
      file: file,
      progress: 0,
      status: 'ready',
      currentChunk: 0,
      totalChunks: totalChunks,
      speed: '',
      uploadedChunks: new Set(),
      canceled: false
    }

    uploadingFiles.value.push(uploadingFile)

    // 如果是自动上传，立即开始
    if (props.autoUpload) {
      startUpload()
    }
  }

  // 删除文件（从 el-upload 触发）
  const handleRemove: UploadProps['onRemove'] = (uploadFile) => {
    removeUploadingFile({ uid: uploadFile.uid } as UploadingFile)
  }

  // 删除上传中的文件
  const removeUploadingFile = (uploadingFile: UploadingFile) => {
    // 从 uploadingFiles 中删除
    const uploadingIndex = uploadingFiles.value.findIndex((item) => item.uid === uploadingFile.uid)
    if (uploadingIndex > -1) {
      uploadingFiles.value.splice(uploadingIndex, 1)
    }

    // 从 fileList 中删除
    const fileIndex = fileList.value.findIndex((item) => item.uid === uploadingFile.uid)
    if (fileIndex > -1) {
      fileList.value.splice(fileIndex, 1)
      updateModelValue()
    }

    // 如果所有文件都被删除，清空 el-upload 的内部状态
    if (uploadingFiles.value.length === 0 && fileList.value.length === 0) {
      uploadRef.value?.clearFiles()
    }
  }

  // 超出限制提示
  const handleExceed: UploadProps['onExceed'] = () => {
    ElMessage.warning(`最多只能上传 ${props.limit} 个文件`)
  }

  // 计算文件 MD5 哈希
  const calculateFileHash = (file: File): Promise<string> => {
    return new Promise((resolve, reject) => {
      const chunkSize = 2 * 1024 * 1024 // 2MB per chunk for hash calculation
      const chunks = Math.ceil(file.size / chunkSize)
      let currentChunk = 0
      const spark = new SparkMD5.ArrayBuffer()
      const fileReader = new FileReader()

      fileReader.onload = (e) => {
        spark.append(e.target?.result as ArrayBuffer)
        currentChunk++

        if (currentChunk < chunks) {
          loadNext()
        } else {
          resolve(spark.end())
        }
      }

      fileReader.onerror = () => {
        reject(new Error('文件读取失败'))
      }

      const loadNext = () => {
        const start = currentChunk * chunkSize
        const end = Math.min(start + chunkSize, file.size)
        fileReader.readAsArrayBuffer(file.slice(start, end))
      }

      loadNext()
    })
  }

  // 上传单个文件
  const uploadFile = async (uploadingFile: UploadingFile) => {
    try {
      uploadingFile.status = 'uploading'
      uploadingFile.canceled = false

      // 计算文件哈希
      const hash = await calculateFileHash(uploadingFile.file)
      uploadingFile.hash = hash

      const chunkSizeBytes = props.chunkSize * 1024 * 1024
      const totalChunks = uploadingFile.totalChunks
      const startTime = Date.now()

      let result: any = {}

      // 上传所有分片
      for (let i = 0; i < totalChunks; i++) {
        if (uploadingFile.canceled) {
          throw new Error('上传已取消')
        }

        const start = i * chunkSizeBytes
        const end = Math.min(start + chunkSizeBytes, uploadingFile.size)
        const chunk = uploadingFile.file.slice(start, end)

        // 创建 FormData
        const formData = new FormData()
        formData.append('file', chunk)
        formData.append('ext', ext.value)
        formData.append('size', uploadingFile.size.toString())
        formData.append('type', uploadingFile.file.type)
        formData.append('hash', hash)
        formData.append('index', i.toString())
        formData.append('total', totalChunks.toString())
        formData.append('name', uploadingFile.name)

        // 上传分片
        result = await chunkUpload(formData)

        // 检查后端是否返回了 URL（秒传：文件已存在）
        if (i == 0 && result?.url) {
          // 文件已存在，直接使用返回的 URL，跳过后续分片上传
          uploadingFile.progress = 100
          uploadingFile.currentChunk = totalChunks
          uploadingFile.speed = '秒传'
          emit('progress', 100)
          ElMessage.success(`${uploadingFile.name} 秒传成功！`)
          break
        }

        // 更新进度
        uploadingFile.currentChunk = i + 1
        uploadingFile.uploadedChunks.add(i)
        uploadingFile.progress = Math.floor(((i + 1) / totalChunks) * 100)

        // 计算上传速度
        const elapsed = (Date.now() - startTime) / 1000
        const uploaded = (i + 1) * chunkSizeBytes
        const speed = uploaded / elapsed
        uploadingFile.speed = formatSpeed(speed)

        // 触发进度事件
        emit('progress', uploadingFile.progress)
      }

      // 上传完成
      uploadingFile.status = 'success'
      uploadingFile.progress = 100

      // 获取文件 URL（支持秒传和正常上传两种情况）
      const fileUrl = result?.url || ''

      if (!fileUrl) {
        throw new Error('上传完成但未返回文件地址')
      }

      // 更新文件列表
      const newFile: UploadUserFile = {
        name: uploadingFile.name,
        url: fileUrl,
        uid: uploadingFile.uid
      }
      fileList.value.push(newFile)
      updateModelValue()

      emit('success', { url: fileUrl, hash })

      // 如果不是秒传，显示普通上传成功消息
      if (uploadingFile.speed !== '秒传') {
        ElMessage.success(`${uploadingFile.name} 上传成功！`)
      }
    } catch (error: any) {
      console.error('上传失败:', error)
      uploadingFile.status = 'exception'
      emit('error', error)
      ElMessage.error(`${uploadingFile.name} 上传失败: ${error.message}`)
    }
  }

  // 开始上传
  const startUpload = async () => {
    if (pendingFiles.value.length === 0) {
      ElMessage.warning('没有待上传的文件')
      return
    }

    uploading.value = true

    try {
      // 串行上传所有文件
      for (const file of pendingFiles.value) {
        if (!file.canceled) {
          await uploadFile(file)
        }
      }
    } finally {
      uploading.value = false
    }
  }

  // 重试上传
  const retryUpload = async (uploadingFile: UploadingFile) => {
    uploadingFile.status = 'ready'
    uploadingFile.progress = 0
    uploadingFile.currentChunk = 0
    uploadingFile.uploadedChunks.clear()
    await uploadFile(uploadingFile)
  }

  // 取消上传
  const cancelUpload = (uploadingFile: UploadingFile) => {
    uploadingFile.canceled = true
    uploadingFile.status = 'exception'
    ElMessage.info(`已取消上传: ${uploadingFile.name}`)
  }

  // 清空待上传列表
  const clearPending = () => {
    uploadingFiles.value = uploadingFiles.value.filter((f) => f.status !== 'ready')
    fileList.value = []
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

  // 格式化文件大小
  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 B'
    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i]
  }

  // 格式化速度
  const formatSpeed = (bytesPerSecond: number): string => {
    return formatFileSize(bytesPerSecond) + '/s'
  }
</script>

<style scoped lang="scss">
  .sa-chunk-upload {
    width: 100%;

    .upload-container {
      :deep(.el-upload) {
        width: 100%;
      }

      :deep(.el-upload-dragger) {
        width: 100%;
        padding: 20px 10px;
      }

      :deep(.el-upload-list) {
        display: none; // 隐藏默认的文件列表，使用自定义进度显示
      }
    }

    .upload-dragger {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;

      .upload-icon {
        font-size: 36px;
        color: #c0c4cc;
        margin-bottom: 16px;
      }

      .upload-text {
        font-size: 14px;
        color: #606266;
        margin-bottom: 8px;

        em {
          color: var(--el-color-primary);
          font-style: normal;
        }
      }

      .upload-hint {
        font-size: 12px;
        color: #909399;
      }
    }

    .el-upload__tip {
      font-size: 12px;
      color: #909399;
      margin-top: 7px;
      line-height: 1.5;
    }

    .upload-progress-list {
      margin-top: 20px;

      .upload-progress-item {
        padding: 15px;
        background-color: #f5f7fa;
        border-radius: 4px;
        margin-bottom: 10px;

        .file-info {
          display: flex;
          align-items: center;
          margin-bottom: 10px;

          .file-icon {
            font-size: 20px;
            color: #409eff;
            margin-right: 8px;
          }

          .file-name {
            flex: 1;
            font-size: 14px;
            color: #303133;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
          }

          .file-size {
            font-size: 12px;
            color: #909399;
            margin-left: 10px;
          }
        }

        .progress-bar {
          margin-bottom: 8px;
        }

        .progress-info {
          display: flex;
          justify-content: space-between;
          align-items: center;

          .progress-text {
            font-size: 12px;
            color: #606266;
          }

          .action-buttons {
            display: flex;
            gap: 5px;
          }
        }
      }
    }

    .upload-actions {
      margin-top: 15px;
      display: flex;
      gap: 10px;
    }
  }
</style>
