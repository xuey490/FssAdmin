<template>
  <el-checkbox-group
    v-model="modelValue"
    v-bind="$attrs"
    :disabled="disabled"
    :size="size"
    :fill="fill"
    :text-color="textColor"
  >
    <!-- 模式1: 按钮样式 -->
    <template v-if="type === 'button'">
      <el-checkbox-button
        v-for="(item, index) in options"
        :key="index"
        :value="item.value"
        :label="item.value"
        :disabled="item.disabled"
      >
        {{ item.label }}
      </el-checkbox-button>
    </template>

    <!-- 模式2: 普通/边框样式 -->
    <template v-else>
      <el-checkbox
        v-for="(item, index) in options"
        :key="index"
        :value="item.value"
        :label="item.value"
        :border="type === 'border'"
        :disabled="item.disabled"
      >
        {{ item.label }}
      </el-checkbox>
    </template>
  </el-checkbox-group>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { useDictStore } from '@/store/modules/dict'

  defineOptions({ name: 'SaCheckbox', inheritAttrs: false })

  interface Props {
    dict: string
    type?: 'checkbox' | 'button' | 'border'
    disabled?: boolean
    size?: 'large' | 'default' | 'small'
    fill?: string
    textColor?: string
    /**
     * 强制转换字典值的类型
     * 可选值: 'number' | 'string'
     * 默认使用 'number'
     */
    valueType?: 'number' | 'string'
  }

  const props = withDefaults(defineProps<Props>(), {
    type: 'checkbox',
    disabled: false,
    size: 'default',
    fill: '',
    textColor: '',
    valueType: 'number'
  })

  const modelValue = defineModel<(string | number)[]>()

  const dictStore = useDictStore()

  const canConvertToNumberStrict = (value: any) => {
    if (value == null) return false
    if (typeof value === 'boolean') return false
    if (Array.isArray(value) && value.length !== 1) return false
    if (typeof value === 'object' && !Array.isArray(value)) return false

    const num = Number(value)
    return !isNaN(num)
  }

  const options = computed(() => {
    const list = dictStore.getByCode(props.dict) || []

    if (!props.valueType) return list

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
