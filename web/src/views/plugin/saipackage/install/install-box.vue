<template>
  <ElDialog v-model="visible" title="上传插件包-安装插件" width="800" :close-on-click-modal="false">
    <div class="flex flex-col items-center mb-6">
      <div class="w-[400px]">
        <div class="text-lg text-red-500 font-bold mb-2">
          请您务必确认模块包文件来自官方渠道或经由官方认证的模块作者，否则系统可能被破坏，因为：
        </div>
        <div class="text-red-500">1. 模块可以修改和新增系统文件</div>
        <div class="text-red-500">2. 模块可以执行sql命令和代码</div>
        <div class="text-red-500">3. 模块可以安装新的前后端依赖</div>
      </div>

      <!-- 已上传的应用信息 -->
      <div v-if="appInfo && appInfo.app" class="mt-10 w-[600px]">
        <ElDescriptions :column="1" border>
          <ElDescriptionsItem label="应用标识">{{ appInfo?.app }}</ElDescriptionsItem>
          <ElDescriptionsItem label="应用名称">{{ appInfo?.title }}</ElDescriptionsItem>
          <ElDescriptionsItem label="应用描述">{{ appInfo?.about }}</ElDescriptionsItem>
          <ElDescriptionsItem label="作者">{{ appInfo?.author }}</ElDescriptionsItem>
          <ElDescriptionsItem label="版本">{{ appInfo?.version }}</ElDescriptionsItem>
        </ElDescriptions>
      </div>

      <!-- 上传区域 -->
      <div v-else class="mt-10 w-[600px]">
        <ElUpload
          drag
          :http-request="uploadFileHandler"
          :show-file-list="false"
          accept=".zip,.rar"
          class="w-full"
        >
          <div class="flex flex-col items-center justify-center py-8">
            <ArtSvgIcon icon="ri:upload-cloud-line" class="text-4xl text-gray-400 mb-2" />
            <div class="text-gray-500">
              将插件包文件拖到此处，或
              <span class="text-primary ml-2">点击上传</span>
            </div>
          </div>
        </ElUpload>
      </div>
    </div>
  </ElDialog>
</template>

<script setup lang="ts">
  import { ref, reactive } from 'vue'
  import { ElMessage } from 'element-plus'
  import type { UploadRequestOptions } from 'element-plus'
  import saipackageApi, { type AppInfo } from '../api/index'

  const emit = defineEmits<{
    (e: 'success'): void
  }>()

  const visible = ref(false)
  const loading = ref(false)

  const uploadSize = 8 * 1024 * 1024

  const initialApp: AppInfo = {
    app: '',
    title: '',
    about: '',
    author: '',
    version: '',
    state: 0
  }

  const appInfo = reactive<AppInfo>({ ...initialApp })

  const uploadFileHandler = async (options: UploadRequestOptions) => {
    const file = options.file
    if (!file) return

    if (file.size > uploadSize) {
      ElMessage.warning(file.name + '超出文件大小限制(8MB)')
      return
    }

    loading.value = true
    try {
      const dataForm = new FormData()
      dataForm.append('file', file)

      const res = await saipackageApi.uploadApp(dataForm)
      if (res) {
        Object.assign(appInfo, res)
        ElMessage.success('上传成功')
        emit('success')
      }
    } catch {
      // Error already handled by http utility
    } finally {
      loading.value = false
    }
  }

  const open = () => {
    visible.value = true
    Object.assign(appInfo, initialApp)
  }

  defineExpose({ open })
</script>
