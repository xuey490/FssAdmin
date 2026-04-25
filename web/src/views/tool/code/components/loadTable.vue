<template>
  <el-drawer
    v-model="visible"
    title="装载数据表"
    size="70%"
    destroy-on-close
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div class="art-full-height">
      <el-alert type="info" :closable="false">
        <template #title>
          <div>1、支持配置多数据源；</div>
          <div>
            2、载入表[sa_shop_category]会自动处理为[SaShopCategory]类，可以编辑对类名进行修改[ShopCategory]
          </div>
        </template>
      </el-alert>

      <div class="flex justify-between items-center mt-4">
        <ElSpace wrap>
          <el-select v-model="searchForm.source" placeholder="切换数据源" style="width: 200px">
            <el-option
              v-for="item in dataSourceList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
          <el-input
            v-model="searchForm.name"
            placeholder="请输入数据表名称"
            style="width: 300px"
            clearable
          />
        </ElSpace>
        <ElSpace wrap>
          <ElButton class="reset-button" @click="handleReset" v-ripple>
            <template #icon>
              <ArtSvgIcon icon="ri:reset-right-line" />
            </template>
            重置
          </ElButton>
          <ElButton type="primary" class="search-button" @click="handleSearch" v-ripple>
            <template #icon>
              <ArtSvgIcon icon="ri:search-line" />
            </template>
            查询
          </ElButton>
        </ElSpace>
      </div>
      <ElCard class="art-table-card" shadow="never">
        <div>
          <ElSpace wrap>
            <ElButton :disabled="selectedRows.length === 0" @click="handleLoadTable" v-ripple>
              <template #icon>
                <ArtSvgIcon icon="ri:check-fill" />
              </template>
              确认选择
            </ElButton>
          </ElSpace>
        </div>
        <!-- 表格 -->
        <ArtTable
          ref="tableRef"
          rowKey="name"
          :loading="loading"
          :data="tableData"
          :columns="columns"
          :pagination="pagination"
          @sort-change="handleSortChange"
          @selection-change="handleSelectionChange"
          @pagination:size-change="handleSizeChange"
          @pagination:current-change="handleCurrentChange"
        />
      </ElCard>
    </div>
  </el-drawer>
</template>

<script setup lang="ts">
  import { ElMessage } from 'element-plus'
  import api from '@/api/safeguard/database'
  import { useTable } from '@/hooks/core/useTable'
  import generate from '@/api/tool/generate'

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

  const selectedRows = ref<Record<string, any>[]>([])
  const dataSourceList = ref<{ label: string; value: string }[]>([])
  const searchForm = ref({
    name: '',
    source: ''
  })

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
    const response = await api.getDataSource()
    dataSourceList.value = response.map((item: any) => ({
      label: item,
      value: item
    }))
    searchForm.value.source = dataSourceList.value[0]?.value || ''
    refreshData()
  }

  /**
   * 获取表格数据
   */
  const refreshData = () => {
    Object.assign(searchParams, searchForm.value)
    getData()
  }

  /**
   * 搜索
   */
  const handleSearch = () => {
    refreshData()
  }

  /**
   * 重置
   */
  const handleReset = () => {
    searchForm.value.name = ''
    refreshData()
  }

  // 表格行选择变化
  const handleSelectionChange = (selection: Record<string, any>[]): void => {
    selectedRows.value = selection
  }

  // 确认选择装载数据表
  const handleLoadTable = async () => {
    if (selectedRows.value.length < 1) {
      ElMessage.info('至少要选择一条数据')
      return
    }
    const names = selectedRows.value.map((item) => ({
      name: item.name,
      comment: item.comment,
      sourceName: item.name
    }))

    await generate.loadTable({
      source: searchForm.value.source,
      names
    })
    ElMessage.success('装载成功')
    emit('success')
    handleClose()
  }

  /**
   * 关闭弹窗
   */
  const handleClose = () => {
    visible.value = false
    selectedRows.value = []
  }

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
      apiFn: api.list,
      immediate: false,
      apiParams: {
        ...searchForm.value
      },
      columnsFactory: () => [
        { type: 'selection' },
        { prop: 'name', label: '表名称' },
        { prop: 'comment', label: '表注释' },
        { prop: 'engine', label: '引擎' },
        { prop: 'collation', label: '编码' },
        { prop: 'create_time', label: '创建时间' }
      ]
    }
  })
</script>
