<template>
  <el-drawer v-model="visible" title="预览代码" size="100%" destroy-on-close @close="handleClose">
    <el-tabs v-model="activeTab" type="card">
      <el-tab-pane
        v-for="item in previewCode"
        :key="item.name"
        :label="item.tab_name"
        :name="item.name"
      >
        <div class="relative">
          <SaCode :code="item.code" :language="item.lang" />
          <el-button class="copy-button" type="primary" @click="handleCopy(item.code)">
            <template #icon>
              <ArtSvgIcon icon="ri:file-copy-line" />
            </template>
            复制
          </el-button>
        </div>
      </el-tab-pane>
    </el-tabs>
  </el-drawer>
</template>

<script setup lang="ts">
  import { useClipboard } from '@vueuse/core'
  import { ElMessage } from 'element-plus'
  import generate from '@/api/tool/generate'

  interface Props {
    modelValue: boolean
    data?: Record<string, any>
  }

  interface Emits {
    (e: 'update:modelValue', value: boolean): void
    (e: 'success'): void
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    data: undefined
  })

  const emit = defineEmits<Emits>()

  const activeTab = ref('controller')
  const previewCode = ref<any[]>([])

  /**
   * 弹窗显示状态双向绑定
   */
  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

  /**
   * 监听弹窗打开，初始化表单数据
   */
  watch(
    () => props.modelValue,
    (newVal) => {
      if (newVal) {
        initPage()
      }
    }
  )

  /**
   * 打开弹窗
   */
  const initPage = async () => {
    try {
      const response = await generate.preview({ id: props.data?.id })
      previewCode.value = response
      activeTab.value = previewCode.value[0]?.name || 'controller'
    } catch (error) {
      console.error(error)
      handleClose()
    }
  }

  /**
   * 关闭弹窗
   */
  const handleClose = () => {
    visible.value = false
  }

  /**
   * 复制代码到剪贴板
   */
  const { copy } = useClipboard()
  const handleCopy = async (code: string) => {
    try {
      await copy(code)
      ElMessage.success('代码已复制到剪贴板')
    } catch {
      ElMessage.error('复制失败，请手动复制')
    }
  }
</script>

<style lang="scss" scoped>
  .copy-button {
    position: absolute;
    right: 15px;
    top: 0px;
    z-index: 999;
  }
</style>
