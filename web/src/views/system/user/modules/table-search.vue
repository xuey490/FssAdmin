<template>
  <sa-search-bar
    ref="searchBarRef"
    v-model="formData"
    :label-width="'70px'"
    :showExpand="false"
    @reset="handleReset"
    @search="handleSearch"
    @expand="handleExpand"
  >
    <el-col v-bind="setSpan(6)">
      <el-form-item label="用户名" prop="username">
        <el-input v-model="formData.username" placeholder="请输入用户名" clearable />
      </el-form-item>
    </el-col>
    <el-col v-bind="setSpan(6)">
      <el-form-item label="手机号" prop="phone">
        <el-input v-model="formData.phone" placeholder="请输入手机号" clearable />
      </el-form-item>
    </el-col>
    <el-col v-bind="setSpan(6)">
      <el-form-item label="状态" prop="status">
        <sa-select v-model="formData.status" dict="data_status" clearable />
      </el-form-item>
    </el-col>
  </sa-search-bar>
</template>

<script setup lang="ts">
  interface Props {
    modelValue: Record<string, any>
  }
  interface Emits {
    (e: 'update:modelValue', value: Record<string, any>): void
    (e: 'search', params: Record<string, any>): void
    (e: 'reset'): void
  }
  const props = defineProps<Props>()
  const emit = defineEmits<Emits>()
  const isExpanded = ref<boolean>(false)

  // 表单数据双向绑定
  const searchBarRef = ref()
  const formData = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
  })

  // 重置
  function handleReset() {
    searchBarRef.value?.ref.resetFields()
    emit('reset')
  }

  // 搜索
  async function handleSearch() {
    emit('search', formData.value)
  }

  // 展开/收起
  function handleExpand(expanded: boolean) {
    isExpanded.value = expanded
  }

  // 栅格占据的列数
  const setSpan = (span: number) => {
    return {
      span: span,
      xs: 24, // 手机：满宽显示
      sm: span >= 12 ? span : 12, // 平板：大于等于12保持，否则用半宽
      md: span >= 8 ? span : 8, // 中等屏幕：大于等于8保持，否则用三分之一宽
      lg: span,
      xl: span
    }
  }
</script>
