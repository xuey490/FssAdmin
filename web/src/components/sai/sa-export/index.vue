<template>
  <div class="sa-export-wrap" @click="handleExport">
    <slot>
      <ElButton :icon="Download" :loading="loading">
        {{ label }}
      </ElButton>
    </slot>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { Download } from '@element-plus/icons-vue'
  import { ElMessage, ElMessageBox } from 'element-plus'
  import axios from 'axios'
  import { useUserStore } from '@/store/modules/user'

  defineOptions({ name: 'SaExport' })

  const props = withDefaults(
    defineProps<{
      url: string
      params?: Record<string, any>
      fileName?: string
      method?: string
      label?: string
    }>(),
    {
      method: 'post',
      label: '导出',
      fileName: '导出数据.xlsx'
    }
  )

  const emit = defineEmits<{
    success: []
    error: [error: any]
  }>()

  const loading = ref(false)

  const handleExport = async () => {
    if (loading.value) return
    if (!props.url) {
      ElMessage.error('未配置导出接口')
      return
    }

    let finalFileName = props.fileName

    try {
      const { value } = await ElMessageBox.prompt('请输入导出文件名称', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        inputValue: props.fileName,
        inputValidator: (val) => !!val.trim() || '文件名不能为空'
      })
      finalFileName = value
    } catch {
      // User cancelled
      return
    }

    try {
      loading.value = true
      const { VITE_API_URL } = import.meta.env
      const { accessToken } = useUserStore()

      axios.defaults.baseURL = VITE_API_URL

      const config = {
        method: props.method,
        url: props.url,
        data: props.method.toLowerCase() === 'post' ? props.params : undefined,
        params: props.method.toLowerCase() === 'get' ? props.params : undefined,
        responseType: 'blob' as const,
        headers: {
          Authorization: accessToken ? `Bearer ${accessToken}` : undefined
        }
      }

      const res = await axios(config)

      // Check if response is json (error case)
      if (res.data.type === 'application/json') {
        const reader = new FileReader()
        reader.onload = () => {
          try {
            const result = JSON.parse(reader.result as string)
            ElMessage.error(result.msg || '导出失败')
            emit('error', result)
          } catch (e) {
            ElMessage.error('导出失败')
            emit('error', e)
          }
        }
        reader.readAsText(res.data)
        return
      }

      const blob = new Blob([res.data], { type: 'application/octet-stream' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = finalFileName
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      URL.revokeObjectURL(url)

      ElMessage.success('导出成功')
      emit('success')
    } catch (error: any) {
      console.error(error)
      ElMessage.error(error.message || '导出失败')
      emit('error', error)
    } finally {
      loading.value = false
    }
  }
</script>

<style scoped>
  .sa-export-wrap {
    display: inline-block;
  }
</style>
