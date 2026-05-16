<template>
  <el-dialog
    v-model="visible"
    title="选择图片"
    width="1024px"
    :close-on-click-modal="false"
    @closed="onClosed"
  >
    <div class="resource-dialog">
      <!-- 搜索和筛选 -->
      <div class="dialog-header">
        <div class="flex items-center justify-between gap-2 flex-1">
          <el-tree-select
            class="search-tree"
            v-model="searchForm.category_id"
            :data="categoryList"
            node-key="id"
            :props="{ label: 'category_name', value: 'id' }"
            :render-after-expand="false"
            check-strictly
            clearable
          />
          <el-input v-model="searchForm.keywords" placeholder="搜索图片名称" clearable>
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
        </div>
        <el-button type="primary" :icon="Refresh" @click="loadResources"> 搜索 </el-button>
        <el-upload
          class="upload-btn"
          :show-file-list="false"
          :http-request="handleUpload"
          :before-upload="beforeUpload"
          accept="image/*"
        >
          <el-button type="success" :icon="UploadFilled">上传图片</el-button>
        </el-upload>
      </div>

      <!-- 图片列表 -->
      <div class="image-list">
        <div v-loading="loading" class="image-grid">
          <div
            v-for="item in imageList"
            :key="item.id"
            class="image-item"
            :class="{ selected: selectedIds.has(item.id) }"
            @click="selectImage(item)"
          >
            <el-image :src="item.url" fit="cover" class="grid-image" />
            <div class="image-info">
              <div class="image-name" :title="item.origin_name">{{ item.origin_name }}</div>
              <div class="image-size">{{ item.size_info }}</div>
            </div>
            <div v-if="selectedIds.has(item.id)" class="selected-badge">
              <el-icon><Check /></el-icon>
            </div>
          </div>

          <!-- 空状态 -->
          <el-empty
            v-if="!loading && imageList.length === 0"
            description="暂无图片资源"
            class="empty-placeholder"
          />
        </div>
      </div>

      <!-- 分页 -->
      <div class="pagination">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :total="total"
          :page-sizes="[14, 28, 42, 56]"
          layout="total, sizes, prev, pager, next, jumper"
          @current-change="loadResources"
          @size-change="loadResources"
        />
      </div>
    </div>

    <template #footer>
      <el-button @click="visible = false">取消</el-button>
      <el-button type="primary" @click="confirmSelection" :disabled="selectedItems.length === 0">
        确定
      </el-button>
    </template>
  </el-dialog>
</template>

<script lang="ts" setup>
  import { ref, watch } from 'vue'
  import { Search, Refresh, Check, UploadFilled } from '@element-plus/icons-vue'
  import { ElMessage } from 'element-plus'
  import type { UploadRequestOptions, UploadProps } from 'element-plus'
  import { getResourceCategory, getResourceList, uploadImage } from '@/api/auth'

  defineOptions({ name: 'SaImageDialog' })

  // Props 定义
  interface Props {
    multiple?: boolean // 是否多选
    limit?: number // 多选限制
    initialUrls?: string | string[] // 初始选中的 URL（用于回显）
  }

  const props = withDefaults(defineProps<Props>(), {
    multiple: false,
    limit: 3,
    initialUrls: ''
  })

  // visible 使用 defineModel
  const visible = defineModel<boolean>('visible', { default: false })

  // Emits 定义
  const emit = defineEmits<{
    confirm: [value: string | string[]]
  }>()

  // 图片资源接口
  interface ImageResource {
    id: string | number
    origin_name: string
    url: string
    size_info: string
    type?: string
    createTime?: string
  }

  // 状态
  const loading = ref(false)
  const searchForm = ref({
    keywords: '',
    category_id: ''
  })
  const selectedIds = ref<Set<string | number>>(new Set())
  const unselectedIds = ref<Set<string | number>>(new Set())
  const selectedItems = ref<ImageResource[]>([])
  const imageList = ref<ImageResource[]>([])
  const categoryList = ref<any>([])
  const currentPage = ref(1)
  const pageSize = ref(14)
  const total = ref(0)

  // 监听弹窗打开
  watch(
    () => visible.value,
    (newVal) => {
      if (newVal) {
        // 初始化选中状态
        selectedIds.value.clear()
        unselectedIds.value.clear()
        selectedItems.value = []

        if (imageList.value.length === 0) {
          loadResources()
        } else {
          syncSelectionFromInitial()
        }
      }
    }
  )

  // 根据 initialUrls 同步选中状态
  const syncSelectionFromInitial = () => {
    const urls = Array.isArray(props.initialUrls)
      ? props.initialUrls
      : props.initialUrls
        ? [props.initialUrls]
        : []
    if (!urls.length) return

    imageList.value.forEach((item) => {
      if (
        urls.includes(item.url) &&
        !unselectedIds.value.has(item.id) &&
        !selectedIds.value.has(item.id)
      ) {
        selectedIds.value.add(item.id)
        selectedItems.value.push(item)
      }
    })
  }

  // 加载资源列表
  const loadResources = async () => {
    loading.value = true
    try {
      const category = await getResourceCategory({ tree: 'true' })
      console.log(searchForm)
      categoryList.value = category || []
      const response: any = await getResourceList({
        page: currentPage.value,
        limit: pageSize.value,
        origin_name: searchForm.value.keywords,
        category_id: searchForm.value.category_id
      })

      const data = response
      imageList.value = data?.list || []
      total.value = data?.total || imageList.value.length

      syncSelectionFromInitial()
    } catch (error: any) {
      console.error('加载图片资源失败:', error)
      ElMessage.error('加载图片资源失败: ' + (error.message || ''))
    } finally {
      loading.value = false
    }
  }

  // 选择图片
  const selectImage = (item: ImageResource) => {
    if (props.multiple) {
      if (selectedIds.value.has(item.id)) {
        selectedIds.value.delete(item.id)
        unselectedIds.value.add(item.id)
        const index = selectedItems.value.findIndex((i) => i.id === item.id)
        if (index > -1) selectedItems.value.splice(index, 1)
      } else {
        if (selectedIds.value.size >= props.limit) {
          ElMessage.warning(`最多只能选择 ${props.limit} 张图片`)
          return
        }
        selectedIds.value.add(item.id)
        unselectedIds.value.delete(item.id)
        selectedItems.value.push(item)
      }
    } else {
      selectedIds.value.clear()
      selectedItems.value = []
      selectedIds.value.add(item.id)
      selectedItems.value.push(item)
    }
  }

  // 确认选择
  const confirmSelection = () => {
    if (selectedItems.value.length === 0) {
      ElMessage.warning('请选择图片')
      return
    }

    const urls = selectedItems.value.map((item) => item.url)
    const result = props.multiple ? urls : urls[0]

    emit('confirm', result)
    visible.value = false
    ElMessage.success('图片选择成功')
  }

  // 上传前验证
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

  // 处理上传
  const handleUpload = async (options: UploadRequestOptions) => {
    const { file } = options
    loading.value = true
    try {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('category_id', searchForm.value.category_id || '1')

      await uploadImage(formData)
      ElMessage.success('上传成功')

      currentPage.value = 1
      await loadResources()
    } catch (error: any) {
      console.error('上传失败:', error)
      ElMessage.error(error.message || '上传失败')
    } finally {
      loading.value = false
    }
  }

  // 弹窗关闭时重置
  const onClosed = () => {
    // 可选：重置搜索等状态
  }
</script>

<style scoped lang="scss">
  .resource-dialog {
    .dialog-header {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;

      .search-tree {
        width: 250px;
      }
    }

    .image-list {
      min-height: 450px;
      max-height: 660px;
      overflow-y: auto;
    }

    .image-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      gap: 10px;
      padding: 10px;

      .image-item {
        position: relative;
        border: 2px solid transparent;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s;
        background: #f5f7fa;

        &:hover {
          border-color: var(--el-color-primary);
          transform: translateY(-2px);
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        &.selected {
          border-color: var(--el-color-primary);
          box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.2);
        }

        .grid-image {
          width: 100%;
          height: 100px;
        }

        .image-info {
          padding: 4px 8px;
          background: #fff;

          .image-name {
            font-size: 12px;
            color: #303133;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
          }

          .image-size {
            font-size: 11px;
            color: #909399;
          }
        }

        .selected-badge {
          position: absolute;
          top: 8px;
          right: 8px;
          width: 24px;
          height: 24px;
          background: var(--el-color-primary);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          color: #fff;
          font-size: 14px;
        }
      }

      .empty-placeholder {
        grid-column: 1 / -1;
      }
    }

    .pagination {
      margin-top: 0px;
      display: flex;
      justify-content: center;
    }
  }
</style>
