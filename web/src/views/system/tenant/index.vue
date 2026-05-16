<template>
  <div class="art-full-height">
    <TableSearch v-model="searchForm" @search="handleSearch" @reset="resetSearchParams" />

    <ElCard class="art-table-card" shadow="never">
      <ArtTableHeader v-model:columns="columnChecks" :loading="loading" @refresh="refreshData">
        <template #left>
          <ElSpace wrap>
            <ElButton v-permission="'core:tenant:save'" @click="showDialog('add')" v-ripple>
              <template #icon>
                <ArtSvgIcon icon="ri:add-fill" />
              </template>
              新增
            </ElButton>
          </ElSpace>
        </template>
      </ArtTableHeader>

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
          <div class="flex gap-2">
            <SaButton v-permission="'core:tenant:update'" type="secondary" @click="showDialog('edit', row)" />
            <SaButton v-permission="'core:tenant:destroy'" type="error" @click="deleteRow(row, api.delete, refreshData)" />
            <SaButton
              v-permission="'core:tenant:update'"
              type="primary"
              ri:speak-line
              @click="openTenantUsersDialog(row)"
            >
            </SaButton>
          </div>
        </template>
      </ArtTable>
    </ElCard>

    <EditDialog
      v-model="dialogVisible"
      :dialog-type="dialogType"
      :data="dialogData"
      @success="refreshData"
    />

    <TenantUsersDialog
      v-model="tenantUsersDialogVisible"
      :tenant-id="currentTenant.id"
      :tenant-name="currentTenant.tenant_name"
    />
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import api from '@/api/system/tenant'
  import TableSearch from './modules/table-search.vue'
  import EditDialog from './modules/edit-dialog.vue'
  import TenantUsersDialog from './modules/tenant-users-dialog.vue'

  const searchForm = ref({
    tenant_name: undefined,
    tenant_code: undefined,
    status: undefined
  })

  const handleSearch = (params: Record<string, any>) => {
    Object.assign(searchParams, params)
    getData()
  }

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
        { prop: 'id', label: '编号', width: 80, align: 'center' },
        { prop: 'tenant_name', label: '租户名称', minWidth: 150 },
        { prop: 'tenant_code', label: '租户编码', minWidth: 130 },
        { prop: 'contact_name', label: '联系人', minWidth: 110 },
        { prop: 'contact_phone', label: '联系电话', minWidth: 130 },
        { prop: 'max_users', label: '最大用户数', width: 110, align: 'center' },
        { prop: 'user_count', label: '当前用户数', width: 110, align: 'center' },
        { prop: 'expire_time', label: '过期时间', width: 170 },
        { prop: 'status', label: '状态', saiType: 'dict', saiDict: 'data_status', width: 90 },
        { prop: 'create_time', label: '创建时间', width: 170, sortable: true },
        { prop: 'operation', label: '操作', width: 230, fixed: 'right', useSlot: true }
      ]
    }
  })

  const { dialogType, dialogVisible, dialogData, showDialog, deleteRow } = useSaiAdmin()

  const tenantUsersDialogVisible = ref(false)
  const currentTenant = reactive({ id: 0, tenant_name: '' })

  const openTenantUsersDialog = (row: Record<string, any>) => {
    currentTenant.id = Number(row.id)
    currentTenant.tenant_name = row.tenant_name || ''
    tenantUsersDialogVisible.value = true
  }
</script>
