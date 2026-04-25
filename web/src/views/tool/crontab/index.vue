<template>
  <div class="art-full-height">
    <!-- 搜索面板 -->
    <TableSearch v-model="searchForm" @search="handleSearch" @reset="resetSearchParams" />

    <ElCard class="art-table-card" shadow="never">
      <!-- 表格头部 -->
      <ArtTableHeader v-model:columns="columnChecks" :loading="loading" @refresh="refreshData">
        <template #left>
          <ElSpace wrap>
            <ElButton v-permission="'tool:crontab:edit'" @click="showDialog('add')" v-ripple>
              <template #icon>
                <ArtSvgIcon icon="ri:add-fill" />
              </template>
              新增
            </ElButton>
          </ElSpace>
        </template>
      </ArtTableHeader>

      <!-- 表格 -->
      <ArtTable
        ref="tableRef"
        rowKey="id"
        :loading="loading"
        :data="data"
        :columns="columns"
        :pagination="pagination"
        @sort-change="handleSortChange"
        @selection-change="handleSelectionChange"
        @pagination:size-change="handleSizeChange"
        @pagination:current-change="handleCurrentChange"
      >
        <!-- 操作列 -->
        <template #operation="{ row }">
          <div class="flex gap-2">
            <SaButton
              v-permission="'tool:crontab:run'"
              type="primary"
              icon="ri:play-fill"
              toolTip="运行任务"
              @click="handleRun(row)"
            />
            <SaButton
              type="primary"
              icon="ri:history-line"
              toolTip="运行日志"
              @click="showTableDialog('edit', row)"
            />
            <SaButton
              v-permission="'tool:crontab:edit'"
              type="secondary"
              @click="showDialog('edit', row)"
            />
            <SaButton
              v-permission="'tool:crontab:edit'"
              type="error"
              @click="deleteRow(row, api.delete, refreshData)"
            />
          </div>
        </template>
      </ArtTable>
    </ElCard>

    <!-- 编辑弹窗 -->
    <EditDialog
      v-model="dialogVisible"
      :dialog-type="dialogType"
      :data="dialogData"
      @success="refreshData"
    />

    <!-- 日志弹窗 -->
    <LogListDialog v-model="tableVisible" :dialog-type="tableDialogType" :data="tableData" />
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import { ElMessage, ElMessageBox } from 'element-plus'
  import api from '@/api/tool/crontab'
  import TableSearch from './modules/table-search.vue'
  import EditDialog from './modules/edit-dialog.vue'
  import LogListDialog from './modules/log-list.vue'

  // 搜索表单
  const searchForm = ref({
    name: undefined,
    type: undefined,
    status: undefined
  })

  // 搜索处理
  const handleSearch = (params: Record<string, any>) => {
    Object.assign(searchParams, params)
    getData()
  }

  // 表格配置
  const {
    columns,
    columnChecks,
    data,
    loading,
    getData,
    searchParams,
    pagination,
    resetSearchParams,
    handleSortChange,
    handleSizeChange,
    handleCurrentChange,
    refreshData
  } = useTable({
    core: {
      apiFn: api.list,
      columnsFactory: () => [
        { prop: 'id', label: '编号', width: 100, align: 'center' },
        { prop: 'name', label: '任务名称', minWidth: 120 },
        {
          prop: 'type',
          label: '任务类型',
          saiType: 'dict',
          saiDict: 'crontab_task_type',
          minWidth: 120
        },
        { prop: 'rule', label: '定时规则', minWidth: 140 },
        { prop: 'target', label: '调用目标', minWidth: 200, showOverflowTooltip: true },
        { prop: 'status', label: '状态', saiType: 'dict', saiDict: 'data_status', width: 100 },
        { prop: 'update_time', label: '更新日期', width: 180, sortable: true },
        { prop: 'operation', label: '操作', width: 180, fixed: 'right', useSlot: true }
      ]
    }
  })

  // 编辑配置
  const { dialogType, dialogVisible, dialogData, showDialog, deleteRow, handleSelectionChange } =
    useSaiAdmin()

  const {
    dialogVisible: tableVisible,
    dialogType: tableDialogType,
    dialogData: tableData,
    showDialog: showTableDialog
  } = useSaiAdmin()

  // 运行任务
  const handleRun = (row: any) => {
    ElMessageBox.confirm(`确定要运行任务【${row.name}】吗？`, '运行任务', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }).then(() => {
      api.run(row.id).then(() => {
        ElMessage.success('任务运行成功')
        refreshData()
      })
    })
  }
</script>
