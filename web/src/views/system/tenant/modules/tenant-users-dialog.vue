<template>
  <el-dialog
    v-model="visible"
    :title="`租户用户管理 - ${tenantName}`"
    width="1080px"
    align-center
    :close-on-click-modal="false"
    @open="loadBoundUsers"
  >
    <el-tabs v-model="activeTab">
      <el-tab-pane label="已关联用户" name="bound">
        <div class="mb-3 flex flex-wrap gap-3">
          <el-input v-model="boundSearch.username" clearable placeholder="账号" style="width: 180px" />
          <el-input v-model="boundSearch.realname" clearable placeholder="姓名" style="width: 180px" />
          <el-input v-model="boundSearch.phone" clearable placeholder="手机号" style="width: 180px" />
          <el-button type="primary" @click="searchBound">查询</el-button>
        </div>

        <el-table :data="boundRows" border height="360" v-loading="boundLoading">
          <el-table-column prop="user_id" label="用户ID" width="90" align="center" />
          <el-table-column prop="username" label="账号" min-width="140" />
          <el-table-column prop="realname" label="姓名" min-width="120" />
          <el-table-column prop="phone" label="手机号" min-width="130" />

          <el-table-column label="默认租户" width="100" align="center">
            <template #default="{ row }">
              <el-switch
                :model-value="row.is_default === 1"
                @change="(value) => handleDefaultChange(row.user_id, Boolean(value))"
                :disabled="row.id === 1"
              />
            </template>
          </el-table-column>
          <el-table-column label="租户管理员" width="100" align="center">
            <template #default="{ row }">
              <el-switch
                :model-value="row.is_super === 1"
                @change="(value) => handleAdminChange(row.user_id, Boolean(value))"
                :disabled="row.id === 1"
              />
            </template>
          </el-table-column>
          <el-table-column label="状态" width="90" align="center">
            <template #default="{ row }">
              <el-tag :type="row.status === 1 ? 'success' : 'danger'">{{ row.status === 1 ? '启用' : '禁用' }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="120" align="center">
            <template #default="{ row }">
              <el-button
                v-permission="'core:tenant:update'"
                type="danger"
                link
                @click="removeUser(row.user_id)"
                v-if="row.id !== 1"
              >
                移除
              </el-button>
            </template>
          </el-table-column>
        </el-table>

        <div class="mt-3 flex justify-end">
          <el-pagination
            v-model:current-page="boundPagination.page"
            v-model:page-size="boundPagination.limit"
            :total="boundPagination.total"
            :page-sizes="[10, 20, 50]"
            layout="total, sizes, prev, pager, next"
            @size-change="loadBoundUsers"
            @current-change="loadBoundUsers"
          />
        </div>
      </el-tab-pane>

      <el-tab-pane label="添加用户" name="available">
        <div class="mb-3 flex flex-wrap gap-3">
          <el-input v-model="availableSearch.username" clearable placeholder="账号" style="width: 180px" />
          <el-input v-model="availableSearch.realname" clearable placeholder="姓名" style="width: 180px" />
          <el-input v-model="availableSearch.phone" clearable placeholder="手机号" style="width: 180px" />
          <el-button type="primary" @click="searchAvailable">查询</el-button>
          <el-button
            v-permission="'core:tenant:update'"
            type="success"
            :disabled="selectedAvailableRows.length === 0"
            @click="handleAddUsers"
          >
            添加选中用户
          </el-button>
        </div>

        <el-table
          :data="availableRows"
          border
          height="360"
          v-loading="availableLoading"
          @selection-change="handleAvailableSelectionChange"
        >
          <el-table-column type="selection" width="50" />
          <el-table-column prop="id" label="用户ID" width="90" align="center" />
          <el-table-column prop="username" label="账号" min-width="140" />
          <el-table-column prop="realname" label="姓名" min-width="120" />
          <el-table-column prop="phone" label="手机号" min-width="130" />
          <el-table-column prop="email" label="邮箱" min-width="180" />
          <el-table-column label="状态" width="90" align="center">
            <template #default="{ row }">
              <el-tag :type="row.status === 1 ? 'success' : 'danger'">{{ row.status === 1 ? '启用' : '禁用' }}</el-tag>
            </template>
          </el-table-column>
        </el-table>

        <div class="mt-3 flex justify-end">
          <el-pagination
            v-model:current-page="availablePagination.page"
            v-model:page-size="availablePagination.limit"
            :total="availablePagination.total"
            :page-sizes="[10, 20, 50]"
            layout="total, sizes, prev, pager, next"
            @size-change="loadAvailableUsers"
            @current-change="loadAvailableUsers"
          />
        </div>
      </el-tab-pane>
    </el-tabs>

    <template #footer>
      <el-button @click="visible = false">关闭</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  import api from '@/api/system/tenant'
  import { ElMessage, ElMessageBox } from 'element-plus'

  interface Props {
    modelValue: boolean
    tenantId: number
    tenantName: string
  }

  interface Emits {
    (e: 'update:modelValue', value: boolean): void
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    tenantId: 0,
    tenantName: ''
  })

  const emit = defineEmits<Emits>()

  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

  const activeTab = ref<'bound' | 'available'>('bound')

  const boundLoading = ref(false)
  const boundRows = ref<Record<string, any>[]>([])
  const boundPagination = reactive({ page: 1, limit: 10, total: 0 })
  const boundSearch = reactive({ username: '', realname: '', phone: '' })

  const availableLoading = ref(false)
  const availableRows = ref<Record<string, any>[]>([])
  const availablePagination = reactive({ page: 1, limit: 10, total: 0 })
  const availableSearch = reactive({ username: '', realname: '', phone: '' })
  const selectedAvailableRows = ref<Record<string, any>[]>([])

  watch(
    () => props.modelValue,
    (val) => {
      if (!val) {
        activeTab.value = 'bound'
        selectedAvailableRows.value = []
      }
    }
  )

  watch(activeTab, (tab) => {
    if (!visible.value) return
    if (tab === 'bound') {
      loadBoundUsers()
    } else {
      loadAvailableUsers()
    }
  })

  const loadBoundUsers = async () => {
    if (!props.tenantId) return
    boundLoading.value = true
    try {
      const res = await api.users(props.tenantId, {
        page: boundPagination.page,
        limit: boundPagination.limit,
        ...boundSearch
      })
      boundRows.value = res.data || []
      boundPagination.total = Number(res.total || 0)
      
      // 调试信息：查看数据
      console.log('租户用户数据:', boundRows.value)
    } finally {
      boundLoading.value = false
    }
  }

  const loadAvailableUsers = async () => {
    if (!props.tenantId) return
    availableLoading.value = true
    try {
      const res = await api.availableUsers(props.tenantId, {
        page: availablePagination.page,
        limit: availablePagination.limit,
        ...availableSearch
      })
      availableRows.value = res.data || []
      availablePagination.total = Number(res.total || 0)
    } finally {
      availableLoading.value = false
    }
  }

  const searchBound = () => {
    boundPagination.page = 1
    loadBoundUsers()
  }

  const searchAvailable = () => {
    availablePagination.page = 1
    loadAvailableUsers()
  }

  const handleAvailableSelectionChange = (rows: Record<string, any>[]) => {
    selectedAvailableRows.value = rows
  }

  const handleAddUsers = async () => {
    if (selectedAvailableRows.value.length === 0) {
      ElMessage.warning('请先选择用户')
      return
    }

    const userIds = selectedAvailableRows.value.map((item) => Number(item.id))
    await api.addUsers(props.tenantId, userIds)
    ElMessage.success('添加成功')

    selectedAvailableRows.value = []
    availablePagination.page = 1
    await loadAvailableUsers()

    boundPagination.page = 1
    activeTab.value = 'bound'
    await loadBoundUsers()
  }

  const handleAdminChange = async (userId: number, value: boolean) => {
    try {
      const isSuper = value ? 1 : 0
      const action = isSuper === 1 ? '设为租户管理员' : '取消租户管理员'
      //console.log(`${action} - 租户ID:`, props.tenantId, '用户ID:', userId, 'isSuper:', isSuper)
      
      await api.setTenantAdmin(props.tenantId, userId, isSuper)
      ElMessage.success(`${action}成功`)
      await loadBoundUsers()
    } catch (error) {
      //console.error('设置租户管理员失败:', error)
      ElMessage.error('设置失败')
      await loadBoundUsers()
    }
  }

  const handleDefaultChange = async (userId: number, value: boolean) => {
    try {
      const isDefault = value ? 1 : 0
      const action = isDefault === 1 ? '设为默认租户' : '取消默认租户'
      console.log(`${action} - 租户ID:`, props.tenantId, '用户ID:', userId, 'isDefault:', isDefault)
      
      await api.setDefaultTenant(props.tenantId, userId, isDefault)
      ElMessage.success(`${action}成功`)
      await loadBoundUsers()
    } catch (error) {
      console.error('设置默认租户失败:', error)
      ElMessage.error('设置失败')
      await loadBoundUsers()
    }
  }

  const removeUser = async (userId: number) => {
    await ElMessageBox.confirm('确定要将该用户从租户中移除吗？', '移除用户', {
      type: 'warning',
      confirmButtonText: '确定',
      cancelButtonText: '取消'
    })
    await api.removeUser(props.tenantId, userId)
    ElMessage.success('移除成功')
    await loadBoundUsers()
    await loadAvailableUsers()
  }
</script>
