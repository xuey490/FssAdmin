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
            <ElButton v-permission="'core:role:save'" @click="showDialog('add')" v-ripple>
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
        rowKey="id"
        :loading="loading"
        :data="data"
        :columns="columns"
        :pagination="pagination"
        @sort-change="handleSortChange"
        @pagination:size-change="handleSizeChange"
        @pagination:current-change="handleCurrentChange"
      >
        <template #operation="{ row }">
          <div class="flex gap-2" v-if="row.id !== 1">
            <SaButton
              v-permission="'core:role:update'"
              type="secondary"
              @click="showDialog('edit', row)"
            />
            <SaButton
              v-permission="'core:role:destroy'"
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
                      v-permission="'core:role:menu'"
                      class="flex-c gap-2"
                      @click="showPermissionDialog('edit', row)"
                    >
                      <ArtSvgIcon icon="ri:user-3-line" />
                      <span>菜单权限</span>
                    </div>
                  </ElDropdownItem>
                </ElDropdownMenu>
              </template>
            </ElDropdown>
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

    <!-- 菜单权限弹窗 -->
    <PermissionDialog
      v-model="permissionDialogVisible"
      :dialog-type="permissionDialogType"
      :data="permissionDialogData"
      @success="refreshData"
    />
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import api from '@/api/system/role'
  import TableSearch from './modules/table-search.vue'
  import EditDialog from './modules/edit-dialog.vue'
  import PermissionDialog from './modules/permission-dialog.vue'

  // 搜索表单
  const searchForm = ref({
    name: undefined,
    code: undefined,
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
      columnsFactory: () => [
        { prop: 'id', label: '编号', minWidth: 60, align: 'center' },
        { prop: 'name', label: '角色名称', minWidth: 120 },
        { prop: 'code', label: '角色编码', minWidth: 120 },
        { prop: 'level', label: '角色级别', minWidth: 100, sortable: true },
        { prop: 'remark', label: '角色描述', minWidth: 150, showOverflowTooltip: true },
        { prop: 'sort', label: '排序', minWidth: 100 },
        { prop: 'status', label: '状态', saiType: 'dict', saiDict: 'data_status' },
        { prop: 'create_time', label: '创建日期', width: 180, sortable: true },
        { prop: 'operation', label: '操作', width: 140, fixed: 'right', useSlot: true }
      ]
    }
  })

  // 编辑配置
  const { dialogType, dialogVisible, dialogData, showDialog, deleteRow } = useSaiAdmin()

  // 权限配置
  const {
    dialogType: permissionDialogType,
    dialogVisible: permissionDialogVisible,
    dialogData: permissionDialogData,
    showDialog: showPermissionDialog
  } = useSaiAdmin()
</script>
