<!-- Markdown编辑器封装 -->
<template>
  <div style="width: 100%">
    <MdEditor
      ref="editorRef"
      v-model="modelValue"
      :theme="theme"
      previewTheme="github"
      :toolbars="toolbars"
      :preview="preview"
      :style="{ height: height, minHeight: minHeight }"
    >
      <template #defToolbars>
        <NormalToolbar title="图片" @onClick="openImageDialog">
          <template #trigger>
            <el-icon><Picture /></el-icon>
          </template>
        </NormalToolbar>
      </template>
    </MdEditor>

    <SaImageDialog
      v-model:visible="imageDialogVisible"
      :multiple="true"
      :limit="10"
      @confirm="onImageSelect"
    />
  </div>
</template>

<script setup lang="ts">
  import { ref, computed } from 'vue'
  import { MdEditor, NormalToolbar } from 'md-editor-v3'
  import type { ExposeParam } from 'md-editor-v3'
  import 'md-editor-v3/lib/style.css'
  import { useSettingStore } from '@/store/modules/setting'
  import { Picture } from '@element-plus/icons-vue'
  import SaImageDialog from '@/components/sai/sa-image-dialog/index.vue'

  defineOptions({ name: 'SaMdEditor' })

  const settingStore = useSettingStore()

  interface Props {
    height?: string
    preview?: boolean
    minHeight?: string
  }

  withDefaults(defineProps<Props>(), {
    height: '500px',
    minHeight: '500px',
    preview: true
  })

  const modelValue = defineModel<string>({ default: '' })
  const editorRef = ref<ExposeParam>()

  // 主题处理
  const theme = computed(() => (settingStore.isDark ? 'dark' : 'light'))

  // 图片弹窗
  const imageDialogVisible = ref(false)
  const openImageDialog = () => {
    imageDialogVisible.value = true
  }

  const onImageSelect = (urls: string | string[]) => {
    const urlList = Array.isArray(urls) ? urls : [urls]
    const markdownImages = urlList.map((url) => `![](${url})`).join('\n')

    editorRef.value?.insert(() => {
      // 插入图片并配置光标位置
      return {
        targetValue: markdownImages,
        select: true,
        deviationStart: 0,
        deviationEnd: 0
      }
    })
  }

  const toolbars = [
    'bold',
    'underline',
    'italic',
    '-',
    'title',
    'strikeThrough',
    'sub',
    'sup',
    'quote',
    'unorderedList',
    'orderedList',
    'task',
    '-',
    'codeRow',
    'code',
    'link',
    0,
    'table',
    'mermaid',
    'katex',
    '-',
    'revoke',
    'next',
    '=',
    'pageFullscreen',
    'preview',
    'previewOnly',
    'catalog'
  ] as any[]
</script>
