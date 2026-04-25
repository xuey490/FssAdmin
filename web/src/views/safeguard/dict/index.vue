<!-- 左右页面 -->
<template>
  <div class="art-full-height">
    <div class="box-border flex gap-4 h-full max-md:block max-md:gap-0 max-md:h-auto">
      <div class="flex-shrink-0 h-full max-md:w-full max-md:h-auto max-md:mb-5">
        <ElCard class="left-card art-card-xs flex flex-col h-full mt-0" shadow="never">
          <template #header>
            <b>数据字典</b>
          </template>
          <ElSpace wrap>
            <SaButton type="primary" icon="ri:refresh-line" @click="refreshTypeData" />
            <SaButton
              v-permission="'core:dict:edit'"
              type="primary"
              @click="typeShowDialog('add')"
            />
            <SaButton v-permission="'core:dict:edit'" type="secondary" @click="updateTypeDialog" />
            <SaButton v-permission="'core:dict:edit'" type="error" @click="deleteTypeDialog" />
          </ElSpace>
          <ArtTable
            rowKey="id"
            :loading="loading"
            :data="typeData"
            :columns="typeColumns"
            :pagination="typePagination"
            highlight-current-row
            @pagination:size-change="handleSizeChange"
            @pagination:current-change="handleCurrentChange"
          >
            <!-- 基础列 -->
            <template #name-header="{ column }">
              <ElPopover placement="bottom" :width="200" trigger="hover">
                <template #reference>
                  <div class="flex items-center gap-2 text-theme c-p custom-header">
                    <span>{{ column.label }}</span>
                    <ElIcon>
                      <Search />
                    </ElIcon>
                  </div>
                </template>
                <ElInput
                  v-model="typeSearch.name"
                  placeholder="搜索字典名称"
                  size="small"
                  clearable
                  @input="handleTypeSearch"
                >
                  <template #prefix>
                    <ElIcon>
                      <Search />
                    </ElIcon>
                  </template>
                </ElInput>
              </ElPopover>
            </template>
            <template #code-header="{ column }">
              <ElPopover placement="bottom" :width="200" trigger="hover">
                <template #reference>
                  <div class="flex items-center gap-2 text-theme c-p custom-header">
                    <span>{{ column.label }}</span>
                    <ElIcon>
                      <Search />
                    </ElIcon>
                  </div>
                </template>
                <ElInput
                  v-model="typeSearch.code"
                  placeholder="搜索字典标识"
                  size="small"
                  clearable
                  @input="handleTypeSearch"
                >
                  <template #prefix>
                    <ElIcon>
                      <Search />
                    </ElIcon>
                  </template>
                </ElInput>
              </ElPopover>
            </template>
            <template #id="{ row }">
              <ElRadio
                v-model="selectedId"
                :value="row.id"
                @update:modelValue="handleTypeChange(row.id, row)"
              />
            </template>
          </ArtTable>
        </ElCard>
      </div>

      <div class="flex flex-col flex-1 min-w-0" v-if="selectedId === 0">
        <ElCard class="flex flex-col flex-5 min-h-0 !mt-0" shadow="never">
          <el-empty description="请先选择左侧字典类型配置" />
        </ElCard>
      </div>

      <div class="flex flex-col flex-1 min-w-0" v-if="selectedId > 0">
        <DictSearch v-model="searchForm" @search="handleSearch" @reset="handleReset" />

        <ElCard class="flex flex-col flex-5 min-h-0 art-table-card" shadow="never">
          <ElSpace wrap>
            <ElButton
              v-permission="'core:dict:edit'"
              @click="showDataDialog('add', { type_id: selectedId })"
              v-ripple
            >
              <template #icon>
                <ArtSvgIcon icon="ri:add-fill" />
              </template>
              新增
            </ElButton>
            <ElButton
              v-permission="'core:dict:edit'"
              @click="deleteSelectedRows(api.dataDelete, getDictData)"
              :disabled="selectedRows.length === 0"
              v-ripple
            >
              <template #icon>
                <ArtSvgIcon icon="ri:delete-bin-5-line" />
              </template>
              删除
            </ElButton>
          </ElSpace>
          <ArtTable
            rowKey="id"
            :loading="loading"
            :data="dictData"
            :columns="dictColumns"
            :pagination="dictPagination"
            highlight-current-row
            @selection-change="handleSelectionChange"
            @pagination:size-change="handleDictSizeChange"
            @pagination:current-change="handleDictCurrentChange"
          >
            <!-- 基础列 -->
            <template #label="{ row }">
              <ElTag
                :style="{
                  backgroundColor: getColor(row.color, 'bg'),
                  borderColor: getColor(row.color, 'border'),
                  color: getColor(row.color, 'text')
                }"
              >
                {{ row.label }}
              </ElTag>
            </template>
            <!-- 操作列 -->
            <template #operation="{ row }">
              <div class="flex gap-2">
                <SaButton
                  v-permission="'core:dict:edit'"
                  type="secondary"
                  @click="showDataDialog('edit', row)"
                />
                <SaButton
                  v-permission="'core:dict:edit'"
                  type="error"
                  @click="deleteRow(row, api.dataDelete, getDictData)"
                />
              </div>
            </template>
          </ArtTable>
        </ElCard>
      </div>
    </div>

    <!-- 字典编辑弹窗 -->
    <TypeEditDialog
      v-model="typeVisible"
      :dialog-type="typeDialogType"
      :data="currentTypeData"
      @success="getTypeData()"
    />

    <!-- 字典项编辑弹窗 -->
    <DictEditDialog
      v-model="dictVisible"
      :dialog-type="dictDialogType"
      :data="currentDictData"
      @success="getDictData()"
    />
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import { Search } from '@element-plus/icons-vue'
  import { ElMessage } from 'element-plus'
  import api from '@/api/safeguard/dict'
  import DictSearch from '@/views/safeguard/dict/modules/dict-search.vue'
  import DictEditDialog from './modules/dict-edit-dialog.vue'
  import TypeEditDialog from './modules/type-edit-dialog.vue'

  // 字典类型数据
  const {
    dialogType: typeDialogType,
    dialogVisible: typeVisible,
    dialogData: currentTypeData,
    showDialog: typeShowDialog,
    deleteRow: typeDeleteRow
  } = useSaiAdmin()

  // 字典类型
  const selectedId = ref(0)
  const selectedRow = ref({})
  const typeSearch = ref({
    name: '',
    code: ''
  })

  /** 修改字典类型 */
  const updateTypeDialog = () => {
    if (selectedId.value === 0) {
      ElMessage.error('请选择要修改的数据')
      return
    }
    typeShowDialog('edit', { ...selectedRow.value })
  }

  /** 删除字典类型 */
  const deleteTypeDialog = () => {
    if (selectedId.value === 0) {
      ElMessage.error('请选择要删除的数据')
      return
    }
    typeDeleteRow({ ...selectedRow.value }, api.delete, refreshTypeData)
  }

  /** 字典类型搜索 */
  const handleTypeSearch = () => {
    Object.assign(searchTypeParams, typeSearch.value)
    getTypeData()
  }

  /** 字典类型切换 */
  const handleTypeChange = (val: any, row?: any) => {
    selectedId.value = val
    selectedRow.value = row
    searchForm.value.type_id = val
    Object.assign(searchParams, searchForm.value)
    getDictData()
  }

  /** 刷新数据 */
  const refreshTypeData = () => {
    selectedId.value = 0
    selectedRow.value = {}
    getTypeData()
    getDictData()
  }

  // 字典类型数据
  const {
    data: typeData,
    columns: typeColumns,
    getData: getTypeData,
    searchParams: searchTypeParams,
    loading,
    pagination: typePagination,
    handleSizeChange,
    handleCurrentChange
  } = useTable({
    core: {
      apiFn: api.typeList,
      apiParams: {
        ...typeSearch.value
      },
      columnsFactory: () => [
        { prop: 'id', label: '选中', width: 80, align: 'center', useSlot: true },
        { prop: 'name', label: '字典名称', useHeaderSlot: true, width: 150 },
        { prop: 'code', label: '字典标识', useHeaderSlot: true, width: 150 },
        { prop: 'status', label: '状态', saiType: 'dict', saiDict: 'data_status', width: 100 }
      ]
    }
  })

  // 字典项数据
  const {
    dialogType: dictDialogType,
    dialogVisible: dictVisible,
    dialogData: currentDictData,
    showDialog: showDataDialog,
    deleteRow,
    handleSelectionChange,
    selectedRows,
    deleteSelectedRows
  } = useSaiAdmin()

  /** 字典项搜索 */
  const searchForm = ref({
    label: '',
    value: '',
    status: '',
    type_id: null
  })

  // 字典项数据
  const {
    data: dictData,
    columns: dictColumns,
    getData: getDictData,
    pagination: dictPagination,
    handleSizeChange: handleDictSizeChange,
    handleCurrentChange: handleDictCurrentChange,
    searchParams
  } = useTable({
    core: {
      apiFn: api.dataList,
      immediate: false,
      apiParams: {
        ...searchForm.value
      },
      columnsFactory: () => [
        { type: 'selection' },
        { prop: 'label', label: '字典标签', useSlot: true },
        { prop: 'value', label: '字典键值' },
        { prop: 'color', label: '颜色' },
        { prop: 'sort', label: '排序' },
        { prop: 'status', label: '状态', saiType: 'dict', saiDict: 'data_status' },
        { prop: 'operation', label: '操作', useSlot: true, width: 120 }
      ]
    }
  })

  // 字典项搜索
  const handleSearch = (params: Record<string, any>) => {
    if (selectedId.value) {
      Object.assign(searchParams, params)
      getDictData()
    }
  }

  // 字典项重置搜索
  const handleReset = () => {
    if (!selectedId.value) {
      ElMessage.warning('请选择字典类型')
      return
    }
    Object.assign(searchParams, {
      label: '',
      value: '',
      status: '',
      type_id: selectedId.value
    })
    getDictData()
  }

  const getColor = (color: string | undefined, type: 'bg' | 'border' | 'text') => {
    // 如果没有指定颜色，使用默认主色调
    if (!color) {
      const colors = {
        bg: 'var(--el-color-primary-light-9)',
        border: 'var(--el-color-primary-light-8)',
        text: 'var(--el-color-primary)'
      }
      return colors[type]
    }

    // 如果是 hex 颜色，转换为 RGB
    let r, g, b
    if (color.startsWith('#')) {
      const hex = color.slice(1)
      r = parseInt(hex.slice(0, 2), 16)
      g = parseInt(hex.slice(2, 4), 16)
      b = parseInt(hex.slice(4, 6), 16)
    } else if (color.startsWith('rgb')) {
      const match = color.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/)
      if (match) {
        r = parseInt(match[1])
        g = parseInt(match[2])
        b = parseInt(match[3])
      } else {
        return color
      }
    } else {
      return color
    }

    // 根据类型返回不同的颜色变体
    switch (type) {
      case 'bg':
        // 背景色 - 更浅的版本
        return `rgba(${r}, ${g}, ${b}, 0.1)`
      case 'border':
        // 边框色 - 中等亮度
        return `rgba(${r}, ${g}, ${b}, 0.3)`
      case 'text':
        // 文字色 - 原始颜色
        return `rgb(${r}, ${g}, ${b})`
      default:
        return color
    }
  }
</script>

<style scoped>
  .left-card :deep(.el-card__body) {
    flex: 1;
    min-height: 0;
    padding: 10px 2px 10px 10px;
  }
</style>
