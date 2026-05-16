<template>
  <!-- 
    v-bind="$attrs" 透传父组件传递的 width, class, style 以及 change, focus 等事件 
  -->
  <el-select
    v-model="modelValue"
    v-bind="$attrs"
    :placeholder="placeholder"
    :disabled="disabled"
    :clearable="clearable"
    :filterable="filterable"
    :multiple="multiple"
    :collapse-tags="collapseTags"
    :collapse-tags-tooltip="collapseTagsTooltip"
  >
    <!-- 遍历生成选项 -->
    <el-option
      v-for="(item, index) in options"
      :key="index"
      :label="item.label"
      :value="item.value"
      :disabled="item.disabled"
    >
      <!-- 支持自定义 option 模板 (可选)，不传则只显示 label -->
      <slot name="option" :item="item">
        <span>{{ item.label }}</span>
      </slot>
    </el-option>

    <!-- 透传 el-select 的其他插槽 (如 prefix, empty) -->
    <template v-for="(_, name) in $slots" #[name]="slotData">
      <slot v-if="name !== 'option'" :name="name" v-bind="slotData || {}"></slot>
    </template>
  </el-select>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { useDictStore } from '@/store/modules/dict'

  defineOptions({ name: 'SaSelect', inheritAttrs: false })

  interface Props {
    /** 字典编码 (必填) */
    dict: string

    /**
     * 强制转换字典值的类型
     * 解决后端返回字符串但前端表单需要数字的问题
     */
    valueType?: 'number' | 'string'

    // --- 以下为常用属性显式定义，为了 IDE 提示友好 ---
    placeholder?: string
    disabled?: boolean
    clearable?: boolean
    filterable?: boolean
    multiple?: boolean
    collapseTags?: boolean
    collapseTagsTooltip?: boolean
  }

  const props = withDefaults(defineProps<Props>(), {
    placeholder: '请选择',
    disabled: false,
    clearable: true, // 下拉框默认开启清除，体验更好
    filterable: false, // 下拉框默认关闭搜索
    multiple: false,
    collapseTags: false,
    collapseTagsTooltip: false,
    valueType: 'number'
  })

  // 支持单选(string/number) 或 多选(Array)
  const modelValue = defineModel<string | number | Array<string | number>>()

  const dictStore = useDictStore()

  // 判断能否转成数字
  const canConvertToNumberStrict = (value: any) => {
    // 严格模式：排除 null、undefined、布尔值、空数组等
    if (value == null) return false
    if (typeof value === 'boolean') return false
    if (Array.isArray(value) && value.length !== 1) return false
    if (typeof value === 'object' && !Array.isArray(value)) return false

    const num = Number(value)
    return !isNaN(num)
  }

  // 计算属性：获取字典数据并处理类型转换
  const options = computed(() => {
    const list = dictStore.getByCode(props.dict) || []

    // 1. 如果没有指定 valueType，直接返回
    if (!props.valueType) return list

    // 2. 如果指定了类型，进行映射转换
    return list.map((item) => {
      let newValue = item.value
      switch (props.valueType) {
        case 'number':
          if (canConvertToNumberStrict(item.value)) {
            newValue = Number(item.value)
          }
          break
        case 'string':
          newValue = String(item.value)
          break
      }

      return {
        ...item,
        value: newValue
      }
    })
  })
</script>

<style scoped>
  /* 让 Select 默认宽度占满父容器，通常在表单中体验更好，可视情况删除 */
  .el-select {
    width: 100%;
  }
</style>
