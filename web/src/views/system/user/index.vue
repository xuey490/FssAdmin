<template>
  <div class="art-full-height">
    <div class="box-border flex gap-4 h-full max-md:block max-md:gap-0 max-md:h-auto">
      <div class="flex-shrink-0 w-64 h-full max-md:w-full max-md:h-auto max-md:mb-5">
        <ElCard class="tree-card art-card-xs flex flex-col h-full mt-0" shadow="never">
          <template #header>
            <b>部门列表</b>
          </template>
          <ElScrollbar>
            <ElTree
              :data="treeData"
              :props="{ children: 'children', label: 'label' }"
              node-key="id"
              default-expand-all
              highlight-current
              @node-click="handleNodeClick"
            />
          </ElScrollbar>
        </ElCard>
      </div>

      <div class="flex flex-col flex-grow min-w-0">
        <!-- 搜索栏 -->
        <TableSearch v-model="searchForm" @search="handleSearch" @reset="handleReset" />

        <ElCard class="flex flex-col flex-1 min-h-0 art-table-card" shadow="never">
          <!-- 表格头部 -->
          <ArtTableHeader v-model:columns="columnChecks" :loading="loading" @refresh="refreshData">
            <template #left>
              <ElSpace wrap>
                <ElButton v-permission="'core:user:save'" @click="showDialog('add')" v-ripple>
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
            <template #operation="{ row }" >
              <div class="flex gap-2" v-permission="'core:user:update'" v-if="row.id !== 1 && userStore.info.id !== row.id">
                <SaButton
                  v-permission="'core:user:update'"
                  type="secondary"
                  @click="showDialog('edit', row)"
                />
                <SaButton
                  v-permission="'core:user:destroy'"
                  type="error"
                  @click="handleDelete(row)"
                />
                <ElDropdown>
                  <ArtIconButton
                    icon="ri:more-2-fill"
                    class="!size-8 bg-g-200 dark:bg-g-300/45 text-sm"
                  />
                  <template #dropdown>
                    <ElDropdownMenu>
                      <ElDropdownItem v-if="checkAuth('core:user:home')" @click="showWorkDialog('edit', row)">
                        <div class="flex-c gap-2">
                          <ArtSvgIcon icon="ri:home-office-line" />
                          <span>设置首页</span>
                        </div>
                      </ElDropdownItem>
                      <ElDropdownItem v-if="checkAuth('core:user:password')" @click="handlePassword(row)">
                        <div class="flex-c gap-2">
                          <ArtSvgIcon icon="ri:key-line" />
                          <span>修改密码</span>
                        </div>
                      </ElDropdownItem>
                      <ElDropdownItem v-if="checkAuth('core:user:cache')" @click="handleCache(row)">
                        <div class="flex-c gap-2">
                          <ArtSvgIcon icon="ri:eraser-line" />
                          <span>清理缓存</span>
                        </div>
                      </ElDropdownItem>
                      <ElDropdownItem v-if="checkAuth('core:user:menu')" @click="showAssignMenuDialog('edit', row)">
                        <div class="flex-c gap-2">
                          <ArtSvgIcon icon="ri:menu-line" />
                          <span>分配菜单</span>
                        </div>
                      </ElDropdownItem>
                    </ElDropdownMenu>
                  </template>
                </ElDropdown>
              </div>
            </template>
          </ArtTable>
        </ElCard>
      </div>
    </div>
    <!-- 表单弹窗 -->
    <EditDialog
      v-model="dialogVisible"
      :dialog-type="dialogType"
      :data="dialogData"
      @success="refreshData"
    />
    <!-- 工作台弹窗 -->
    <WorkDialog
      v-model="workDialogVisible"
      :dialog-type="workDialogType"
      :data="workDialogData"
      @success="refreshData"
    />
    <!-- 分配菜单弹窗 -->
    <AssignMenuDialog
      v-model="assignMenuDialogVisible"
      :data="assignMenuDialogData"
      @success="refreshData"
    />
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import { useUserStore } from '@/store/modules/user'
  import { ElMessageBox } from 'element-plus'
  import { checkAuth } from '@/utils/tool'
  import TableSearch from './modules/table-search.vue'
  import EditDialog from './modules/edit-dialog.vue'
  import WorkDialog from './modules/work-dialog.vue'
  import AssignMenuDialog from './modules/assign-menu-dialog.vue'
  import api from '@/api/system/user'
  import deptApi from '@/api/system/dept'

  const userStore = useUserStore()

  const treeData = ref([])

  // 编辑框
  const { dialogType, dialogVisible, dialogData, showDialog, handleSelectionChange, deleteRow } =
    useSaiAdmin()

  const {
    dialogType: workDialogType,
    dialogVisible: workDialogVisible,
    dialogData: workDialogData,
    showDialog: showWorkDialog
  } = useSaiAdmin()

  const {
    dialogType: assignMenuDialogType,
    dialogVisible: assignMenuDialogVisible,
    dialogData: assignMenuDialogData,
    showDialog: showAssignMenuDialog
  } = useSaiAdmin()

  // 搜索表单
  const searchForm = ref({
    username: undefined,
    phone: undefined,
    email: undefined,
    dept_id: undefined,
    status: ''
  })

  const {
    columns,
    columnChecks,
    data,
    loading,
    pagination,
    getData,
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
        { type: 'index', width: 60, label: '序号' },
        {
          prop: 'avatar',
          label: '用户名',
          minWidth: 200,
          saiType: 'imageAndText',
          saiFirst: 'username',
          saiSecond: 'email'
        },
        { prop: 'phone', label: '手机号', width: 120 },
        { prop: 'dept.name', label: '部门', minWidth: 150 },
        { prop: 'status', label: '状态', width: 80, saiType: 'dict', saiDict: 'data_status' },
        { prop: 'dashboard', label: '首页', width: 100, saiType: 'dict', saiDict: 'dashboard' },
        { prop: 'login_time', label: '上次登录', width: 170, sortable: true },
        {
          prop: 'operation',
          label: '操作',
          width: 140,
          fixed: 'right',
          useSlot: true
        }
      ]
    }
  })

  /**
   * 搜索
   * @param params
   */
  const handleSearch = (params: Record<string, any>) => {
    Object.assign(searchParams, params)
    getData()
  }

  /**
   * 重置
   */
  const handleReset = () => {
    searchForm.value.dept_id = undefined
    resetSearchParams()
  }

  /**
   * 切换部门
   * @param data
   */
  const handleNodeClick = (data: any) => {
    searchParams.dept_id = data.id
    getData()
  }

  /**
   * 获取部门数据
   */
  const getDeptList = () => {
    deptApi.accessDept().then((data: any) => {
      treeData.value = data
    })
  }

  /**
   * 修改密码
   * @param row
   */
  const handlePassword = (row: any) => {
    ElMessageBox.prompt('请输入新密码', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      inputPattern: /^.{6,16}$/,
      inputErrorMessage: '密码长度在6到16之间',
      type: 'warning'
    }).then(({ value }) => {
      api.changePassword({ id: row.id, password: value }).then(() => {
        ElMessage.success('修改密码成功')
      })
    })
  }

  /**
   * 清理缓存
   * @param row
   */
  const handleCache = (row: any) => {
    ElMessageBox.confirm('确定要清理缓存吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }).then(() => {
      api.clearCache({ id: row.id }).then(() => {
        ElMessage.success('清理缓存成功')
      })
    })
  }

  /**
   * 删除用户
   * @param row
   */
  const handleDelete = (row: any) => {
    ElMessageBox.confirm(`确定要删除用户【${row.username}】吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }).then(() => {
      api.delete(row.id).then(() => {
        ElMessage.success('删除成功')
        refreshData()
      })
    }).catch(() => {})
  }

  onMounted(() => {
    getDeptList()
  })
</script>
