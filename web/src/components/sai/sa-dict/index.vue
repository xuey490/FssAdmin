<!-- 字典组件 -->
<template>
  <div class="sa-dict-wrapper">
    <template v-if="render === 'tag'">
      <ElTag
        v-for="(item, index) in normalizedValues"
        :key="index"
        :size="size"
        :style="{
          backgroundColor: getColor(getData(item)?.color, 'bg'),
          borderColor: getColor(getData(item)?.color, 'border'),
          color: getColor(getData(item)?.color, 'text')
        }"
        :round="round"
        class="mr-1 last:mr-0"
      >
        {{ getData(item)?.label || item }}
      </ElTag>
    </template>
    <template v-else>
      <span v-for="(item, index) in normalizedValues" :key="index">
        {{ getData(item)?.label || item }}{{ index < normalizedValues.length - 1 ? '、' : '' }}
      </span>
    </template>
  </div>
</template>

<script setup lang="ts">
  import { useDictStore } from '@/store/modules/dict'

  defineOptions({ name: 'SaDict' })

  interface Props {
    /** 字典类型 */
    dict: string
    /** 字典值（支持字符串或数组） */
    value: string | string[] | number | number[]
    /** 渲染方式 */
    render?: string
    /** 标签大小 */
    size?: 'large' | 'default' | 'small'
    /** 是否圆角 */
    round?: boolean
  }

  const props = withDefaults(defineProps<Props>(), {
    render: 'tag',
    size: 'default',
    round: false
  })

  const dictStore = useDictStore()

  // 统一处理 value，转换为数组格式
  const normalizedValues = computed(() => {
    if (Array.isArray(props.value)) {
      return props.value.map((v) => String(v))
    }
    return props.value !== undefined && props.value !== null && props.value !== ''
      ? [String(props.value)]
      : []
  })

  // 根据值获取字典数据
  const getData = (value: string) => dictStore.getDataByValue(props.dict, value)

  const getColor = (color: string | undefined, type: 'bg' | 'border' | 'text') => {
    // 如果没有指定颜色，使用默认主色调
    if (!color) {
      const colors = {
        bg: 'var(--el-color-primary-light-9)',
        border: 'var(--el-color-primary-light-8)',
        text: 'var(--el-color-primary)'
      }
      return colors[type]
    }

    // 如果是 hex 颜色，转换为 RGB
    let r, g, b
    if (color.startsWith('#')) {
      const hex = color.slice(1)
      r = parseInt(hex.slice(0, 2), 16)
      g = parseInt(hex.slice(2, 4), 16)
      b = parseInt(hex.slice(4, 6), 16)
    } else if (color.startsWith('rgb')) {
      const match = color.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/)
      if (match) {
        r = parseInt(match[1])
        g = parseInt(match[2])
        b = parseInt(match[3])
      } else {
        return color
      }
    } else {
      return color
    }

    // 根据类型返回不同的颜色变体
    switch (type) {
      case 'bg':
        // 背景色 - 更浅的版本
        return `rgba(${r}, ${g}, ${b}, 0.1)`
      case 'border':
        // 边框色 - 中等亮度
        return `rgba(${r}, ${g}, ${b}, 0.3)`
      case 'text':
        // 文字色 - 原始颜色
        return `rgb(${r}, ${g}, ${b})`
      default:
        return color
    }
  }
</script>
