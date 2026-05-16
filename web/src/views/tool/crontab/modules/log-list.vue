<template>
  <el-drawer
    v-model="visible"
    title="任务执行日志"
    size="70%"
    destroy-on-close
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div class="art-full-height">
      <div class="flex justify-between items-center">
        <ElSpace wrap>
          <el-date-picker
            v-model="searchForm.create_time"
            type="datetimerange"
            range-separator="至"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            value-format="YYYY-MM-DD HH:mm:ss"
            clearable
          />
        </ElSpace>
        <ElSpace wrap>
          <ElButton class="reset-button" @click="handleReset" v-ripple>
            <template #icon>
              <ArtSvgIcon icon="ri:reset-right-line" />
            </template>
            重置
          </ElButton>
          <ElButton type="primary" class="search-button" @click="handleSearch" v-ripple>
            <template #icon>
              <ArtSvgIcon icon="ri:search-line" />
            </template>
            查询
          </ElButton>
        </ElSpace>
      </div>
      <ElCard class="art-table-card" shadow="never">
        <div>
          <ElSpace wrap>
            <ElButton
              v-permission="'tool:crontab:edit'"
              :disabled="selectedRows.length === 0"
              @click="handleLoadTable"
              v-ripple
            >
              <template #icon>
                <ArtSvgIcon icon="ri:delete-bin-5-line" />
              </template>
              删除
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
          <template #status="{ row }">
            <ElTag v-if="row.status == 1" type="success">成功</ElTag>
            <ElTag v-else type="danger">失败</ElTag>
          </template>
        </ArtTable>
      </ElCard>
    </div>
  </el-drawer>
</template>

<script setup lang="ts">
  import { ElMessage, ElMessageBox } from 'element-plus'
  import api from '@/api/tool/crontab'
  import { useTable } from '@/hooks/core/useTable'

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

  const selectedRows = ref<Record<string, any>[]>([])
  const searchForm = ref({
    crontab_id: '',
    orderType: 'desc',
    create_time: []
  })

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
    if (!props.data?.id) {
      ElMessage.error('请先选择一个任务')
      return
    }
    searchForm.value.crontab_id = props.data.id
    refreshData()
  }

  /**
   * 获取表格数据
   */
  const refreshData = () => {
    Object.assign(searchParams, searchForm.value)
    getData()
  }

  /**
   * 搜索
   */
  const handleSearch = () => {
    refreshData()
  }

  /**
   * 重置
   */
  const handleReset = () => {
    searchForm.value.create_time = []
    refreshData()
  }

  // 表格行选择变化
  const handleSelectionChange = (selection: Record<string, any>[]): void => {
    selectedRows.value = selection
  }

  // 确认选择装载数据表
  const handleLoadTable = async () => {
    if (selectedRows.value.length < 1) {
      ElMessage.info('至少要选择一条数据')
      return
    }
    ElMessageBox.confirm(
      `确定要删除选中的 ${selectedRows.value.length} 条数据吗？`,
      '删除选中数据',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'error'
      }
    ).then(() => {
      api.logDelete({ ids: selectedRows.value.map((row) => row.id) }).then(() => {
        ElMessage.success('删除成功')
        refreshData()
      })
    })
  }

  /**
   * 关闭弹窗
   */
  const handleClose = () => {
    visible.value = false
    selectedRows.value = []
  }

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
      apiFn: api.logList,
      immediate: false,
      apiParams: {
        ...searchForm.value
      },
      columnsFactory: () => [
        { type: 'selection' },
        { prop: 'create_time', label: '执行时间', sortable: true },
        { prop: 'target', label: '调用目标' },
        { prop: 'parameter', label: '任务参数' },
        { prop: 'status', label: '执行状态', useSlot: true, width: 100 }
      ]
    }
  })
</script>
