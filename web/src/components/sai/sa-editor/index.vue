<!-- WangEditor 富文本编辑器 插件地址：https://www.wangeditor.com/ -->
<template>
  <div class="editor-wrapper">
    <div class="editor-toolbar-wrapper">
      <Toolbar
        class="editor-toolbar"
        :editor="editorRef"
        :mode="mode"
        :defaultConfig="toolbarConfig"
      />
      <!-- 自定义图库选择按钮 -->
      <el-tooltip content="从图库选择" placement="top">
        <el-button class="gallery-btn" :icon="FolderOpened" @click="openImageDialog" />
      </el-tooltip>
    </div>
    <Editor
      :style="{ height: height, overflowY: 'hidden' }"
      v-model="modelValue"
      :mode="mode"
      :defaultConfig="editorConfig"
      @onCreated="onCreateEditor"
    />

    <!-- 图片选择弹窗 -->
    <SaImageDialog
      v-model:visible="imageDialogVisible"
      :multiple="true"
      :limit="10"
      @confirm="onImageSelect"
    />
  </div>
</template>

<script setup lang="ts">
  import '@wangeditor/editor/dist/css/style.css'
  import { onBeforeUnmount, onMounted, shallowRef, computed, ref } from 'vue'
  import { Editor, Toolbar } from '@wangeditor/editor-for-vue'
  import EmojiText from '@/utils/ui/emojo'
  import { IDomEditor, IToolbarConfig, IEditorConfig } from '@wangeditor/editor'
  import { uploadImage } from '@/api/auth'
  import { FolderOpened } from '@element-plus/icons-vue'
  import SaImageDialog from '@/components/sai/sa-image-dialog/index.vue'

  defineOptions({ name: 'SaEditor' })

  // Props 定义
  interface Props {
    /** 编辑器高度 */
    height?: string
    /** 自定义工具栏配置 */
    toolbarKeys?: string[]
    /** 插入新工具到指定位置 */
    insertKeys?: { index: number; keys: string[] }
    /** 排除的工具栏项 */
    excludeKeys?: string[]
    /** 编辑器模式 */
    mode?: 'default' | 'simple'
    /** 占位符文本 */
    placeholder?: string
    /** 上传配置 */
    uploadConfig?: {
      maxFileSize?: number
      maxNumberOfFiles?: number
      server?: string
    }
  }

  const props = withDefaults(defineProps<Props>(), {
    height: '500px',
    mode: 'default',
    placeholder: '请输入内容...',
    excludeKeys: () => ['fontFamily']
  })

  const modelValue = defineModel<string>({ required: true })

  // 编辑器实例
  const editorRef = shallowRef<IDomEditor>()

  // 图片弹窗状态
  const imageDialogVisible = ref(false)

  // 常量配置
  const DEFAULT_UPLOAD_CONFIG = {
    maxFileSize: 3 * 1024 * 1024, // 3MB
    maxNumberOfFiles: 10,
    fieldName: 'file',
    allowedFileTypes: ['image/*']
  } as const

  // 合并上传配置
  const mergedUploadConfig = computed(() => ({
    ...DEFAULT_UPLOAD_CONFIG,
    ...props.uploadConfig
  }))

  // 工具栏配置
  const toolbarConfig = computed((): Partial<IToolbarConfig> => {
    const config: Partial<IToolbarConfig> = {}

    // 完全自定义工具栏
    if (props.toolbarKeys && props.toolbarKeys.length > 0) {
      config.toolbarKeys = props.toolbarKeys
    }

    // 插入新工具
    if (props.insertKeys) {
      config.insertKeys = props.insertKeys
    }

    // 排除工具
    if (props.excludeKeys && props.excludeKeys.length > 0) {
      config.excludeKeys = props.excludeKeys
    }

    return config
  })

  // 编辑器配置
  const editorConfig: Partial<IEditorConfig> = {
    placeholder: props.placeholder,
    MENU_CONF: {
      uploadImage: {
        // 自定义上传
        async customUpload(file: File, insertFn: (url: string, alt: string, href: string) => void) {
          try {
            // 验证文件大小
            if (file.size > mergedUploadConfig.value.maxFileSize) {
              const maxSizeMB = (mergedUploadConfig.value.maxFileSize / 1024 / 1024).toFixed(1)
              ElMessage.error(`图片大小不能超过 ${maxSizeMB}MB`)
              return
            }

            // 创建 FormData
            const formData = new FormData()
            formData.append('file', file)

            // 调用上传接口
            const response: any = await uploadImage(formData)

            // 获取图片 URL
            const imageUrl = response?.data?.url || response?.url || ''

            if (!imageUrl) {
              throw new Error('上传失败，未返回图片地址')
            }

            // 插入图片到编辑器
            insertFn(imageUrl, file.name, imageUrl)

            ElMessage.success(`图片上传成功 ${EmojiText[200]}`)
          } catch (error: any) {
            console.error('图片上传失败:', error)
            ElMessage.error(`图片上传失败: ${error.message || EmojiText[500]}`)
          }
        },

        // 其他配置
        maxFileSize: mergedUploadConfig.value.maxFileSize,
        maxNumberOfFiles: mergedUploadConfig.value.maxNumberOfFiles,
        allowedFileTypes: mergedUploadConfig.value.allowedFileTypes
      }
    }
  }

  // 打开图片选择弹窗
  const openImageDialog = () => {
    imageDialogVisible.value = true
  }

  // 图片选择回调
  const onImageSelect = (urls: string | string[]) => {
    const editor = editorRef.value
    if (!editor) return

    const urlList = Array.isArray(urls) ? urls : [urls]
    urlList.forEach((url) => {
      editor.insertNode({
        type: 'image',
        src: url,
        alt: '',
        href: '',
        style: {},
        children: [{ text: '' }]
      } as any)
    })
  }

  // 编辑器创建回调
  const onCreateEditor = (editor: IDomEditor) => {
    editorRef.value = editor

    // 监听全屏事件
    editor.on('fullScreen', () => {
      console.log('编辑器进入全屏模式')
    })

    // 确保在编辑器创建后应用自定义图标
    applyCustomIcons()
  }

  // 应用自定义图标（带重试机制）
  const applyCustomIcons = () => {
    let retryCount = 0
    const maxRetries = 10
    const retryDelay = 100

    const tryApplyIcons = () => {
      const editor = editorRef.value
      if (!editor) {
        if (retryCount < maxRetries) {
          retryCount++
          setTimeout(tryApplyIcons, retryDelay)
        }
        return
      }

      // 获取当前编辑器的工具栏容器
      const editorContainer = editor.getEditableContainer().closest('.editor-wrapper')
      if (!editorContainer) {
        if (retryCount < maxRetries) {
          retryCount++
          setTimeout(tryApplyIcons, retryDelay)
        }
        return
      }

      const toolbar = editorContainer.querySelector('.w-e-toolbar')
      const toolbarButtons = editorContainer.querySelectorAll('.w-e-bar-item button[data-menu-key]')

      if (toolbar && toolbarButtons.length > 0) {
        return
      }

      // 如果工具栏还没渲染完成，继续重试
      if (retryCount < maxRetries) {
        retryCount++
        setTimeout(tryApplyIcons, retryDelay)
      } else {
        console.warn('工具栏渲染超时，无法应用自定义图标 - 编辑器实例:', editor.id)
      }
    }

    // 使用 requestAnimationFrame 确保在下一帧执行
    requestAnimationFrame(tryApplyIcons)
  }

  // 暴露编辑器实例和方法
  defineExpose({
    /** 获取编辑器实例 */
    getEditor: () => editorRef.value,
    /** 设置编辑器内容 */
    setHtml: (html: string) => editorRef.value?.setHtml(html),
    /** 获取编辑器内容 */
    getHtml: () => editorRef.value?.getHtml(),
    /** 清空编辑器 */
    clear: () => editorRef.value?.clear(),
    /** 聚焦编辑器 */
    focus: () => editorRef.value?.focus(),
    /** 打开图库选择 */
    openImageDialog
  })

  // 生命周期
  onMounted(() => {
    // 图标替换已在 onCreateEditor 中处理
  })

  onBeforeUnmount(() => {
    const editor = editorRef.value
    if (editor) {
      editor.destroy()
    }
  })
</script>

<style lang="scss">
  @use './style';

  .editor-toolbar-wrapper {
    display: flex;
    align-items: center;
    border-bottom: 1px solid var(--el-border-color-light);

    .editor-toolbar {
      flex: 1;
      border-bottom: none !important;
    }

    .gallery-btn {
      margin: 0 8px;
      padding: 8px;
      height: 32px;
      width: 32px;
    }
  }
</style>
