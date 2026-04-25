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
              v-permission="'core:email:destroy'"
              :disabled="selectedRows.length === 0"
              @click="deleteSelectedRows(api.delete, refreshData)"
              v-ripple
            >
              <template #icon>
                <ArtSvgIcon icon="ri:delete-bin-5-line" />
              </template>
              删除
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
        <template #status="{ row }">
          <ElTag v-if="row.status == 'success'" type="success">成功</ElTag>
          <ElTag v-else type="danger">失败</ElTag>
        </template>
        <template #operation="{ row }">
          <div class="flex">
            <SaButton
              v-permission="'core:email:destroy'"
              type="error"
              @click="deleteRow(row, api.delete, refreshData)"
            />
          </div>
        </template>
      </ArtTable>
    </ElCard>
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import api from '@/api/safeguard/emailLog'
  import TableSearch from './modules/table-search.vue'

  // 搜索表单
  const searchForm = ref({
    from: undefined,
    email: undefined,
    status: undefined,
    create_time: undefined,
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
    pagination,
    resetSearchParams,
    handleSortChange,
    handleSizeChange,
    handleCurrentChange,
    refreshData
  } = useTable({
    core: {
      apiFn: api.list,
      apiParams: {
        ...searchForm.value
      },
      columnsFactory: () => [
        { type: 'selection' },
        { prop: 'id', label: '编号', width: 100, align: 'center' },
        { prop: 'gateway', label: '服务Host' },
        { prop: 'from', label: '发件人', minWidth: 150, showOverflowTooltip: true },
        { prop: 'email', label: '收件人', minWidth: 150, showOverflowTooltip: true },
        { prop: 'code', label: '验证码' },
        { prop: 'status', label: '发送状态', useSlot: true },
        { prop: 'response', label: '发送结果', minWidth: 150, showOverflowTooltip: true },
        { prop: 'create_time', label: '发送时间', width: 180, sortable: true },
        { prop: 'operation', label: '操作', width: 80, fixed: 'right', useSlot: true }
      ]
    }
  })

  // 编辑配置
  const { deleteRow, deleteSelectedRows, selectedRows, handleSelectionChange } = useSaiAdmin()
</script>
