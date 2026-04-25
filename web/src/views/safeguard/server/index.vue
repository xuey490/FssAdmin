<template>
  <div class="page-content mb-5">
    <el-row :gutter="20">
      <!-- 内存 信息 -->
      <el-col :span="24" class="mb-4">
        <el-card class="art-table-card" shadow="never">
          <template #header>
            <span class="text-lg font-medium">内存信息</span>
          </template>
          <div class="flex justify-between">
            <div class="flex-1">
              <el-descriptions :column="1" border>
                <el-descriptions-item label="总内存">
                  {{ serverInfo.memory.total }}
                </el-descriptions-item>
                <el-descriptions-item label="已使用内存">
                  {{ serverInfo.memory.used }}
                </el-descriptions-item>
                <el-descriptions-item label="PHP使用内存">
                  {{ serverInfo.memory.php }}
                </el-descriptions-item>
                <el-descriptions-item label="空闲内存">
                  {{ serverInfo.memory.free }}
                </el-descriptions-item>
                <el-descriptions-item label="使用率">
                  {{ serverInfo.memory.rate }}
                </el-descriptions-item>
              </el-descriptions>
            </div>
            <div class="w-80 p-4 text-center">
              <div class="pb-3.5">
                <span class="text-base font-medium">内存使用率</span>
              </div>
              <el-progress
                type="dashboard"
                :percentage="toSafePercentage(serverInfo.memory.rate)"
              />
            </div>
          </div>
        </el-card>
      </el-col>

      <!-- PHP 信息 -->
      <el-col :span="24" class="mb-4">
        <el-card class="art-table-card" shadow="never">
          <template #header>
            <span class="text-lg font-medium">PHP及环境信息</span>
          </template>
          <div class="py-2">
            <el-descriptions :column="2" border class="php-config" v-if="serverInfo.phpEnv">
              <el-descriptions-item
                label="PHP版本"
                label-class-name="php-label"
                content-class-name="php-content"
              >
                {{ serverInfo.phpEnv?.php_version }}
              </el-descriptions-item>
              <el-descriptions-item
                label="操作系统"
                label-class-name="php-label"
                content-class-name="php-content"
              >
                {{ serverInfo.phpEnv?.os }}
              </el-descriptions-item>
              <el-descriptions-item
                label="项目路径"
                label-class-name="php-label"
                content-class-name="php-content"
              >
                <div class="project-path">{{ serverInfo.phpEnv?.project_path }}</div>
              </el-descriptions-item>
              <el-descriptions-item
                label="内存限制"
                label-class-name="php-label"
                content-class-name="php-content"
              >
                {{ serverInfo.phpEnv?.memory_limit }}
              </el-descriptions-item>
              <el-descriptions-item
                label="最大执行时间"
                label-class-name="php-label"
                content-class-name="php-content"
              >
                {{
                  serverInfo.phpEnv?.max_execution_time === '0'
                    ? '无限制'
                    : `${serverInfo.phpEnv?.max_execution_time}秒`
                }}
              </el-descriptions-item>
              <el-descriptions-item
                label="错误报告"
                label-class-name="php-label"
                content-class-name="php-content"
              >
                {{ serverInfo.phpEnv?.error_reporting }}
              </el-descriptions-item>
              <el-descriptions-item
                label="显示错误"
                label-class-name="php-label"
                content-class-name="php-content"
              >
                {{ serverInfo.phpEnv?.display_errors }}
              </el-descriptions-item>
              <el-descriptions-item
                label="上传限制"
                label-class-name="php-label"
                content-class-name="php-content"
              >
                {{ serverInfo.phpEnv?.upload_max_filesize }}
              </el-descriptions-item>
              <el-descriptions-item
                label="POST大小"
                label-class-name="php-label"
                content-class-name="php-content"
              >
                {{ serverInfo.phpEnv?.post_max_size }}
              </el-descriptions-item>
              <el-descriptions-item
                label="扩展目录"
                label-class-name="php-label"
                content-class-name="php-content"
              >
                {{ serverInfo.phpEnv?.extension_dir }}
              </el-descriptions-item>
              <el-descriptions-item
                label="扩展目录"
                label-class-name="php-label"
                content-class-name="php-content"
              >
                {{ serverInfo.phpEnv?.loaded_extensions }}
              </el-descriptions-item>
            </el-descriptions>
          </div>
        </el-card>
      </el-col>

      <!-- 磁盘 信息 -->
      <el-col :span="24" class="mb-4">
        <el-card class="art-table-card" shadow="never">
          <template #header>
            <div class="card-header">
              <span><i class="el-icon-disk"></i> 磁盘监控</span>
            </div>
          </template>

          <el-table :data="serverInfo.disk" style="width: 100%">
            <el-table-column prop="filesystem" label="文件系统" />
            <el-table-column prop="size" label="总大小" />
            <el-table-column prop="used" label="已用空间" />
            <el-table-column prop="available" label="可用空间" />
            <el-table-column prop="use_percentage" label="使用率">
              <template #default="{ row }">
                <el-progress
                  :percentage="toSafePercentage(row.use_percentage)"
                  :stroke-width="12"
                  :show-text="true"
                />
              </template>
            </el-table-column>
            <el-table-column prop="mounted_on" label="挂载点" />
          </el-table>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup lang="ts">
  import api from '@/api/safeguard/server'
  import { onMounted } from 'vue'

  const loading = ref(false)

  const serverInfo = reactive({
    memory: {
      total: '',
      used: '',
      rate: '',
      php: '',
      free: ''
    },
    disk: [] as any[],
    phpEnv: {} as any
  })

  const toSafePercentage = (value: unknown): number => {
    const raw = String(value ?? '').replace('%', '').trim()
    const num = Number.parseFloat(raw)
    if (Number.isNaN(num)) return 0
    if (num < 0) return 0
    if (num > 100) return 100
    return Number(num.toFixed(2))
  }

  /**
   * 更新服务器信息
   */
  const updateServer = async () => {
    loading.value = true
    try {
      const data = await api.monitor({})
      serverInfo.memory = data.memory
      serverInfo.phpEnv = data.phpEnv
      serverInfo.disk = data.disk
    } finally {
      loading.value = false
    }
  }

  onMounted(() => {
    updateServer()
  })
</script>

<style lang="scss" scoped>
  :deep(.el-descriptions__label) {
    width: 200px;
  }
  :deep(.el-descriptions__content) {
    width: 400px;
  }
</style>
