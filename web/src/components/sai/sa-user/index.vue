<template>
  <el-select
    v-model="selectedValue"
    v-bind="$attrs"
    :placeholder="placeholder"
    :disabled="disabled"
    :clearable="clearable"
    :filterable="false"
    :multiple="multiple"
    :collapse-tags="collapseTags"
    :collapse-tags-tooltip="collapseTagsTooltip"
    :loading="loading"
    popper-class="sa-user-select-popper"
    @visible-change="handleVisibleChange"
    @clear="handleClear"
  >
    <template #header>
      <div class="sa-user-select-header" @click.stop>
        <el-input
          v-model="searchKeyword"
          placeholder="搜索用户名、姓名、手机号"
          clearable
          @input="handleSearch"
          @click.stop
        >
          <template #prefix>
            <el-icon><Search /></el-icon>
          </template>
        </el-input>
      </div>
    </template>

    <!-- 隐藏的选项，用于显示已选中的用户 -->
    <el-option
      v-for="user in selectedUsers"
      :key="user.id"
      :value="user.id"
      :label="user.username"
      style="display: none"
    />

    <!-- 使用 el-option 包装表格内容 -->
    <el-option value="" disabled style="height: auto; padding: 0">
      <div class="sa-user-select-table" @click.stop>
        <el-table
          ref="tableRef"
          :data="userList"
          @row-click="handleRowClick"
          @selection-change="handleSelectionChange"
          :row-class-name="getRowClassName"
          size="small"
          v-loading="loading"
        >
          <el-table-column
            v-if="multiple"
            type="selection"
            width="45"
            :selectable="checkSelectable"
          />
          <el-table-column prop="id" label="编号" align="center" width="80" />
          <el-table-column prop="avatar" label="头像" width="60">
            <template #default="{ row }">
              <el-avatar :size="32" :src="row.avatar">
                {{ row.username?.charAt(0) }}
              </el-avatar>
            </template>
          </el-table-column>
          <el-table-column prop="username" label="用户名" width="100" show-overflow-tooltip />
          <el-table-column prop="realname" label="姓名" width="100" show-overflow-tooltip />
          <el-table-column prop="phone" label="手机号" width="110" show-overflow-tooltip />
        </el-table>

        <div class="sa-user-select-pagination">
          <el-pagination
            v-model:current-page="pagination.page"
            v-model:page-size="pagination.limit"
            :total="pagination.total"
            layout="total, prev, pager, next"
            small
            background
            @current-change="handlePageChange"
            @size-change="handleSizeChange"
          />
        </div>
      </div>
    </el-option>

    <template #empty>
      <el-empty description="暂无用户数据" :image-size="60" />
    </template>
  </el-select>
</template>

<script lang="ts" setup>
  import { ref, computed, watch, onMounted } from 'vue'
  import { getUserList } from '@/api/auth'
  import { Search } from '@element-plus/icons-vue'
  import { ElMessage } from 'element-plus'
  import type { TableInstance } from 'element-plus'

  defineOptions({ name: 'SaUser', inheritAttrs: false })

  interface UserItem {
    id: number
    username: string
    email: string
    phone: string
    avatar?: string
    status: string
    [key: string]: any
  }

  interface Props {
    /** 占位符 */
    placeholder?: string
    /** 是否禁用 */
    disabled?: boolean
    /** 是否可清空 */
    clearable?: boolean
    /** 是否可搜索 */
    filterable?: boolean
    /** 是否多选 */
    multiple?: boolean
    /** 多选时是否折叠标签 */
    collapseTags?: boolean
    /** 多选折叠时是否显示提示 */
    collapseTagsTooltip?: boolean
    /** 返回值类型：'id' 返回用户ID，'object' 返回完整用户对象 */
    valueType?: 'id' | 'object'
  }

  const props = withDefaults(defineProps<Props>(), {
    placeholder: '请选择用户',
    disabled: false,
    clearable: true,
    filterable: true,
    multiple: false,
    collapseTags: true,
    collapseTagsTooltip: true,
    valueType: 'id'
  })

  // 支持单选(number/object) 或 多选(Array)
  const modelValue = defineModel<number | null | UserItem | Array<number | UserItem>>()

  // 内部选中值
  const selectedValue = ref<any>()
  const searchKeyword = ref('')
  const loading = ref(false)
  const userList = ref<UserItem[]>([])
  const tableRef = ref<TableInstance>()

  // 缓存所有已选中的用户信息
  const allSelectedUsers = ref<UserItem[]>([])

  // 计算已选中的用户列表（用于显示）
  const selectedUsers = computed(() => {
    if (!selectedValue.value) return []

    const selectedIds = props.multiple
      ? Array.isArray(selectedValue.value)
        ? selectedValue.value
        : []
      : [selectedValue.value]

    // 从缓存中查找用户信息
    return selectedIds
      .map((id) => {
        const cached = allSelectedUsers.value.find((u) => u.id === id)
        if (cached) return cached

        // 从当前列表中查找
        const fromList = userList.value.find((u) => u.id === id)
        if (fromList) {
          // 添加到缓存
          allSelectedUsers.value.push(fromList)
          return fromList
        }

        // 如果都找不到，返回一个临时对象
        return { id, username: `用户${id}`, email: '', phone: '', status: '1' }
      })
      .filter(Boolean)
  })

  // 分页参数
  const pagination = ref({
    page: 1,
    limit: 5,
    total: 0
  })

  // 获取用户列表
  const fetchUserList = async () => {
    loading.value = true
    try {
      const params: any = {
        page: pagination.value.page,
        limit: pagination.value.limit
      }

      // 添加搜索条件
      if (searchKeyword.value) {
        params.keyword = searchKeyword.value
      }

      const response = await getUserList(params)

      if (response) {
        console.log(response)
        userList.value = (response as any).list || (response as any).data || []
        pagination.value.total = (response as any).total || 0
      }
    } catch (error) {
      console.error('获取用户列表失败:', error)
      ElMessage.error('获取用户列表失败')
    } finally {
      loading.value = false
    }
  }

  // 搜索防抖
  let searchTimer: any = null
  const handleSearch = () => {
    if (searchTimer) clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
      pagination.value.page = 1
      fetchUserList()
    }, 300)
  }

  // 下拉框显示/隐藏
  const handleVisibleChange = (visible: boolean) => {
    if (visible) {
      // 打开时加载数据
      fetchUserList()
    }
  }

  // 清空选择
  const handleClear = () => {
    selectedValue.value = props.multiple ? [] : null
    if (tableRef.value) {
      if (props.multiple) {
        tableRef.value.clearSelection()
      } else {
        tableRef.value.setCurrentRow(null)
      }
    }
  }

  // 单选 - 行点击
  const handleRowClick = (row: UserItem) => {
    if (!props.multiple) {
      handleCurrentChange(row)
    }
  }

  // 单选 - 当前行改变
  const handleCurrentChange = (row: UserItem | undefined) => {
    if (!props.multiple && row) {
      // 添加到缓存
      const existingIndex = allSelectedUsers.value.findIndex((u) => u.id === row.id)
      if (existingIndex === -1) {
        allSelectedUsers.value.push(row)
      } else {
        allSelectedUsers.value[existingIndex] = row
      }

      selectedValue.value = props.valueType === 'id' ? row.id : row
    }
  }

  // 多选 - 选择改变
  const handleSelectionChange = (selection: UserItem[]) => {
    if (props.multiple) {
      // 更新缓存
      selection.forEach((row) => {
        const existingIndex = allSelectedUsers.value.findIndex((u) => u.id === row.id)
        if (existingIndex === -1) {
          allSelectedUsers.value.push(row)
        } else {
          allSelectedUsers.value[existingIndex] = row
        }
      })

      selectedValue.value = selection.map((item) => (props.valueType === 'id' ? item.id : item))
    }
  }

  // 检查行是否可选
  const checkSelectable = () => {
    return !props.disabled
  }

  // 选中行高亮
  const getRowClassName = ({ row }: { row: UserItem }) => {
    const ids = props.multiple
      ? Array.isArray(selectedValue.value) ? selectedValue.value : []
      : selectedValue.value ? [selectedValue.value] : []
    return ids.some((v: any) => (props.valueType === 'id' ? v === row.id : v?.id === row.id))
      ? 'is-selected-row'
      : ''
  }

  // 分页改变
  const handlePageChange = () => {
    fetchUserList()
  }

  const handleSizeChange = () => {
    pagination.value.page = 1
    fetchUserList()
  }

  // 监听内部选中值变化，同步到 v-model
  watch(
    selectedValue,
    (newVal) => {
      modelValue.value = newVal
    },
    { deep: true }
  )

  // 监听 v-model 变化，同步到内部选中值
  watch(
    () => modelValue.value,
    (newVal) => {
      selectedValue.value = newVal
    },
    { immediate: true, deep: true }
  )

  // 组件挂载时初始化
  onMounted(() => {
    // 如果有初始值，加载数据
    if (modelValue.value) {
      fetchUserList()
    }
  })
</script>

<style scoped lang="scss">
  .sa-user-select-header {
    padding: 8px 12px;
    border-bottom: 1px solid var(--el-border-color-light);
  }

  .sa-user-select-table {
    min-height: 480px;
    max-height: 600px;
    display: flex;
    flex-direction: column;

    :deep(.el-table) {
      .el-table__header-wrapper {
        th {
          background-color: var(--el-fill-color-light);
        }
      }

      .el-table__row {
        cursor: pointer;

        &:hover {
          background-color: var(--el-fill-color-light);
        }
      }
    }
  }

  .sa-user-select-pagination {
    padding: 8px 12px;
    border-top: 1px solid var(--el-border-color-light);
    background-color: var(--el-fill-color-blank);
    display: flex;
    justify-content: center;
  }
</style>

<style lang="scss">
  // 全局样式，不使用 scoped
  .sa-user-select-popper {
    max-width: 90vw !important;

    .el-select-dropdown__item {
      height: auto !important;
      min-height: 320px !important;
      max-height: 360px !important;
      padding: 0 !important;
      line-height: normal !important;

      &.is-disabled {
        cursor: default;
        background-color: transparent !important;
      }
    }

    .el-select-dropdown__wrap {
      max-height: 340px !important;
    }

    // 确保下拉框列表容器也不限制高度
    .el-select-dropdown__list {
      padding: 0 !important;
    }

    // 确保滚动容器正确显示
    .el-scrollbar__view {
      padding: 0 !important;
    }

    .el-table .is-selected-row td {
      background-color: var(--el-color-primary-light-9) !important;
    }
  }
</style>
