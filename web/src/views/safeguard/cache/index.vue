<template>
  <div class="page-content">
    <el-row :gutter="20" class="browser-panels">
      <!-- 第一列：第一级键（前缀） -->
      <el-col :span="8">
        <el-card class="art-table-card panel-card" shadow="never">
          <template #header>
            <div class="panel-header">
              <span class="text-lg font-medium">缓存分组</span>
              <el-button size="small" @click="refreshLevel1" :loading="loading.level1" v-ripple>
                <template #icon>
                  <el-icon><Refresh /></el-icon>
                </template>
              </el-button>
            </div>
          </template>

          <div class="panel-content">
            <el-input
              v-model="searchPattern"
              placeholder="搜索键前缀..."
              clearable
              @change="handleSearchChange"
              class="mb-3"
            >
              <template #prefix>
                <el-icon><Search /></el-icon>
              </template>
            </el-input>

            <el-scrollbar height="calc(100vh - 300px)">
              <div
                v-for="item in level1Keys"
                :key="item.key"
                class="key-item"
                :class="{ active: selectedLevel1 === item.key }"
                @click="handleLevel1Click(item)"
              >
                <div class="key-info">
                  <el-icon class="key-icon"><FolderOpened /></el-icon>
                  <span class="key-name">{{ item.key }}</span>
                </div>
                <el-tag size="small" type="info">{{ item.count }}</el-tag>
              </div>

              <el-empty v-if="!loading.level1 && level1Keys.length === 0" description="暂无数据" />
            </el-scrollbar>
          </div>
        </el-card>
      </el-col>

      <!-- 第二列：第二级键 -->
      <el-col :span="8">
        <el-card class="art-table-card panel-card" shadow="never">
          <template #header>
            <div class="panel-header">
              <span class="text-lg font-medium">
                {{ selectedLevel1 || '选择分组' }}
              </span>
              <el-button
                v-if="selectedLevel1"
                size="small"
                type="danger"
                @click="handleDeletePattern(selectedLevel1 + ':*')"
                v-ripple
              >
                <template #icon>
                  <el-icon><Delete /></el-icon>
                </template>
                批量删除
              </el-button>
            </div>
          </template>

          <div class="panel-content">
            <el-scrollbar height="calc(100vh - 260px)">
              <div
                v-for="item in level2Keys"
                :key="item.key"
                class="key-item"
                :class="{ active: selectedLevel2 === item.key }"
                @click="handleLevel2Click(item)"
              >
                <div class="key-info">
                  <el-icon class="key-icon">
                    <component :is="item.type === 'prefix' ? FolderOpened : Document" />
                  </el-icon>
                  <span class="key-name">{{ getShortKey(item.key) }}</span>
                </div>
                <div class="key-actions">
                  <el-tag size="small" type="info" v-if="item.count > 1">{{ item.count }}</el-tag>
                  <el-button
                    size="small"
                    type="danger"
                    text
                    @click.stop="handleDeleteKey(item.key)"
                    v-ripple
                  >
                    <el-icon><Delete /></el-icon>
                  </el-button>
                </div>
              </div>

              <el-empty v-if="!loading.level2 && level2Keys.length === 0" description="请选择左侧分组" />
            </el-scrollbar>
          </div>
        </el-card>
      </el-col>

      <!-- 第三列：第三级键或键详情 -->
      <el-col :span="8">
        <el-card class="art-table-card panel-card" shadow="never">
          <template #header>
            <div class="panel-header">
              <span class="text-lg font-medium">
                {{ selectedLevel2 ? getShortKey(selectedLevel2) : '键详情' }}
              </span>
              <el-button
                v-if="selectedLevel2 && level3Keys.length > 0"
                size="small"
                type="danger"
                @click="handleDeletePattern(selectedLevel2 + ':*')"
                v-ripple
              >
                <template #icon>
                  <el-icon><Delete /></el-icon>
                </template>
                批量删除
              </el-button>
            </div>
          </template>

          <div class="panel-content">
            <!-- 第三级键列表 -->
            <el-scrollbar v-if="level3Keys.length > 0" height="calc(100vh - 260px)">
              <div
                v-for="item in level3Keys"
                :key="item.key"
                class="key-item"
                :class="{ active: selectedKey === item.key }"
                @click="handleKeyClick(item.key)"
              >
                <div class="key-info">
                  <el-icon class="key-icon"><Document /></el-icon>
                  <span class="key-name">{{ getShortKey(item.key) }}</span>
                </div>
                <div class="key-actions">
                  <el-tag size="small" v-if="item.ttl > 0">{{ formatTTL(item.ttl) }}</el-tag>
                  <el-tag size="small" type="info">{{ item.size }}</el-tag>
                  <el-button
                    size="small"
                    type="danger"
                    text
                    @click.stop="handleDeleteKey(item.key)"
                    v-ripple
                  >
                    <el-icon><Delete /></el-icon>
                  </el-button>
                </div>
              </div>
            </el-scrollbar>

            <!-- 键详情 -->
            <div v-else-if="keyInfo" class="key-detail">
              <el-descriptions :column="1" border class="mb-3">
                <el-descriptions-item label="键名" label-class-name="detail-label">
                  <div class="key-name-full">{{ keyInfo.key }}</div>
                </el-descriptions-item>
                <el-descriptions-item label="类型" label-class-name="detail-label">
                  <el-tag size="small">{{ keyInfo.type }}</el-tag>
                </el-descriptions-item>
                <el-descriptions-item label="TTL" label-class-name="detail-label">
                  <el-tag v-if="keyInfo.ttl === -1" type="success" size="small">永不过期</el-tag>
                  <el-tag v-else-if="keyInfo.ttl === -2" type="danger" size="small">键不存在</el-tag>
                  <el-tag v-else type="warning" size="small">{{ formatTTL(keyInfo.ttl) }}</el-tag>
                </el-descriptions-item>
                <el-descriptions-item label="大小" label-class-name="detail-label">
                  {{ keyInfo.size }}
                </el-descriptions-item>
              </el-descriptions>

              <div class="key-value">
                <div class="value-header">
                  <span class="text-base font-medium">值内容</span>
                  <el-button size="small" type="danger" @click="handleDeleteKey(keyInfo.key)" v-ripple>
                    <template #icon>
                      <el-icon><Delete /></el-icon>
                    </template>
                    删除
                  </el-button>
                </div>
                <el-input
                  v-if="keyInfo.type === 'string'"
                  v-model="keyInfo.value"
                  type="textarea"
                  :rows="12"
                  readonly
                  class="mt-3"
                />
                <pre v-else class="value-content">{{ JSON.stringify(keyInfo.value, null, 2) }}</pre>
              </div>
            </div>

            <el-empty v-else description="请选择键查看详情" />
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Refresh, Search, FolderOpened, Document, Delete } from '@element-plus/icons-vue'
import { RedisBrowserService } from '@/api/monitor/redis-browser'

// 响应式数据
const searchPattern = ref('*')
const level1Keys = ref<any[]>([])
const level2Keys = ref<any[]>([])
const level3Keys = ref<any[]>([])
const keyInfo = ref<any>(null)

const selectedLevel1 = ref('')
const selectedLevel2 = ref('')
const selectedKey = ref('')

const loading = reactive({
  level1: false,
  level2: false,
  level3: false,
  keyInfo: false
})

// 加载第一级键
const loadLevel1 = async () => {
  loading.level1 = true
  try {
    const data = await RedisBrowserService.getLevel1(searchPattern.value)
    level1Keys.value = data || []
  } catch (error: any) {
    ElMessage.error(error.message || '加载失败')
  } finally {
    loading.level1 = false
  }
}

// 加载第二级键
const loadLevel2 = async (prefix: string) => {
  loading.level2 = true
  try {
    const data = await RedisBrowserService.getLevel2(prefix)
    level2Keys.value = data || []
  } catch (error: any) {
    ElMessage.error(error.message || '加载失败')
  } finally {
    loading.level2 = false
  }
}

// 加载第三级键
const loadLevel3 = async (prefix: string) => {
  loading.level3 = true
  try {
    const data = await RedisBrowserService.getLevel3(prefix)
    level3Keys.value = data || []
  } catch (error: any) {
    ElMessage.error(error.message || '加载失败')
  } finally {
    loading.level3 = false
  }
}

// 加载键详情
const loadKeyInfo = async (key: string) => {
  loading.keyInfo = true
  try {
    const data = await RedisBrowserService.getKeyInfo(key)
    keyInfo.value = data || null
  } catch (error: any) {
    ElMessage.error(error.message || '加载失败')
  } finally {
    loading.keyInfo = false
  }
}

// 处理第一级点击
const handleLevel1Click = (item: any) => {
  selectedLevel1.value = item.key
  selectedLevel2.value = ''
  selectedKey.value = ''
  level2Keys.value = []
  level3Keys.value = []
  keyInfo.value = null
  loadLevel2(item.key)
}

// 处理第二级点击
const handleLevel2Click = (item: any) => {
  selectedLevel2.value = item.key
  selectedKey.value = ''
  level3Keys.value = []
  keyInfo.value = null

  if (item.type === 'prefix') {
    // 有第三级，加载第三级键
    loadLevel3(item.key)
  } else {
    // 没有第三级，直接显示键详情
    loadKeyInfo(item.fullKey || item.key)
  }
}

// 处理键点击
const handleKeyClick = (key: string) => {
  selectedKey.value = key
  loadKeyInfo(key)
}

// 删除单个键
const handleDeleteKey = (key: string) => {
  ElMessageBox.confirm(`确定要删除键 "${key}" 吗？`, '删除确认', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      await RedisBrowserService.deleteKey(key)
      ElMessage.success('删除成功')
      refreshCurrentLevel()
    } catch (error: any) {
      ElMessage.error(error.message || '删除失败')
    }
  })
}

// 批量删除（按模式）
const handleDeletePattern = (pattern: string) => {
  ElMessageBox.confirm(`确定要删除所有匹配 "${pattern}" 的键吗？`, '批量删除确认', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'error'
  }).then(async () => {
    try {
      await RedisBrowserService.deleteByPattern(pattern)
      ElMessage.success('删除成功')
      refreshCurrentLevel()
    } catch (error: any) {
      ElMessage.error(error.message || '删除失败')
    }
  })
}

// 刷新当前层级
const refreshCurrentLevel = () => {
  if (selectedKey.value) {
    // 刷新第三级
    loadLevel3(selectedLevel2.value)
  } else if (selectedLevel2.value) {
    // 刷新第二级
    loadLevel2(selectedLevel1.value)
  } else if (selectedLevel1.value) {
    // 刷新第一级
    loadLevel1()
  } else {
    // 刷新根级
    loadLevel1()
  }
}

// 刷新第一级
const refreshLevel1 = () => {
  selectedLevel1.value = ''
  selectedLevel2.value = ''
  selectedKey.value = ''
  level2Keys.value = []
  level3Keys.value = []
  keyInfo.value = null
  loadLevel1()
}

// 搜索变化
const handleSearchChange = () => {
  refreshLevel1()
}

// 获取短键名（去掉前缀）
const getShortKey = (key: string) => {
  const parts = key.split(':')
  return parts[parts.length - 1] || key
}

// 格式化 TTL
const formatTTL = (seconds: number) => {
  if (seconds < 60) return `${seconds}秒`
  if (seconds < 3600) return `${Math.floor(seconds / 60)}分钟`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}小时`
  return `${Math.floor(seconds / 86400)}天`
}

onMounted(() => {
  loadLevel1()
})
</script>

<style lang="scss" scoped>
.page-content {
  .browser-panels {
    min-height: calc(100vh - 180px);
  }

  .panel-card {
    height: 100%;
    
    :deep(.el-card__body) {
      padding: 0;
    }
  }

  .panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .panel-content {
    padding: 20px;
  }

  .key-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 14px;
    margin-bottom: 10px;
    background: var(--el-fill-color-light);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    border: 1px solid transparent;

    &:hover {
      background: var(--el-fill-color);
      transform: translateX(2px);
    }

    &.active {
      background: var(--el-color-primary-light-9);
      border-color: var(--el-color-primary);
      box-shadow: 0 2px 8px rgba(64, 158, 255, 0.15);
    }

    .key-info {
      display: flex;
      align-items: center;
      flex: 1;
      min-width: 0;

      .key-icon {
        margin-right: 10px;
        color: var(--el-color-primary);
        flex-shrink: 0;
        font-size: 18px;
      }

      .key-name {
        font-size: 14px;
        color: var(--el-text-color-primary);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-weight: 500;
      }
    }

    .key-actions {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
    }
  }

  .key-detail {
    .detail-label {
      width: 100px;
      font-weight: 500;
    }

    .key-name-full {
      word-break: break-all;
      font-size: 13px;
      color: var(--el-text-color-regular);
    }

    .key-value {
      .value-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--el-border-color-lighter);
      }

      .value-content {
        padding: 16px;
        background: var(--el-fill-color-light);
        border-radius: 8px;
        font-size: 13px;
        line-height: 1.8;
        max-height: 500px;
        overflow: auto;
        margin-top: 12px;
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        color: var(--el-text-color-regular);
        border: 1px solid var(--el-border-color-light);
      }

      :deep(.el-textarea__inner) {
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 13px;
        line-height: 1.8;
      }
    }
  }

  :deep(.el-scrollbar__view) {
    padding-right: 10px;
  }

  :deep(.el-empty) {
    padding: 40px 0;
  }
}
</style>
