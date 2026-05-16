<template>
  <div class="art-full-height">
    <!-- 搜索面板 -->
    <TableSearch v-model="searchForm" @search="handleSearch" @reset="resetSearchParams" />

    <ElCard class="art-table-card" shadow="never">
      <!-- 表格头部 -->
      <ArtTableHeader v-model:columns="columnChecks" :loading="loading" @refresh="refreshData">
        <template #left>
          <ElSpace wrap>
            <ElButton v-permission="'core:database:index'" @click="showTableDialog('add')" v-ripple>
              <template #icon>
                <ArtSvgIcon icon="ri:upload-2-line" />
              </template>
              装载
            </ElButton>
            <ElButton
              v-permission="'tool:code:edit'"
              :disabled="selectedRows.length === 0"
              @click="batchGenerate"
              v-ripple
            >
              <template #icon>
                <ArtSvgIcon icon="ri:download-2-line" />
              </template>
              生成
            </ElButton>
            <ElButton
              v-permission="'tool:code:edit'"
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
        <!-- 生成类型列 -->
        <template #tpl_category="{ row }">
          <el-tag v-if="row.tpl_category === 'single'" type="success">单表CRUD</el-tag>
          <el-tag v-else type="danger">树表CRUD</el-tag>
        </template>

        <!-- 操作列 -->
        <template #operation="{ row }">
          <div class="flex gap-2">
            <SaButton
              v-permission="'tool:code:edit'"
              type="secondary"
              icon="ri:eye-line"
              @click="showDialog('edit', row)"
            />
            <SaButton
              v-permission="'tool:code:edit'"
              type="primary"
              icon="ri:refresh-line"
              @click="syncTable(row.id)"
            />
            <SaButton
              v-permission="'tool:code:edit'"
              type="secondary"
              @click="showEditDialog('edit', row)"
            />
            <SaButton
              v-permission="'tool:code:edit'"
              type="error"
              @click="deleteRow(row, api.delete, refreshData)"
            />
            <ElDropdown>
              <ArtIconButton
                icon="ri:more-2-fill"
                class="!size-8 bg-g-200 dark:bg-g-300/45 text-sm"
              />
              <template #dropdown>
                <ElDropdownMenu>
                  <ElDropdownItem>
                    <div
                      v-permission="'tool:code:edit'"
                      class="flex-c gap-2"
                      @click="generateFile(row.id)"
                    >
                      <ArtSvgIcon icon="ri:folder-add-line" />
                      <span>生成到项目</span>
                    </div>
                  </ElDropdownItem>
                  <ElDropdownItem>
                    <div
                      v-permission="'tool:code:edit'"
                      class="flex-c gap-2"
                      @click="generateCode(row.id)"
                    >
                      <ArtSvgIcon icon="ri:download-line" />
                      <span>代码下载</span>
                    </div>
                  </ElDropdownItem>
                </ElDropdownMenu>
              </template>
            </ElDropdown>
          </div>
        </template>
      </ArtTable>
    </ElCard>

    <!-- 装载数据表 -->
    <LoadTable v-model="tableVisible" :dialog-type="dialogType" @success="refreshData" />

    <!-- 预览代码 -->
    <Preview v-model="dialogVisible" :data="dialogData" />

    <!-- 编辑弹窗 -->
    <EditInfo v-model="editVisible" :data="editDialogData" @success="refreshData" />
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import { ElMessage, ElMessageBox } from 'element-plus'
  import api from '@/api/tool/generate'
  import { downloadFile } from '@/utils/tool'

  import TableSearch from './modules/table-search.vue'
  import LoadTable from './components/loadTable.vue'
  import Preview from './components/preview.vue'
  import EditInfo from './components/editInfo.vue'

  // 编辑弹窗
  const {
    dialogType,
    dialogVisible,
    dialogData,
    showDialog,
    handleSelectionChange,
    deleteRow,
    deleteSelectedRows,
    selectedRows
  } = useSaiAdmin()

  const { dialogVisible: tableVisible, showDialog: showTableDialog } = useSaiAdmin()

  const {
    dialogVisible: editVisible,
    dialogData: editDialogData,
    showDialog: showEditDialog
  } = useSaiAdmin()

  // 搜索表单
  const searchForm = ref({
    table_name: undefined,
    source: undefined
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
    pagination,
    searchParams,
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
        { type: 'selection', width: 50 },
        { prop: 'table_name', label: '表名称', minWidth: 180, align: 'left' },
        { prop: 'table_comment', label: '表描述', minWidth: 150, align: 'left' },
        { prop: 'template', label: '应用类型', minWidth: 120 },
        { prop: 'namespace', label: '应用名称', minWidth: 120 },
        { prop: 'stub', label: '模板类型', minWidth: 120 },
        { prop: 'tpl_category', label: '生成类型', minWidth: 120, useSlot: true },
        { prop: 'update_time', label: '更新时间', width: 180, sortable: true },
        { prop: 'operation', label: '操作', width: 220, fixed: 'right', useSlot: true }
      ]
    }
  })

  /**
   * 生成代码下载
   */
  const generateCode = async (ids: number | string) => {
    ElMessage.info('代码生成下载中，请稍后')
    const response = await api.generateCode({
      ids: ids.toString().split(',')
    })
    if (response) {
      downloadFile(response, 'code.zip')
      ElMessage.success('代码生成成功，开始下载')
    } else {
      ElMessage.error('文件下载失败')
    }
  }

  /**
   * 同步表结构
   */
  const syncTable = async (id: number) => {
    ElMessageBox.confirm('执行同步操作将会覆盖已经设置的表结构，确定要同步吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }).then(() => {
      api.async({ id }).then(() => {
        ElMessage.success('同步成功')
      })
    })
  }

  /**
   * 生成到项目
   */
  const generateFile = async (id: number) => {
    ElMessageBox.confirm('生成到项目将会覆盖原有文件，确定要生成吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }).then(() => {
      api.generateFile({ id }).then(() => {
        ElMessage.success('生成到项目成功')
      })
    })
  }

  /**
   * 批量生成代码
   */
  const batchGenerate = () => {
    if (selectedRows.value.length === 0) {
      ElMessage.error('至少要选择一条数据')
      return
    }
    generateCode(selectedRows.value.map((item: any) => item.id).join(','))
  }
</script>

<style lang="scss" scoped>
  :deep(.el-drawer__header) {
    margin-bottom: 10px !important;
  }
</style>
