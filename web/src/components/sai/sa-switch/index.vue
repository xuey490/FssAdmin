<template>
  <el-switch
    v-model="modelValue"
    v-bind="$attrs"
    :loading="props.loading"
    :inline-prompt="props.inlinePrompt"
    :active-value="props.activeValue"
    :inactive-value="props.inactiveValue"
    :active-text="props.showText ? props.activeText : undefined"
    :inactive-text="props.showText ? props.inactiveText : undefined"
  />
</template>

<script lang="ts" setup>
  defineOptions({ name: 'SaSwitch', inheritAttrs: false })

  interface Props {
    /** 是否显示加载中 */
    loading?: boolean
    /** 是否在开关内显示文字 */
    inlinePrompt?: boolean
    /** 打开时的值 */
    activeValue?: string | number | boolean
    /** 关闭时的值 */
    inactiveValue?: string | number | boolean
    /** 是否显示文字 */
    showText?: boolean
    /** 打开时的文字描述 */
    activeText?: string
    /** 关闭时的文字描述 */
    inactiveText?: string
  }

  const props = withDefaults(defineProps<Props>(), {
    loading: false,
    inlinePrompt: true,
    activeValue: 1,
    inactiveValue: 2,
    showText: true,
    activeText: '启用',
    inactiveText: '禁用'
  })

  const modelValue = defineModel<string | number | boolean | undefined>()
</script>
