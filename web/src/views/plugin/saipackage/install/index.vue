<template>
  <div class="art-full-height">
    <ElCard class="flex flex-col flex-1 min-h-0 art-table-card" shadow="never">
      <!-- 提示警告 -->
      <ElAlert type="warning" :closable="false">
        仅支持上传由插件市场下载的zip压缩包进行安装，请您务必确认插件包文件来自官方渠道或经由官方认证的插件作者！
      </ElAlert>

      <!-- 工具栏 -->
      <div class="flex flex-wrap items-center my-2">
        <ElButton @click="getList" v-ripple>
          <template #icon>
            <ArtSvgIcon icon="ri:refresh-line" />
          </template>
        </ElButton>
        <ElButton @click="handleUpload" v-ripple>
          <template #icon>
            <ArtSvgIcon icon="ri:upload-line" />
          </template>
          上传插件包
        </ElButton>
        <ElButton type="danger" @click="handleTerminal" v-ripple>
          <template #icon>
            <ArtSvgIcon icon="ri:terminal-box-line" />
          </template>
        </ElButton>

        <div class="flex items-center gap-1 ml-auto">
          <div class="version-title">saiadmin版本</div>
          <div class="version-value">{{ version?.saiadmin_version?.describe }}</div>
          <div class="version-title">状态</div>
          <div
            class="version-value"
            :class="[
              version?.saiadmin_version?.notes === '正常' ? 'text-green-500' : 'text-red-500'
            ]"
          >
            {{ version?.saiadmin_version?.notes }}
          </div>
          <div class="version-title">saipackage安装器</div>
          <div class="version-value">{{ version?.saipackage_version?.describe }}</div>
          <div class="version-title">状态</div>
          <div
            class="version-value"
            :class="[
              version?.saipackage_version?.notes === '正常' ? 'text-green-500' : 'text-red-500'
            ]"
          >
            {{ version?.saipackage_version?.notes }}
          </div>
        </div>
      </div>

      <!-- Tab切换 -->
      <ElTabs v-model="activeTab" type="border-card">
        <!-- 本地安装 Tab -->
        <ElTabPane label="本地安装" name="local">
          <ArtTable
            :loading="loading"
            :data="installList"
            :columns="columns"
            :show-table-header="false"
          >
            <!-- 插件标识列 -->
            <template #app="{ row }">
              <ElLink :href="row.website" target="_blank" type="primary">{{ row.app }}</ElLink>
            </template>

            <!-- 状态列 -->
            <template #state="{ row }">
              <ElTag v-if="row.state === 0" type="danger">已卸载</ElTag>
              <ElTag v-else-if="row.state === 1" type="success">已安装</ElTag>
              <ElTag v-else-if="row.state === 2" type="primary">等待安装</ElTag>
              <ElTag v-else-if="row.state === 4" type="warning">等待安装依赖</ElTag>
            </template>

            <!-- 前端依赖列 -->
            <template #npm="{ row }">
              <ElLink
                v-if="row.npm_dependent_wait_install === 1"
                type="primary"
                @click="handleExecFront(row)"
              >
                <ArtSvgIcon icon="ri:download-line" class="mr-1" />点击安装
              </ElLink>
              <ElTag v-else-if="row.state === 2" type="info">-</ElTag>
              <ElTag v-else type="success">已安装</ElTag>
            </template>

            <!-- 后端依赖列 -->
            <template #composer="{ row }">
              <ElLink
                v-if="row.composer_dependent_wait_install === 1"
                type="primary"
                @click="handleExecBackend(row)"
              >
                <ArtSvgIcon icon="ri:download-line" class="mr-1" />点击安装
              </ElLink>
              <ElTag v-else-if="row.state === 2" type="info">-</ElTag>
              <ElTag v-else type="success">已安装</ElTag>
            </template>

            <!-- 操作列 -->
            <template #operation="{ row }">
              <ElSpace>
                <ElPopconfirm
                  title="确定要安装当前插件吗?"
                  @confirm="handleInstall(row)"
                  confirm-button-text="确定"
                  cancel-button-text="取消"
                >
                  <template #reference>
                    <ElLink type="warning">
                      <ArtSvgIcon icon="ri:apps-2-add-line" class="mr-1" />安装
                    </ElLink>
                  </template>
                </ElPopconfirm>
                <ElPopconfirm
                  title="确定要卸载当前插件吗?"
                  @confirm="handleUninstall(row)"
                  confirm-button-text="确定"
                  cancel-button-text="取消"
                >
                  <template #reference>
                    <ElLink type="danger">
                      <ArtSvgIcon icon="ri:delete-bin-5-line" class="mr-1" />卸载
                    </ElLink>
                  </template>
                </ElPopconfirm>
              </ElSpace>
            </template>
          </ArtTable>
        </ElTabPane>

        <!-- 在线商店 Tab -->
        <ElTabPane label="在线商店" name="online">
          <!-- 搜索栏 -->
          <div class="flex flex-wrap items-center gap-4 mb-4">
            <ElInput
              v-model="searchForm.keywords"
              placeholder="请输入关键词"
              clearable
              class="!w-48"
              @keyup.enter="fetchOnlineApps"
            >
              <template #prefix>
                <ArtSvgIcon icon="ri:search-line" />
              </template>
            </ElInput>
            <ElSelect v-model="searchForm.type" placeholder="类型" clearable class="!w-32">
              <ElOption label="全部" value="" />
              <ElOption label="插件" :value="1" />
              <ElOption label="系统" :value="2" />
              <ElOption label="组件" :value="3" />
              <ElOption label="项目" :value="4" />
            </ElSelect>
            <ElSelect v-model="searchForm.price" placeholder="价格" class="!w-32">
              <ElOption label="全部" value="all" />
              <ElOption label="免费" value="free" />
              <ElOption label="付费" value="paid" />
            </ElSelect>
            <ElButton type="primary" @click="fetchOnlineApps">搜索</ElButton>

            <!-- 商店账号 -->
            <div class="ml-auto flex items-center gap-2">
              <template v-if="storeUser">
                <ElAvatar :size="24">
                  <img v-if="storeUser.avatar" :src="storeUser.avatar" />
                  <ArtSvgIcon v-else icon="ri:user-line" />
                </ElAvatar>
                <span class="font-medium">{{ storeUser.nickname || storeUser.username }}</span>
                <ElButton size="small" @click="showPurchasedApps">已购应用</ElButton>
                <ElButton size="small" @click="handleLogout">退出</ElButton>
              </template>
              <template v-else>
                <ElButton size="small" @click="handleLogin">登录</ElButton>
                <ElButton size="small" @click="handleRegister">注册</ElButton>
                <span class="text-sm text-gray-400">来管理已购插件</span>
              </template>
            </div>
          </div>

          <!-- 应用网格 -->
          <div class="app-grid">
            <div
              v-for="item in onlineApps"
              :key="item.id"
              class="app-card"
              @click="showDetail(item)"
            >
              <div class="app-card-header">
                <img :src="item.logo" :alt="item.title" class="app-logo" />
                <div class="app-info">
                  <div class="app-title">{{ item.title }}</div>
                  <div class="app-version">v{{ item.version }}</div>
                </div>
                <div class="app-price" :class="{ free: item.price === '0.00' }">
                  {{ item.price === '0.00' ? '免费' : '¥' + item.price }}
                </div>
              </div>
              <div class="app-about">{{ item.about }}</div>
              <div class="app-footer">
                <div class="app-author">
                  <img
                    :src="item.avatar || 'https://via.placeholder.com/24'"
                    class="author-avatar"
                  />
                  <span>{{ item.username }}</span>
                </div>
                <div class="app-sales">
                  <ArtSvgIcon icon="ri:user-line" class="mr-1" />
                  {{ item.sales_num }} 销量
                </div>
              </div>
            </div>
          </div>

          <!-- 分页 -->
          <div class="flex justify-center mt-4">
            <ElPagination
              v-model:current-page="onlinePagination.current"
              v-model:page-size="onlinePagination.size"
              :total="onlinePagination.total"
              :page-sizes="[12, 24, 48]"
              layout="total, prev, pager, next, sizes"
              @size-change="fetchOnlineApps"
              @current-change="fetchOnlineApps"
            />
          </div>
        </ElTabPane>
      </ElTabs>
    </ElCard>

    <!-- 上传插件弹窗 -->
    <InstallForm ref="installFormRef" @success="getList" />

    <!-- 终端弹窗 -->
    <TerminalBox ref="terminalRef" @success="getList" />

    <!-- 详情抽屉 -->
    <ElDrawer v-model="detailVisible" :size="600" :with-header="true">
      <template #header>
        <div class="flex items-center gap-3">
          <img :src="currentApp?.logo" class="w-9 h-9 rounded-lg" />
          <div>
            <div class="text-lg font-semibold">{{ currentApp?.title }}</div>
            <div class="text-xs text-gray-400">
              v{{ currentApp?.version }} · {{ currentApp?.username }}
            </div>
          </div>
        </div>
      </template>
      <div class="detail-content">
        <div class="detail-price" :class="{ free: currentApp?.price === '0.00' }">
          {{ currentApp?.price === '0.00' ? '免费' : '¥' + currentApp?.price }}
        </div>
        <div class="detail-about">{{ currentApp?.about }}</div>

        <!-- 截图预览 -->
        <div v-if="currentApp?.screenshots?.length" class="mb-6">
          <div class="text-base font-semibold mb-3">截图预览</div>
          <ElSpace wrap :size="12">
            <ElImage
              v-for="(img, idx) in currentApp?.screenshots"
              :key="idx"
              :src="img"
              :preview-src-list="currentApp?.screenshots"
              :preview-teleported="true"
              fit="cover"
              class="w-36 h-24 rounded-lg cursor-pointer"
            />
          </ElSpace>
        </div>

        <!-- 详情描述 -->
        <div class="detail-desc">
          <div class="text-base font-semibold mb-3">详细介绍</div>
          <div class="desc-content" v-html="renderMarkdown(currentApp?.content)"></div>
        </div>

        <!-- 购买按钮 -->
        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
          <ElButton type="primary" size="large" class="w-full" @click="handleBuy">
            <template #icon>
              <ArtSvgIcon icon="ri:shopping-cart-line" />
            </template>
            前往购买
          </ElButton>
        </div>
      </div>
    </ElDrawer>

    <!-- 登录弹窗 -->
    <ElDialog v-model="loginVisible" title="登录应用商店" width="400" :close-on-click-modal="false">
      <ElForm :model="loginForm" @submit.prevent="submitLogin" label-position="top">
        <ElFormItem label="用户名/邮箱" required>
          <ElInput v-model="loginForm.username" placeholder="请输入用户名或邮箱" clearable>
            <template #prefix>
              <ArtSvgIcon icon="ri:user-line" />
            </template>
          </ElInput>
        </ElFormItem>
        <ElFormItem label="密码" required>
          <ElInput
            v-model="loginForm.password"
            type="password"
            placeholder="请输入密码"
            show-password
            clearable
          >
            <template #prefix>
              <ArtSvgIcon icon="ri:lock-line" />
            </template>
          </ElInput>
        </ElFormItem>
        <ElFormItem label="验证码" required>
          <div class="flex gap-2 w-full">
            <ElInput v-model="loginForm.code" placeholder="请输入验证码" clearable class="flex-1">
              <template #prefix>
                <ArtSvgIcon icon="ri:shield-check-line" />
              </template>
            </ElInput>
            <img
              :src="captchaImage"
              @click="getCaptcha"
              class="h-8 w-24 cursor-pointer rounded"
              title="点击刷新"
            />
          </div>
        </ElFormItem>
        <ElFormItem>
          <ElButton type="primary" native-type="submit" class="w-full" :loading="loginLoading">
            登录
          </ElButton>
        </ElFormItem>
        <div class="text-center text-sm text-gray-400">
          还没有账号？
          <ElLink type="primary" @click="handleRegister">立即注册</ElLink>
        </div>
      </ElForm>
    </ElDialog>

    <!-- 已购应用抽屉 -->
    <ElDrawer v-model="purchasedVisible" title="已购应用" :size="720">
      <div v-loading="purchasedLoading" class="purchased-list">
        <div v-for="app in purchasedApps" :key="app.id" class="purchased-card">
          <img :src="app.logo" class="purchased-logo" />
          <div class="purchased-info">
            <div class="purchased-title">{{ app.title }}</div>
            <div class="purchased-version">v{{ app.version }} · {{ app.developer }}</div>
            <div class="purchased-about">{{ app.about }}</div>
          </div>
          <div class="gap-2">
            <ElButton size="small" @click="viewDocs(app)">
              <template #icon>
                <ArtSvgIcon icon="ri:book-line" />
              </template>
              文档
            </ElButton>
            <ElButton type="primary" size="small" @click="showVersions(app)">
              <template #icon>
                <ArtSvgIcon icon="ri:download-line" />
              </template>
              下载
            </ElButton>
          </div>
        </div>
        <ElEmpty
          v-if="!purchasedLoading && purchasedApps.length === 0"
          description="暂无已购应用"
        />
      </div>
    </ElDrawer>

    <!-- 版本选择对话框 -->
    <ElDialog
      v-model="versionVisible"
      :title="'选择版本 - ' + (currentPurchasedApp?.title || '')"
      width="500"
    >
      <div v-loading="versionLoading" class="version-list">
        <div v-for="ver in versionList" :key="ver.id" class="version-item">
          <div>
            <div class="version-info-row">
              <span class="version-name">v{{ ver.version }}</span>
              <span class="version-date">{{ ver.create_time }}</span>
            </div>
            <div class="version-remark">{{ ver.remark }}</div>
          </div>
          <ElButton
            type="primary"
            size="small"
            :loading="downloadingId === ver.id"
            @click="downloadVersion(ver)"
          >
            下载安装
          </ElButton>
        </div>
        <ElEmpty v-if="!versionLoading && versionList.length === 0" description="暂无可用版本" />
      </div>
    </ElDialog>
  </div>
</template>

<script setup lang="ts">
  import { ref, reactive, onMounted, watch } from 'vue'
  import { ElMessage } from 'element-plus'
  import type { ColumnOption } from '@/types'
  import saipackageApi, {
    type AppInfo,
    type VersionInfo,
    type StoreApp,
    type StoreUser,
    type PurchasedApp,
    type AppVersion
  } from '../api/index'
  import InstallForm from './install-box.vue'
  import TerminalBox from './terminal.vue'

  // ========== 基础状态 ==========
  const activeTab = ref('local')
  const version = ref<VersionInfo>({})
  const loading = ref(false)
  const installFormRef = ref()
  const terminalRef = ref()
  const installList = ref<AppInfo[]>([])

  // ========== 本地安装相关 ==========
  const handleUpload = () => {
    installFormRef.value?.open()
  }

  // 检查版本兼容性
  const checkVersionCompatibility = (support: string | undefined): boolean => {
    if (!support || !version.value?.saiadmin_version?.describe) {
      return false // 如果没有兼容性信息，默认不允许安装
    }

    const currentVersion = version.value.saiadmin_version.describe
    const currentMatch = currentVersion.match(/^(\d+)\./)

    if (!currentMatch) {
      return true
    }

    const currentMajor = currentMatch[1]
    // support 格式为 "5.x" 或 "5.x|6.x"，用 | 分隔多个支持的版本
    const supportVersions = support.split('|').map((v) => v.trim())

    // 检查当前版本是否匹配任意一个支持的版本
    return supportVersions.some((ver) => {
      const supportMatch = ver.match(/^(\d+)\.x$/i)
      return supportMatch && supportMatch[1] === currentMajor
    })
  }

  const handleInstall = async (record: AppInfo) => {
    // 检查
    if (version.value?.saipackage_version?.state === 'fail') {
      ElMessage.error('插件市场saipackage版本检测失败')
      return
    }

    // 检查版本兼容性
    if (!checkVersionCompatibility(record.support)) {
      ElMessage.error(
        `此插件仅支持 ${record.support} 版本框架，当前框架版本为 ${version.value?.saiadmin_version?.describe}，不兼容无法安装`
      )
      return
    }

    try {
      await saipackageApi.installApp({ appName: record.app })
      ElMessage.success('安装成功')
      getList()
      saipackageApi.reloadBackend()
    } catch {
      // Error already handled by http utility
    }
  }

  const handleUninstall = async (record: AppInfo) => {
    await saipackageApi.uninstallApp({ appName: record.app })
    ElMessage.success('卸载成功')
    getList()
  }

  const handleExecFront = (record: AppInfo) => {
    const extend = 'module-install:' + record.app
    terminalRef.value?.open()
    setTimeout(() => {
      terminalRef.value?.frontInstall(extend)
    }, 500)
  }

  const handleExecBackend = (record: AppInfo) => {
    const extend = 'module-install:' + record.app
    terminalRef.value?.open()
    setTimeout(() => {
      terminalRef.value?.backendInstall(extend)
    }, 500)
  }

  const handleTerminal = () => {
    terminalRef.value?.open()
  }

  const columns: ColumnOption[] = [
    { prop: 'app', label: '插件标识', width: 120, useSlot: true },
    { prop: 'title', label: '插件名称', width: 150 },
    { prop: 'about', label: '插件描述', showOverflowTooltip: true },
    { prop: 'author', label: '作者', width: 120 },
    { prop: 'version', label: '版本', width: 100 },
    { prop: 'support', label: '框架兼容', width: 120, align: 'center' },
    { prop: 'state', label: '插件状态', width: 100, useSlot: true },
    { prop: 'npm', label: '前端依赖', width: 100, useSlot: true },
    { prop: 'composer', label: '后端依赖', width: 100, useSlot: true },
    { prop: 'operation', label: '操作', width: 140, fixed: 'right', useSlot: true }
  ]

  const getList = async () => {
    loading.value = true
    try {
      const resp = await saipackageApi.getAppList()
      installList.value = resp?.data || []
      version.value = resp?.version || {}
    } catch {
      // Error already handled by http utility
    } finally {
      loading.value = false
    }
  }

  // ========== 在线商店相关 ==========
  const detailVisible = ref(false)
  const currentApp = ref<StoreApp | null>(null)
  const storeUser = ref<StoreUser | null>(null)
  const storeToken = ref(localStorage.getItem('storeToken') || '')
  const onlineApps = ref<StoreApp[]>([])
  const onlineLoading = ref(false)
  const onlinePagination = reactive({
    current: 1,
    size: 12,
    total: 0
  })

  // 登录相关
  const loginVisible = ref(false)
  const loginLoading = ref(false)
  const captchaImage = ref('')
  const captchaUuid = ref('')
  const loginForm = reactive({
    username: '',
    password: '',
    code: ''
  })

  // 搜索表单
  const searchForm = reactive({
    keywords: '',
    type: '' as string | number,
    price: 'all'
  })

  // 已购应用相关
  const purchasedVisible = ref(false)
  const purchasedLoading = ref(false)
  const purchasedApps = ref<PurchasedApp[]>([])
  const versionVisible = ref(false)
  const versionLoading = ref(false)
  const versionList = ref<AppVersion[]>([])
  const currentPurchasedApp = ref<PurchasedApp | null>(null)
  const downloadingId = ref<number | null>(null)

  const handleLogin = () => {
    loginVisible.value = true
    getCaptcha()
  }

  const handleRegister = () => {
    window.open('https://saas.saithink.top/register', '_blank')
  }

  const handleLogout = () => {
    storeUser.value = null
    storeToken.value = ''
    localStorage.removeItem('storeToken')
  }

  const getCaptcha = async () => {
    try {
      const response = await saipackageApi.getStoreCaptcha()
      captchaImage.value = response?.image || ''
      captchaUuid.value = response?.uuid || ''
    } catch {
      // Error already handled by http utility
    }
  }

  const submitLogin = async () => {
    if (!loginForm.username || !loginForm.password || !loginForm.code) {
      ElMessage.warning('请填写完整信息')
      return
    }

    loginLoading.value = true
    try {
      const response = await saipackageApi.storeLogin({
        username: loginForm.username,
        password: loginForm.password,
        code: loginForm.code,
        uuid: captchaUuid.value
      })

      storeToken.value = response?.access_token || ''
      localStorage.setItem('storeToken', response?.access_token || '')
      loginVisible.value = false
      loginForm.username = ''
      loginForm.password = ''
      loginForm.code = ''
      await fetchStoreUser()
      ElMessage.success('登录成功')
    } catch {
      getCaptcha()
      // Error already handled by http utility
    } finally {
      loginLoading.value = false
    }
  }

  const fetchStoreUser = async () => {
    if (!storeToken.value) return

    try {
      const response = await saipackageApi.getStoreUserInfo(storeToken.value)
      storeUser.value = response || null
    } catch {
      handleLogout()
    }
  }

  const fetchOnlineApps = async () => {
    onlineLoading.value = true
    try {
      const response = await saipackageApi.getOnlineAppList({
        page: onlinePagination.current,
        limit: onlinePagination.size,
        price: searchForm.price,
        type: searchForm.type,
        keywords: searchForm.keywords
      })

      onlineApps.value = response?.data || []
      onlinePagination.total = response?.total || 0
    } catch {
      // Error already handled by http utility
    } finally {
      onlineLoading.value = false
    }
  }

  const showDetail = (item: StoreApp) => {
    currentApp.value = item
    detailVisible.value = true
  }

  const renderMarkdown = (content?: string) => {
    if (!content) return ''
    return content
      .replace(/^### (.+)$/gm, '<h3>$1</h3>')
      .replace(/^## (.+)$/gm, '<h2>$1</h2>')
      .replace(/^# (.+)$/gm, '<h1>$1</h1>')
      .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
      .replace(/\*(.+?)\*/g, '<em>$1</em>')
      .replace(/`(.+?)`/g, '<code>$1</code>')
      .replace(/^- (.+)$/gm, '<li>$1</li>')
      .replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>')
      .replace(/\n/g, '<br/>')
      .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>')
  }

  const handleBuy = () => {
    window.open('https://saas.saithink.top/apps', '_blank')
  }

  const showPurchasedApps = async () => {
    purchasedVisible.value = true
    purchasedLoading.value = true

    try {
      const response = await saipackageApi.getPurchasedApps(storeToken.value)
      purchasedApps.value = response || []
    } catch {
      // Error already handled by http utility
    }
    purchasedLoading.value = false
  }

  const viewDocs = (app: PurchasedApp) => {
    window.open(`https://saas.saithink.top/docs/${app.appname}`, '_blank')
  }

  const showVersions = async (app: PurchasedApp) => {
    currentPurchasedApp.value = app
    versionVisible.value = true
    versionLoading.value = true

    try {
      const response = await saipackageApi.getAppVersions(storeToken.value, app.app_id)
      versionList.value = response || []
    } catch {
      // Error already handled by http utility
    }
    versionLoading.value = false
  }

  const downloadVersion = async (ver: AppVersion) => {
    downloadingId.value = ver.id

    try {
      await saipackageApi.downloadApp({
        token: storeToken.value,
        id: ver.id
      })

      ElMessage.success('下载成功，即将刷新插件列表...')
      versionVisible.value = false
      purchasedVisible.value = false
      activeTab.value = 'local'
      getList()
    } catch {
      // Error already handled by http utility
    }
    downloadingId.value = null
  }

  // 监听 tab 切换
  watch(activeTab, (val) => {
    if (val === 'online') {
      fetchOnlineApps()
      fetchStoreUser()
    }
  })

  onMounted(() => {
    getList()
  })
</script>

<style lang="scss" scoped>
  .version-title {
    padding: 5px 10px;
    background: var(--el-fill-color-light);
    border: 1px solid var(--el-border-color);
    font-size: 12px;
  }

  .version-value {
    padding: 5px 10px;
    border: 1px solid var(--el-border-color);
    font-size: 12px;
  }

  .app-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
    max-height: calc(100vh - 380px);
    overflow-y: auto;
  }

  .app-card {
    background: var(--el-bg-color);
    border-radius: 8px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid var(--el-border-color);

    &:hover {
      box-shadow: var(--el-box-shadow-light);
      transform: translateY(-2px);
    }
  }

  .app-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
  }

  .app-logo {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: cover;
  }

  .app-info {
    flex: 1;
  }

  .app-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--el-text-color-primary);
  }

  .app-version {
    font-size: 12px;
    color: var(--el-text-color-secondary);
  }

  .app-price {
    font-size: 16px;
    font-weight: 600;
    color: var(--el-color-danger);

    &.free {
      color: var(--el-color-success);
    }
  }

  .app-about {
    font-size: 13px;
    color: var(--el-text-color-regular);
    line-height: 1.5;
    margin-bottom: 12px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .app-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: var(--el-text-color-secondary);
  }

  .app-author {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .author-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
  }

  .app-sales {
    display: flex;
    align-items: center;
  }

  .detail-content {
    padding: 16px 0;
  }

  .detail-price {
    font-size: 24px;
    font-weight: 600;
    color: var(--el-color-danger);
    margin-bottom: 16px;

    &.free {
      color: var(--el-color-success);
    }
  }

  .detail-about {
    font-size: 14px;
    color: var(--el-text-color-regular);
    line-height: 1.6;
    margin-bottom: 24px;
  }

  .desc-content {
    font-size: 14px;
    color: var(--el-text-color-regular);
    line-height: 1.8;

    :deep(code) {
      background: var(--el-fill-color);
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 13px;
    }

    :deep(a) {
      color: var(--el-color-primary);
    }
  }

  .purchased-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .purchased-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: var(--el-bg-color);
    border-radius: 8px;
    border: 1px solid var(--el-border-color);
  }

  .purchased-logo {
    width: 56px;
    height: 56px;
    border-radius: 8px;
    object-fit: cover;
  }

  .purchased-info {
    flex: 1;
    min-width: 0;
  }

  .purchased-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--el-text-color-primary);
    margin-bottom: 4px;
  }

  .purchased-version {
    font-size: 12px;
    color: var(--el-text-color-secondary);
    margin-bottom: 6px;
  }

  .purchased-about {
    font-size: 13px;
    color: var(--el-text-color-regular);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .version-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .version-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px;
    background: var(--el-fill-color-light);
    border-radius: 6px;
  }

  .version-info-row {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .version-name {
    font-weight: 600;
    color: var(--el-text-color-primary);
  }

  .version-date {
    font-size: 12px;
    color: var(--el-text-color-secondary);
  }

  .version-remark {
    flex: 1;
    font-size: 13px;
    color: var(--el-text-color-regular);
  }
</style>
