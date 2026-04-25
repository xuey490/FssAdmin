<template>
  <div class="art-full-height">
    <!-- 搜索面板 -->
    <TableSearch v-model="searchForm" @search="handleSearch" @reset="resetSearchParams" />

    <ElCard class="art-table-card" shadow="never">
      <!-- 表格头部 -->
      <ArtTableHeader v-model:columns="columnChecks" :loading="loading" @refresh="refreshData">
        <template #left>
          <ElSpace wrap>
            <ElButton v-permission="'article:create'" @click="showDialog('add')" v-ripple>
              <template #icon>
                <ArtSvgIcon icon="ri:add-fill" />
              </template>
              新增
            </ElButton>
            <ElButton
              v-permission="'article:delete'"
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
        <!-- 图片列 -->
        <template #image="{ row }">
          <el-image
            v-if="row.image"
            :src="row.image"
            :preview-src-list="[row.image]"
            :preview-teleported="true"
            :z-index="9999"
            fit="cover"
            style="width: 50px; height: 50px; border-radius: 4px;"
          />
        </template>

        <!-- 简介列 -->
        <template #describe="{ row }">
          <el-tooltip :content="row.describe" placement="top" :disabled="!row.describe">
            <div class="text-ellipsis">{{ row.describe }}</div>
          </el-tooltip>
        </template>

        <!-- 是否外链列 -->
        <template #is_link="{ row }">
          <el-tag :type="row.is_link === 1 ? 'success' : 'info'" size="small">
            {{ row.is_link === 1 ? '是' : '否' }}
          </el-tag>
        </template>

        <!-- 是否热门列 -->
        <template #is_hot="{ row }">
          <el-tag :type="row.is_hot === 1 ? 'danger' : 'info'" size="small">
            {{ row.is_hot === 1 ? '热门' : '普通' }}
          </el-tag>
        </template>

        <!-- 状态列 -->
        <template #status="{ row }">
          <el-switch
            v-model="row.status"
            :active-value="1"
            :inactive-value="0"
            @change="handleStatusChange(row)"
          />
        </template>

        <!-- 操作列 -->
        <template #operation="{ row }">
          <div class="flex gap-2">
            <SaButton
              v-permission="'article:update'"
              type="secondary"
              @click="showDialog('edit', row)"
            />
            <SaButton
              v-permission="'article:delete'"
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
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import api from '@/api/article'
  import TableSearch from './modules/table-search.vue'
  import EditDialog from './modules/form.vue'

  // 搜索表单
  const searchForm = ref({
    title: undefined,
    author: undefined,
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
        { type: 'selection' },
        { prop: 'id', label: 'ID', width: 80, align: 'center' },
        { prop: 'title', label: '文章标题', minWidth: 200, showOverflowTooltip: true },
        { prop: 'author', label: '作者', width: 120 },
        { prop: 'image', label: '封面图片', width: 100, useSlot: true },
        //{ prop: 'describe', label: '文章简介', minWidth: 200, useSlot: true },
        { prop: 'views', label: '浏览次数', width: 100, align: 'center' },
        { prop: 'is_link', label: '是否外链', width: 100, align: 'center', useSlot: true },
        { prop: 'is_hot', label: '是否热门', width: 100, align: 'center', useSlot: true },
        { prop: 'sort', label: '排序', width: 80, align: 'center' },
        { prop: 'status', label: '状态', saiType: 'dict', saiDict: 'data_status', width: 100, useSlot: true },
        { prop: 'create_time', label: '创建时间', width: 180, sortable: true },
        { prop: 'operation', label: '操作', width: 150, fixed: 'right', useSlot: true }
      ]
    }
  })

  // 状态切换
  const handleStatusChange = async (row: any) => {
    try {
      await api.updateStatus(row.id, row.status)
      ElMessage.success('状态更新成功')
    } catch (error) {
      ElMessage.error('状态更新失败')
      row.status = row.status === 1 ? 0 : 1
    }
  }

  // 编辑配置
  const {
    dialogType,
    dialogVisible,
    dialogData,
    showDialog,
    deleteRow,
    deleteSelectedRows,
    handleSelectionChange,
    selectedRows
  } = useSaiAdmin()
</script>

<style scoped>
.text-ellipsis {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 200px;
}
</style>