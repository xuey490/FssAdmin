<template>
  <el-drawer
    v-model="visible"
    :title="`回收站 - ${props.data?.name}`"
    size="70%"
    destroy-on-close
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div class="art-full-height">
      <!-- 表格头部 -->
      <div>
        <ElSpace wrap>
          <ElButton
            v-permission="'core:recycle:edit'"
            :disabled="selectedRows.length === 0"
            @click="handleDestroyRows()"
            v-ripple
          >
            <template #icon>
              <ArtSvgIcon icon="ri:delete-bin-5-line" />
            </template>
            销毁
          </ElButton>
          <ElButton
            v-permission="'core:recycle:edit'"
            :disabled="selectedRows.length === 0"
            @click="handleRestoreRows()"
            v-ripple
          >
            <template #icon>
              <ArtSvgIcon icon="ri:restart-line" />
            </template>
            恢复
          </ElButton>
        </ElSpace>
      </div>
      <!-- 表格 -->
      <ArtTable
        ref="tableRef"
        rowKey="id"
        :loading="loading"
        :data="tableData"
        :columns="columns"
        :pagination="pagination"
        @sort-change="handleSortChange"
        @selection-change="handleSelectionChange"
        @pagination:size-change="handleSizeChange"
        @pagination:current-change="handleCurrentChange"
      >
        <!-- 数据详情插槽 -->
        <template #json_data="{ row }">
          {{ JSON.stringify(row) }}
        </template>
      </ArtTable>
    </div>
  </el-drawer>
</template>

<script setup lang="ts">
  import api from '@/api/safeguard/database'
  import { ElMessage, ElMessageBox } from 'element-plus'
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'

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

  /**
   * 初始化页面数据
   */
  const initPage = async () => {
    // 如果有数据，则填充数据
    if (props.data) {
      await nextTick()
      refreshData()
    }
  }

  const refreshData = () => {
    searchForm.value.table = props.data?.name
    Object.assign(searchParams, searchForm.value)
    getData()
  }

  const searchForm = ref({
    table: null
  })

  const {
    loading,
    data: tableData,
    columns,
    getData,
    pagination,
    searchParams,
    handleSortChange,
    handleSizeChange,
    handleCurrentChange
  } = useTable({
    core: {
      apiFn: async (params: any) => {
        const response = await api.getRecycle(params)
        // 返回格式为 { code: 200, data: { list: [...], total: 10, page: 1, limit: 20 }, message: 'success' }
        return {
          list: response?.data || [],
          total: response?.total || 0
        }
      },
      immediate: false,
      apiParams: {
        ...searchForm.value
      },
      columnsFactory: () => [
        { type: 'selection' },
        { prop: 'delete_time', label: '删除时间', width: 180 },
        { prop: 'json_data', label: '数据详情', useSlot: true, showOverflowTooltip: true }
      ]
    }
  })

  // 编辑配置
  const { handleSelectionChange, selectedRows } = useSaiAdmin()

  /**
   * 关闭弹窗并重置表单
   */
  const handleClose = () => {
    visible.value = false
  }

  /**
   * 销毁选中数据
   */
  const handleDestroyRows = (): void => {
    if (selectedRows.value.length === 0) {
      ElMessage.warning('请选择要销毁的行')
      return
    }
    ElMessageBox.confirm(
      `确定要销毁选中的 ${selectedRows.value.length} 条数据吗？`,
      '销毁选中数据',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'error'
      }
    ).then(() => {
      api
        .delete({ table: searchForm.value.table, ids: selectedRows.value.map((row) => row.id) })
        .then(() => {
          ElMessage.success('操作成功')
          refreshData()
          selectedRows.value = []
        })
    })
  }

  /**
   * 恢复选中数据
   */
  const handleRestoreRows = (): void => {
    if (selectedRows.value.length === 0) {
      ElMessage.warning('请选择要恢复的行')
      return
    }
    ElMessageBox.confirm(
      `确定要恢复选中的 ${selectedRows.value.length} 条数据吗？`,
      '恢复选中数据',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'error'
      }
    ).then(() => {
      api
        .recovery({ table: searchForm.value.table, ids: selectedRows.value.map((row) => row.id) })
        .then(() => {
          ElMessage.success('操作成功')
          refreshData()
          selectedRows.value = []
        })
    })
  }
</script>
