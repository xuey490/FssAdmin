<template>
  <div class="flex items-center">
    <span class="label">{{ props.label }}</span>
    <el-tooltip :content="props.tooltip" :placement="props.placement">
      <el-icon class="ml-0.5">
        <QuestionFilled />
      </el-icon>
    </el-tooltip>
  </div>
</template>

<script lang="ts" setup>
  import { QuestionFilled } from '@element-plus/icons-vue'

  defineOptions({ name: 'SaLabel', inheritAttrs: false })

  interface Props {
    /** 标签内容 */
    label: string
    /** 提示内容 */
    tooltip?: string
    /** 提示位置 */
    placement?: 'top' | 'bottom' | 'left' | 'right'
  }

  const props = withDefaults(defineProps<Props>(), {
    tooltip: '',
    placement: 'top'
  })
</script>
