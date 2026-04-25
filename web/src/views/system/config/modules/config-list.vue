<template>
  <el-drawer
    v-model="visible"
    title="配置管理"
    size="70%"
    destroy-on-close
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div class="art-full-height">
      <!-- 表格头部 -->
      <div>
        <ElSpace wrap>
          <ElButton v-permission="'core:config:edit'" @click="handleAddClick" v-ripple>
            <template #icon>
              <ArtSvgIcon icon="ri:add-fill" />
            </template>
            新增
          </ElButton>
          <ElButton
            v-permission="'core:config:edit'"
            :disabled="selectedRows.length === 0"
            @click="deleteSelectedRows(api.configDelete, refreshData)"
            v-ripple
          >
            <template #icon>
              <ArtSvgIcon icon="ri:delete-bin-5-line" />
            </template>
            删除
          </ElButton>
        </ElSpace>
      </div>
      <!-- 表格 -->
      <ArtTable
        ref="tableRef"
        rowKey="id"
        :loading="loading"
        :data="tableData"
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
            <SaButton
              v-permission="'core:config:edit'"
              type="secondary"
              @click="showDialog('edit', row)"
            />
            <SaButton
              v-permission="'core:config:edit'"
              type="error"
              @click="deleteRow(row, api.configDelete, refreshData)"
            />
          </div>
        </template>
      </ArtTable>

      <ConfigEditDialog
        v-model="dialogVisible"
        :dialog-type="dialogType"
        :data="dialogData"
        @success="refreshData"
      />
    </div>
  </el-drawer>
</template>

<script setup lang="ts">
  import api from '@/api/system/config'
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import ConfigEditDialog from './config-edit-dialog.vue'

  interface Props {
    modelValue: boolean
    data?: Record<string, any>
  }

  interface Emits {
    (e: 'update:modelValue', value: boolean): void
    (e: 'success'): void
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    data: undefined
  })

  const emit = defineEmits<Emits>()

  /**
   * 弹窗显示状态双向绑定
   */
  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

  /**
   * 监听弹窗打开，初始化表单数据
   */
  watch(
    () => props.modelValue,
    (newVal) => {
      if (newVal) {
        initPage()
      }
    }
  )

  /**
   * 初始化页面数据
   */
  const initPage = async () => {
    // 如果有数据，则填充数据
    if (props.data) {
      await nextTick()
      refreshData()
    }
  }

  const refreshData = () => {
    searchForm.value.group_id = props.data?.id
    Object.assign(searchParams, searchForm.value)
    getData()
  }

  const handleAddClick = () => {
    showDialog('add', { group_id: searchForm.value.group_id })
  }

  const searchForm = ref({
    label: '',
    value: '',
    status: '',
    group_id: null
  })

  const {
    loading,
    data: tableData,
    columns,
    getData,
    pagination,
    searchParams,
    handleSortChange,
    handleSizeChange,
    handleCurrentChange
  } = useTable({
    core: {
      apiFn: api.configList,
      immediate: false,
      apiParams: {
        ...searchForm.value
      },
      columnsFactory: () => [
        { type: 'selection' },
        { prop: 'key', label: '配置标识' },
        { prop: 'name', label: '配置标题' },
        { prop: 'input_type', label: '组件类型', width: 100 },
        { prop: 'sort', label: '排序', width: 100, sortable: true },
        { prop: 'remark', label: '备注' },
        { prop: 'operation', label: '操作', useSlot: true, width: 100 }
      ]
    }
  })

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

  /**
   * 关闭弹窗并重置表单
   */
  const handleClose = () => {
    visible.value = false
    emit('success')
  }
</script>
