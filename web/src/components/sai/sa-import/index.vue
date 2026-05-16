<template>
  <div class="sa-import-wrap" @click="open">
    <div class="trigger">
      <slot>
        <ElButton :icon="Upload">
          {{ title }}
        </ElButton>
      </slot>
    </div>

    <el-dialog
      v-model="visible"
      :title="title"
      :width="width"
      append-to-body
      destroy-on-close
      class="sa-import-dialog"
      :close-on-click-modal="false"
    >
      <div class="import-container">
        <el-upload
          ref="uploadRef"
          class="upload-area"
          drag
          action="#"
          :accept="accept"
          :limit="1"
          :auto-upload="true"
          :on-exceed="handleExceed"
          :on-remove="handleRemove"
          v-model:file-list="fileList"
          :http-request="customUpload"
        >
          <el-icon class="el-icon--upload"><UploadFilled /></el-icon>
          <div class="el-upload__text"> 将文件拖到此处，或 <em>点击上传</em> </div>
          <template #tip>
            <div class="el-upload__tip">
              {{ tip || `请上传 ${accept.replace(/,/g, '/')} 格式文件` }}
            </div>
          </template>
        </el-upload>

        <div class="template-download" v-if="showTemplate">
          <el-link type="primary" :underline="false" @click="downloadTemplate">
            <el-icon class="el-icon--left"><Download /></el-icon> 下载导入模板
          </el-link>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
  import axios from 'axios'
  import { ref, computed } from 'vue'
  import { useUserStore } from '@/store/modules/user'
  import { UploadFilled, Upload, Download } from '@element-plus/icons-vue'
  import { ElMessage, genFileId } from 'element-plus'
  import type {
    UploadInstance,
    UploadProps,
    UploadRawFile,
    UploadUserFile,
    UploadRequestOptions
  } from 'element-plus'

  defineOptions({ name: 'SaImport' })

  const props = withDefaults(
    defineProps<{
      title?: string
      width?: string | number
      uploadUrl?: string
      downloadUrl?: string
      accept?: string
      tip?: string
      data?: Record<string, any>
    }>(),
    {
      title: '导入',
      width: '600px',
      accept: '.xlsx,.xls'
    }
  )

  const emit = defineEmits<{
    success: [response: any]
    error: [error: any]
    'download-template': []
  }>()

  const visible = ref(false)
  const loading = ref(false)
  const uploadRef = ref<UploadInstance>()
  const fileList = ref<UploadUserFile[]>([])

  const showTemplate = computed(() => {
    return props.downloadUrl
  })

  const open = () => {
    visible.value = true
    fileList.value = []
  }

  const handleExceed: UploadProps['onExceed'] = (files) => {
    uploadRef.value!.clearFiles()
    const file = files[0] as UploadRawFile
    file.uid = genFileId()
    uploadRef.value!.handleStart(file)
  }

  const handleRemove = () => {
    fileList.value = []
  }

  const customUpload = async (options: UploadRequestOptions) => {
    if (!props.uploadUrl) {
      ElMessage.error('未配置上传接口')
      options.onError('未配置上传接口' as any)
      return
    }

    try {
      loading.value = true
      const formData = new FormData()
      formData.append('file', options.file)

      if (props.data) {
        Object.keys(props.data).forEach((key) => {
          formData.append(key, props.data![key])
        })
      }

      const { VITE_API_URL } = import.meta.env
      const { accessToken } = useUserStore()

      axios.defaults.baseURL = VITE_API_URL
      const res = await axios.post(props.uploadUrl, formData, {
        headers: {
          Authorization: `Bearer ` + accessToken,
          'Content-Type': 'multipart/form-data'
        }
      })

      ElMessage.success(res?.data?.msg || '导入成功')
      emit('success', res.data)
      visible.value = false
    } catch (error: any) {
      console.error(error)
      ElMessage.error(error?.response?.data?.msg || '导入失败')
      emit('error', error)
    } finally {
      loading.value = false
    }
  }

  const downloadTemplate = async () => {
    if (props.downloadUrl) {
      try {
        const { VITE_API_URL } = import.meta.env
        const { accessToken } = useUserStore()

        axios.defaults.baseURL = VITE_API_URL

        const config = {
          method: 'post',
          url: props.downloadUrl,
          data: props.data,
          responseType: 'blob' as const,
          headers: {
            Authorization: accessToken ? `Bearer ${accessToken}` : undefined
          }
        }

        const res = await axios(config)
        const blob = new Blob([res.data], { type: 'application/octet-stream' })
        const url = URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = '导入模板.xlsx'
        document.body.appendChild(a)
        a.click()
        document.body.removeChild(a)
        URL.revokeObjectURL(url)
      } catch (error) {
        console.error(error)
        ElMessage.error('下载模板失败')
      }
    } else {
      emit('download-template')
    }
  }

  defineExpose({
    open
  })
</script>

<style scoped lang="scss">
  .sa-import-wrap {
    display: inline-block;
  }

  .import-container {
    padding: 10px 0;
    position: relative;

    .upload-area {
      :deep(.el-upload-dragger) {
        width: 100%;
      }
    }

    .template-download {
      margin-top: 10px;
      text-align: right;
      display: flex;
      justify-content: flex-end;
      align-items: center;
    }
  }
</style>
