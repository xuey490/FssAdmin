<template>
  <div class="art-full-height">
    <!-- 搜索面板 -->
    <TableSearch
      v-model="searchForm"
      @search="handleSearch"
      @reset="resetSearchParams"
    ></TableSearch>

    <ElCard class="art-table-card" shadow="never">
      <!-- 表格头部 -->
      <ArtTableHeader v-model:columns="columnChecks" :loading="loading" @refresh="refreshData">
        <template #left>
          <ElSpace wrap>
            <ElButton
              v-permission="'core:database:edit'"
              :disabled="selectedRows.length === 0"
              @click="handleOptimizeRows()"
              v-ripple
            >
              <template #icon>
                <ArtSvgIcon icon="ri:tools-fill" />
              </template>
              优化表
            </ElButton>
            <ElButton
              v-permission="'core:database:edit'"
              :disabled="selectedRows.length === 0"
              @click="handleFragmentRows()"
              v-ripple
            >
              <template #icon>
                <ArtSvgIcon icon="ri:wrench-line" />
              </template>
              清理碎片
            </ElButton>
          </ElSpace>
        </template>
      </ArtTableHeader>

      <!-- 表格 -->
      <ArtTable
        ref="tableRef"
        rowKey="name"
        :loading="loading"
        :data="data"
        :columns="columns"
        @sort-change="handleSortChange"
        @selection-change="handleSelectionChange"
      >
        <!-- 操作列 -->
        <template #operation="{ row }">
          <div class="flex gap-2">
            <SaButton
              v-permission="'core:database:index'"
              type="primary"
              icon="ri:node-tree"
              tool-tip="表结构"
              @click="handleTableDialog(row)"
            />
            <SaButton
              v-permission="'core:database:index'"
              type="info"
              icon="ri:code-s-slash-line"
              tool-tip="建表语句"
              @click="handleDdlDialog(row)"
            />
            <SaButton
              v-permission="'core:recycle:index'"
              type="success"
              icon="ri:recycle-line"
              tool-tip="回收站"
              @click="handleRecycleDialog(row)"
            />
          </div>
        </template>
      </ArtTable>
    </ElCard>

    <!-- 表结构信息 -->
    <TableDialog v-model="dialogVisible" :data="dialogData" />

    <!-- 建表语句 -->
    <DdlDialog v-model="ddlVisible" :data="ddlData" />

    <!-- 回收站 -->
    <RecycleList v-model="recycleVisible" :data="recycleData" />
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import { ElMessageBox } from 'element-plus'
  import api from '@/api/safeguard/database'
  import TableSearch from './modules/table-search.vue'
  import TableDialog from './modules/table-dialog.vue'
  import RecycleList from './modules/recycle-list.vue'
  import DdlDialog from './modules/ddl-dialog.vue'

  // 搜索表单
  const searchForm = ref({
    name: undefined,
    orderField: 'create_time',
    orderType: 'desc'
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
    resetSearchParams,
    handleSortChange,
    refreshData
  } = useTable({
    core: {
      apiFn: async (params: any) => {
        const response = await api.list(params)

        // 返回格式为 { code: 200, data: { list: [...], total: 10 }, message: 'success' }
        return {
          list: response.data || [],
          total: response.total || 0
        }
      },
      apiParams: {
        ...searchForm.value
      },
      columnsFactory: () => [
        { type: 'selection' },
        { prop: 'name', label: '表名称', minWidth: 200 },
        { prop: 'comment', label: '表注释', minWidth: 150, showOverflowTooltip: true },
        { prop: 'engine', label: '表引擎', width: 120 },
        { prop: 'update_time', label: '更新时间', width: 180, sortable: true },
        { prop: 'rows', label: '总行数', width: 120 },
        { prop: 'data_free', label: '碎片大小', width: 120 },
        { prop: 'data_length', label: '数据大小', width: 120 },
        { prop: 'collation', label: '字符集', width: 180 },
        { prop: 'create_time', label: '创建时间', width: 180, sortable: true },
        { prop: 'operation', label: '操作', width: 140, fixed: 'right', useSlot: true }
      ]
    }
  })

  // 编辑配置
  const { dialogVisible, dialogData, selectedRows, handleSelectionChange } = useSaiAdmin()
  const recycleVisible = ref(false)
  const recycleData = ref({})
  const ddlVisible = ref(false)
  const ddlData = ref({})

  /**
   * 表结构
   * @param row
   */
  const handleTableDialog = (row: Record<string, any>): void => {
    dialogVisible.value = true
    dialogData.value = row
  }

  /**
   * 回收站
   * @param row
   */
  const handleRecycleDialog = (row: Record<string, any>): void => {
    recycleVisible.value = true
    recycleData.value = row
  }

  const handleDdlDialog = (row: Record<string, any>): void => {
    ddlData.value = row
    ddlVisible.value = true
  }

  /**
   * 优化表
   */
  const handleOptimizeRows = (): void => {
    if (selectedRows.value.length === 0) {
      ElMessage.warning('请选择要优化的行')
      return
    }
    ElMessageBox.confirm(
      `确定要优化选中的 ${selectedRows.value.length} 条数据吗？`,
      '优化选中数据',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'error'
      }
    ).then(() => {
      api.optimize({ tables: selectedRows.value.map((row) => row.name) }).then(() => {
        ElMessage.success('操作成功')
        refreshData()
        selectedRows.value = []
      })
    })
  }

  /**
   * 清理表碎片
   */
  const handleFragmentRows = (): void => {
    if (selectedRows.value.length === 0) {
      ElMessage.warning('请选择要清理碎片的行')
      return
    }
    ElMessageBox.confirm(
      `确定要清理选中的 ${selectedRows.value.length} 条数据吗？`,
      '清理碎片操作',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'error'
      }
    ).then(() => {
      api.fragment({ tables: selectedRows.value.map((row) => row.name) }).then(() => {
        ElMessage.success('操作成功')
        refreshData()
        selectedRows.value = []
      })
    })
  }
</script>
