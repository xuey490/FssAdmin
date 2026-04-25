<template>
  <div class="sa-image-picker" :style="containerStyle">
    <!-- 多选模式下的图片列表 -->
    <div
      v-if="multiple && Array.isArray(selectedImage) && selectedImage.length > 0"
      class="image-list-display"
    >
      <div
        v-for="(url, index) in selectedImage"
        :key="url"
        class="picker-trigger mini"
        :class="{ round: round }"
      >
        <el-image :src="url" fit="cover" class="preview-image" />
        <div class="image-mask" :class="{ round: round }">
          <el-icon class="mask-icon" @click.stop="handlePreview(index)"><ZoomIn /></el-icon>
          <el-icon class="mask-icon" @click.stop="removeImage(index)"><Delete /></el-icon>
        </div>
      </div>
      <!-- 添加按钮 -->
      <div
        v-if="selectedImage.length < limit"
        class="picker-trigger mini add-btn"
        :class="{ round: round }"
        @click="openDialog"
      >
        <el-icon class="picker-icon"><Plus /></el-icon>
      </div>
    </div>

    <!-- 单选模式或空状态 -->
    <div v-else class="picker-trigger" :style="triggerStyle" :class="{ round: round }">
      <div
        v-if="!selectedImage || (Array.isArray(selectedImage) && selectedImage.length === 0)"
        class="empty-state"
        @click="openDialog"
      >
        <el-icon class="picker-icon"><Plus v-if="multiple" /><Picture v-else /></el-icon>
        <div class="picker-text">点击选择</div>
      </div>
      <div v-else class="selected-image">
        <el-image
          :src="Array.isArray(selectedImage) ? selectedImage[0] : selectedImage"
          fit="cover"
          class="preview-image"
          :class="{ round: round }"
        />
        <div class="image-mask" :class="{ round: round }">
          <el-icon class="mask-icon" @click.stop="handlePreview(0)"><ZoomIn /></el-icon>
          <el-icon class="mask-icon" @click.stop="openDialog"><Edit /></el-icon>
          <el-icon class="mask-icon" @click.stop="clearImage"><Delete /></el-icon>
        </div>
      </div>
    </div>

    <!-- 使用独立的图片选择弹窗组件 -->
    <SaImageDialog
      v-model:visible="dialogVisible"
      :multiple="multiple"
      :limit="limit"
      :initial-urls="modelValue"
      @confirm="onDialogConfirm"
    />

    <!-- 图片预览 -->
    <el-image-viewer
      v-if="previewVisible"
      :url-list="previewList"
      :initial-index="previewIndex"
      @close="closePreview"
    />
  </div>
</template>

<script lang="ts" setup>
  import { ref, computed, watch } from 'vue'
  import { Picture, Delete, Edit, ZoomIn, Plus } from '@element-plus/icons-vue'
  import SaImageDialog from '@/components/sai/sa-image-dialog/index.vue'

  defineOptions({ name: 'SaImagePicker' })

  // Props 定义
  interface Props {
    modelValue?: string | string[] // v-model 绑定值
    placeholder?: string // 占位符文本
    multiple?: boolean // 是否多选
    limit?: number // 多选限制
    round?: boolean // 是否圆角
    width?: string | number // 宽度
    height?: string | number // 高度
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    placeholder: '点击选择图片',
    multiple: false,
    limit: 3,
    round: false,
    width: '120px',
    height: '120px'
  })

  // 计算容器样式
  const containerStyle = computed(() => {
    return {
      width: typeof props.width === 'number' ? `${props.width}px` : props.width,
      height: typeof props.height === 'number' ? `${props.height}px` : props.height
    }
  })

  // 计算触发器样式
  const triggerStyle = computed(() => {
    return {
      width: '100%',
      height: '100%'
    }
  })

  // Emits 定义
  const emit = defineEmits<{
    'update:modelValue': [value: string | string[]]
    change: [value: string | string[]]
  }>()

  // 状态
  const dialogVisible = ref(false)
  const previewVisible = ref(false)
  const previewIndex = ref(0)
  const selectedImage = ref<string | string[]>(props.modelValue)

  // 监听 modelValue 变化
  watch(
    () => props.modelValue,
    (newVal) => {
      if (Array.isArray(newVal)) {
        selectedImage.value = [...newVal]
      } else {
        selectedImage.value = newVal
      }
    },
    { deep: true, immediate: true }
  )

  // 打开对话框
  const openDialog = () => {
    dialogVisible.value = true
  }

  // 弹窗确认回调
  const onDialogConfirm = (result: string | string[]) => {
    selectedImage.value = result
    emit('update:modelValue', result)
    emit('change', result)
  }

  // 清除图片
  const clearImage = () => {
    selectedImage.value = props.multiple ? [] : ''
    emit('update:modelValue', selectedImage.value)
    emit('change', selectedImage.value)
  }

  // 移除单个图片（多选模式）
  const removeImage = (index: number) => {
    if (Array.isArray(selectedImage.value)) {
      const newList = [...selectedImage.value]
      newList.splice(index, 1)
      selectedImage.value = newList
      emit('update:modelValue', newList)
      emit('change', newList)
    }
  }

  // 预览处理
  const handlePreview = (index: number = 0) => {
    if (selectedImage.value) {
      previewIndex.value = index
      previewVisible.value = true
    }
  }

  // 计算预览列表
  const previewList = computed(() => {
    if (Array.isArray(selectedImage.value)) {
      return selectedImage.value
    }
    return selectedImage.value ? [selectedImage.value] : []
  })

  const closePreview = () => {
    previewVisible.value = false
  }
</script>

<style scoped lang="scss">
  .sa-image-picker {
    width: 100%;
    height: 100%;

    .image-list-display {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .picker-trigger {
      border: 1px dashed var(--el-border-color);
      border-radius: 6px;
      cursor: pointer;
      transition: var(--el-transition-duration-fast);
      overflow: hidden;
      position: relative;
      box-sizing: border-box;

      &:hover {
        border-color: var(--el-color-primary);
      }

      &.mini {
        width: 60px;
        height: 60px;
      }

      &.round {
        border-radius: 50%;

        &.mini {
          border-radius: 50%;
        }
      }

      &.add-btn {
        display: flex;
        align-items: center;
        justify-content: center;

        .picker-icon {
          font-size: 28px;
          color: #8c939d;
        }
      }

      .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;

        &.round {
          border-radius: 50%;
        }

        .picker-icon {
          font-size: clamp(20px, 3vw, 28px);
          color: #8c939d;
          margin-bottom: 4px;
        }

        .picker-text {
          font-size: clamp(10px, 2vw, 12px);
          color: #606266;
        }
      }

      .selected-image {
        width: 100%;
        height: 100%;
        position: relative;

        &.round {
          border-radius: 50%;
        }

        .preview-image {
          width: 100%;
          height: 100%;

          &.round {
            border-radius: 50%;
          }
        }
      }

      .image-mask {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 10;

        &.round {
          border-radius: 50%;
        }

        .mask-icon {
          font-size: clamp(16px, 2vw, 20px);
          color: #fff;
          cursor: pointer;
          transition: transform 0.2s;

          &:hover {
            transform: scale(1.2);
          }
        }
      }

      &:hover .image-mask {
        opacity: 1;
      }
    }
  }
</style>
