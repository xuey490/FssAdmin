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
              v-permission="'core:logs:deleteOper'"
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
        <template #operation="{ row }">
          <div class="flex gap-2">
            <SaButton type="success" @click="handleParams(row)" />
            <SaButton
              v-permission="'core:logs:deleteOper'"
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
  import { ElMessageBox } from 'element-plus'
  import api from '@/api/safeguard/operLog'
  import TableSearch from './modules/table-search.vue'

  // 搜索表单
  const searchForm = ref({
    username: undefined,
    ip: undefined,
    service_name: undefined,
    router: undefined,
    create_time: undefined,
    orderField: 'create_time',
    orderType: 'desc'
  })

  // 搜索处理
  const handleSearch = (params: Record<string, any>) => {
    Object.assign(searchParams, params)
    getData()
	
	console.log(getData())
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
        { prop: 'username', label: '操作用户' },
        { prop: 'service_name', label: '业务名称' },
        { prop: 'router', label: '路由', minWidth: 180, showOverflowTooltip: true },
        { prop: 'ip', label: '操作IP' },
        { prop: 'ip_location', label: '操作地点' },
        { prop: 'duration', label: '耗时(ms)' },		
        { prop: 'create_time', label: '操作时间', width: 180, sortable: true },
        { prop: 'operation', label: '操作', width: 100, fixed: 'right', useSlot: true }
      ]
    }
  })

  // 编辑配置
  const { deleteRow, deleteSelectedRows, selectedRows, handleSelectionChange } = useSaiAdmin()

  // 预览参数
  const handleParams = (row: any) => {
    let formattedData = row.request_data
    // 尝试格式化JSON数据
    if (row.request_data) {
      try {
        // 如果已经是对象，直接格式化；如果是字符串，先解析再格式化
        const parsedData =
          typeof row.request_data === 'string' ? JSON.parse(row.request_data) : row.request_data
        formattedData = JSON.stringify(parsedData, null, 2)
      } catch (error) {
        // 如果解析失败，保持原样显示
        formattedData = row.request_data
        console.log('Error parsing JSON:', error)
      }
    }

    ElMessageBox({
      title: '请求参数',
      message: h(
        'div',
        {
          style: {
            maxHeight: '400px',
            minWidth: '380px',
            overflow: 'auto',
            backgroundColor: '#f5f5f5',
            padding: '16px',
            borderRadius: '4px'
          }
        },
        [
          h(
            'pre',
            {
              style: {
                margin: 0,
                fontFamily: 'Consolas, Monaco, "Courier New", monospace',
                fontSize: '14px',
                lineHeight: '1.5',
                color: '#333'
              }
            },
            formattedData
          )
        ]
      ),
      callback: () => {}
    })
  }
</script>
