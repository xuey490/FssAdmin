<template>
  <el-dialog v-model="visible" title="表结构信息" width="800px" align-center @close="handleClose">
    <div>
      <el-table :data="tableData" style="width: 100%">
        <el-table-column prop="column_name" label="字段名称" width="180"> </el-table-column>
        <el-table-column prop="column_type" label="字段类型" width="120"> </el-table-column>
        <el-table-column prop="column_key" label="字段索引" width="100"> </el-table-column>
        <el-table-column prop="column_default" label="默认值" width="100"> </el-table-column>
        <el-table-column prop="column_comment" label="字段注释" min-width="200" showOverflowTooltip>
        </el-table-column>
      </el-table>
    </div>
  </el-dialog>
</template>

<script setup lang="ts">
  import api from '@/api/safeguard/database'

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

  const tableData = ref<Api.Common.ApiData[]>([])

  /**
   * 初始化页面数据
   */
  const initPage = async () => {
    // 如果有数据，则填充数据
    if (props.data) {
      await nextTick()
      if (props.data.name) {
        const data = await api.getDetailed({ table: props.data.name })

        tableData.value = Array.isArray(data) ? data : ((data as any)?.columns ?? [])
      }
    }
  }

  /**
   * 关闭弹窗并重置表单
   */
  const handleClose = () => {
    visible.value = false
  }
</script>
