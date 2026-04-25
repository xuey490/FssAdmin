<template>
  <div class="art-full-height">
    <div class="box-border flex gap-4 h-full max-md:block max-md:gap-0 max-md:h-auto">
      <div class="flex-shrink-0 w-64 h-full max-md:w-full max-md:h-auto max-md:mb-5">
        <ElCard class="tree-card art-card-xs flex flex-col h-full mt-0" shadow="never">
          <template #header>
            <div class="flex justify-between items-center">
              <b>附件分类</b>
              <SaButton
                v-permission="'core:attachment:edit'"
                type="primary"
                @click="categoryShowDialog('add')"
              />
            </div>
          </template>
          <ElScrollbar>
            <ElTree
              :data="treeData"
              :props="{ children: 'children', label: 'label' }"
              node-key="id"
              default-expand-all
              highlight-current
              :expand-on-click-node="false"
              @node-click="handleNodeClick"
            >
              <template #default="{ node, data }">
                <div class="flex items-center justify-between w-full" v-if="data.id > 1">
                  <span>{{ node.label }}</span>
                  <div class="tree-node-actions">
                    <SaButton
                      v-permission="'core:attachment:edit'"
                      type="secondary"
                      @click="categoryShowDialog('edit', data)"
                    />
                    <SaButton
                      v-permission="'core:attachment:edit'"
                      type="error"
                      @click="categoryDeleteRow(data, categoryApi.delete, getCategoryList)"
                    />
                  </div>
                </div>
              </template>
            </ElTree>
          </ElScrollbar>
        </ElCard>
      </div>

      <div class="flex flex-col flex-grow min-w-0">
        <ElCard class="art-table-card !mt-0" shadow="never">
          <!-- 表格头部 -->
          <div class="flex justify-between items-center mb-4">
            <ElSpace wrap>
              <ElUpload
                v-permission="'core:system:uploadImage'"
                class="upload-btn"
                :show-file-list="false"
                :http-request="handleUpload"
                :before-upload="beforeUpload"
                accept="image/*"
              >
                <ElButton :icon="UploadFilled">上传图片</ElButton>
              </ElUpload>
              <ElButton
                v-permission="'core:attachment:edit'"
                :disabled="selectedRows.length === 0"
                @click="deleteSelectedRows(api.delete, refreshData)"
                v-ripple
              >
                <template #icon>
                  <ArtSvgIcon icon="ri:delete-bin-5-line" />
                </template>
                删除
              </ElButton>
              <ElButton
                v-permission="'core:attachment:edit'"
                :disabled="selectedRows.length === 0"
                @click="moveDialogVisible = true"
                v-ripple
              >
                <template #icon>
                  <ArtSvgIcon icon="ri:swap-box-line" />
                </template>
                移动
              </ElButton>
            </ElSpace>
            <ElSpace wrap>
              <SaSelect
                v-model="searchForm.storage_mode"
                placeholder="请选择存储模式"
                dict="upload_mode"
                @change="handleSearch"
                clearable
                style="width: 160px"
              />
              <ElInput
                v-model="searchForm.origin_name"
                placeholder="请输入文件名称"
                :suffix-icon="Search"
                @keyup.enter="handleSearch"
                @clear="handleSearch"
                clearable
                style="width: 240px"
              />
            </ElSpace>
          </div>

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
            <template #operation="{ row }">
              <div class="flex gap-2">
                <SaButton
                  v-permission="'core:attachment:edit'"
                  type="secondary"
                  @click="showDialog('edit', row)"
                />
                <SaButton
                  v-permission="'core:attachment:edit'"
                  type="error"
                  @click="deleteRow(row, api.delete, refreshData)"
                />
              </div>
            </template>
          </ArtTable>
        </ElCard>
      </div>
    </div>

    <!-- 分类弹窗 -->
    <CategoryDialog
      v-model="categoryDialogVisible"
      :dialog-type="categoryDialogType"
      :data="categoryDialogData"
      @success="getCategoryList"
    />

    <!-- 移动弹窗 -->
    <MoveDialog v-model="moveDialogVisible" :selected-rows="selectedRows" @success="refreshData" />

    <!-- 表单弹窗 -->
    <EditDialog
      v-model="dialogVisible"
      :dialog-type="dialogType"
      :data="dialogData"
      @success="refreshData"
    />
  </div>
</template>

<script setup lang="ts">
  import api from '@/api/safeguard/attachment'
  import categoryApi from '@/api/safeguard/category'
  import { uploadImage } from '@/api/auth'
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import { Search, UploadFilled } from '@element-plus/icons-vue'
  import type { UploadRequestOptions, UploadProps } from 'element-plus'
  import EditDialog from './modules/edit-dialog.vue'
  import CategoryDialog from './modules/category-dialog.vue'
  import MoveDialog from './modules/move-dialog.vue'

  /** 附件分类数据 */
  const treeData = ref([])

  /** 获取附件分类数据 */
  const getCategoryList = () => {
    categoryApi.list({ tree: true }).then((data: any) => {
      treeData.value = data
    })
  }

  /**
   * 切换附件分类
   * @param data
   */
  const handleNodeClick = (data: any) => {
    if (data.id === 1) {
      searchParams.category_id = undefined
    } else {
      searchParams.category_id = data.id
    }
    getData()
  }

  /** 附件分类弹窗相关 */
  const {
    dialogType: categoryDialogType,
    dialogVisible: categoryDialogVisible,
    dialogData: categoryDialogData,
    showDialog: categoryShowDialog,
    deleteRow: categoryDeleteRow
  } = useSaiAdmin()

  /** 移动弹窗相关 */
  const moveDialogVisible = ref(false)

  /** 附件弹窗相关 */
  const {
    dialogType,
    dialogVisible,
    dialogData,
    showDialog,
    selectedRows,
    handleSelectionChange,
    deleteRow,
    deleteSelectedRows
  } = useSaiAdmin()

  /** 附件搜索表单 */
  const searchForm = ref({
    origin_name: undefined,
    storage_mode: undefined,
    category_id: undefined,
    orderField: 'create_time',
    orderType: 'desc'
  })

  /** 附件表格相关 */
  const {
    columns,
    data,
    loading,
    pagination,
    getData,
    searchParams,
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
        { type: 'selection' },
        { prop: 'url', label: '预览', saiType: 'image', width: 80 },
        { prop: 'origin_name', label: '文件名称', minWidth: 160, showOverflowTooltip: true },
        {
          prop: 'storage_mode',
          label: '存储模式',
          width: 100,
          saiType: 'dict',
          saiDict: 'upload_mode'
        },
        { prop: 'mime_type', label: '文件类型', width: 160, showOverflowTooltip: true },
        { prop: 'size_info', label: '文件大小', width: 100 },
        { prop: 'create_time', label: '上传时间', width: 180, sortable: true },
        { prop: 'operation', label: '操作', width: 100, fixed: 'right', useSlot: true }
      ]
    }
  })

  /** 附件搜索 */
  const handleSearch = () => {
    Object.assign(searchParams, searchForm.value)
    getData()
  }

  /** 附件上传前验证 */
  const beforeUpload: UploadProps['beforeUpload'] = (file) => {
    const isImage = file.type.startsWith('image/')
    if (!isImage) {
      ElMessage.error('只能上传图片文件!')
      return false
    }
    const isLt5M = file.size / 1024 / 1024 < 5
    if (!isLt5M) {
      ElMessage.error('图片大小不能超过 5MB!')
      return false
    }
    return true
  }

  /** 附件处理上传 */
  const handleUpload = async (options: UploadRequestOptions) => {
    const { file } = options
    try {
      const formData = new FormData()
      formData.append('file', file)
      await uploadImage(formData)
      ElMessage.success('上传成功')
      refreshData()
    } catch (error: any) {
      console.error('上传失败:', error)
      ElMessage.error(error.message || '上传失败')
    }
  }

  /** 初始化附件分类数据 */
  onMounted(() => {
    getCategoryList()
  })
</script>

<style lang="scss" scoped>
  .tree-node-actions {
    opacity: 0;
    transition: opacity 0.2s;
    display: flex;
    gap: 4px;
  }

  .el-tree-node__content:hover .tree-node-actions {
    opacity: 1;
  }

  :deep(.el-tree-node__content) {
    height: 32px;
  }
</style>
