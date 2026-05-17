<template>
  <div class="art-full-height">
    <ElCard class="art-card-xs mt-0">
      <template #header>
        <div class="flex items-center justify-between">
          <b>插件管理</b>
          <ElSpace>
            <ElButton type="primary" @click="showCreateDialog = true">新建插件</ElButton>
            <ElButton type="warning" @click="openDoctorDialog()">插件诊断</ElButton>
            <ElButton @click="loadPlugins">刷新</ElButton>
          </ElSpace>
        </div>
      </template>

      <ElTable :data="pluginList" v-loading="loading" border>
        <ElTableColumn prop="name" label="名称" min-width="120" />
        <ElTableColumn prop="title" label="标题" min-width="140" />
        <ElTableColumn prop="version" label="版本" width="110" />
        <ElTableColumn prop="author" label="作者" min-width="120" />
        <ElTableColumn label="状态" width="120">
          <template #default="{ row }">
            <ElTag :type="row.enabled ? 'success' : row.installed ? 'warning' : 'info'">
              {{ row.status_text }}
            </ElTag>
          </template>
        </ElTableColumn>
        <ElTableColumn label="依赖" min-width="260">
          <template #default="{ row }">
            <span v-if="dependencyText(row.dependencies)">{{
              dependencyText(row.dependencies)
            }}</span>
            <span v-else class="text-gray-400">无</span>
          </template>
        </ElTableColumn>
        <ElTableColumn label="操作" width="440" fixed="right">
          <template #default="{ row }">
            <ElSpace wrap>
              <ElButton size="small" type="primary" link @click="openConfigDialog(row.name)">
                配置
              </ElButton>
              <ElButton
                v-if="!row.installed"
                size="small"
                type="success"
                link
                @click="installPlugin(row.name)"
              >
                安装
              </ElButton>
              <ElButton
                v-if="row.installed && !row.enabled"
                size="small"
                type="success"
                link
                @click="enablePlugin(row.name)"
              >
                启用
              </ElButton>
              <ElButton
                v-if="row.installed && row.enabled"
                size="small"
                type="warning"
                link
                @click="disablePlugin(row.name)"
              >
                禁用
              </ElButton>
              <ElButton
                v-if="row.installed"
                size="small"
                type="danger"
                link
                @click="uninstallPlugin(row.name)"
              >
                卸载
              </ElButton>
            </ElSpace>
          </template>
        </ElTableColumn>
      </ElTable>
    </ElCard>

    <ElDialog v-model="showCreateDialog" title="新建插件" width="520px" destroy-on-close>
      <ElForm :model="createForm" label-width="90px">
        <ElFormItem label="名称">
          <ElInput v-model="createForm.name" placeholder="如 blog-center" />
        </ElFormItem>
        <ElFormItem label="标题">
          <ElInput v-model="createForm.title" placeholder="如 博客中心" />
        </ElFormItem>
        <ElFormItem label="作者">
          <ElInput v-model="createForm.author" placeholder="作者名称" />
        </ElFormItem>
        <ElFormItem label="描述">
          <ElInput v-model="createForm.description" type="textarea" :rows="3" />
        </ElFormItem>
      </ElForm>
      <template #footer>
        <ElButton @click="showCreateDialog = false">取消</ElButton>
        <ElButton type="primary" @click="createPlugin">创建</ElButton>
      </template>
    </ElDialog>

    <ElDialog v-model="showConfigDialog" title="插件配置" width="760px" destroy-on-close>
      <div class="text-sm text-gray-500 mb-2">插件：{{ configPluginName }}</div>
      <ElInput
        v-model="configText"
        type="textarea"
        :rows="18"
        placeholder="请输入 JSON 配置"
        class="font-mono"
      />
      <template #footer>
        <ElButton @click="showConfigDialog = false">取消</ElButton>
        <ElButton type="primary" @click="savePluginConfig">保存配置</ElButton>
      </template>
    </ElDialog>

    <ElDialog v-model="showDoctorDialog" title="插件诊断" width="980px" destroy-on-close>
      <div class="mb-3 flex items-center gap-2">
        <ElInput
          v-model="doctorPluginName"
          placeholder="按插件名过滤（可选，例如 blog）"
          clearable
          style="max-width: 320px"
        />
        <ElButton type="primary" @click="loadDoctorData">开始诊断</ElButton>
      </div>
      <ElInput
        v-model="doctorText"
        type="textarea"
        :rows="22"
        readonly
        class="font-mono"
        placeholder="诊断结果将显示在这里"
      />
      <template #footer>
        <ElButton @click="copyDoctorText">复制结果</ElButton>
        <ElButton type="primary" @click="loadDoctorData">刷新诊断</ElButton>
      </template>
    </ElDialog>
  </div>
</template>

<script setup lang="ts">
  import { ElMessage, ElMessageBox } from 'element-plus'
  import api from '@/api/system/plugin'
  import type { PluginItem } from '@/api/system/plugin'

  const loading = ref(false)
  const pluginList = ref<PluginItem[]>([])

  const showCreateDialog = ref(false)
  const createForm = reactive({
    name: '',
    title: '',
    author: '',
    description: ''
  })

  const showConfigDialog = ref(false)
  const configPluginName = ref('')
  const configText = ref('{}')

  const showDoctorDialog = ref(false)
  const doctorPluginName = ref('')
  const doctorText = ref('')

  const loadPlugins = async () => {
    loading.value = true
    try {
      pluginList.value = await api.list()
    } finally {
      loading.value = false
    }
  }

  const dependencyText = (deps: PluginItem['dependencies']) => {
    if (!deps) return ''
    if (Array.isArray(deps)) return deps.join(', ')
    return Object.keys(deps)
      .map((k) => `${k}@${deps[k]}`)
      .join(', ')
  }

  const createPlugin = async () => {
    if (!createForm.name) {
      ElMessage.warning('请填写插件名称')
      return
    }
    await api.create(createForm)
    ElMessage.success('插件创建成功')
    showCreateDialog.value = false
    createForm.name = ''
    createForm.title = ''
    createForm.author = ''
    createForm.description = ''
    await loadPlugins()
  }

  const installPlugin = async (name: string) => {
    const action = await ElMessageBox.confirm(
      '安装时是否自动解析并安装依赖插件？',
      `安装插件：${name}`,
      {
        type: 'info',
        distinguishCancelAndClose: true,
        confirmButtonText: '自动安装依赖',
        cancelButtonText: '仅安装当前插件'
      }
    ).catch(() => 'cancel')

    const autoDeps = action !== 'cancel'
    await api.install({
      name,
      auto_install_dependencies: autoDeps
    })
    ElMessage.success('安装成功')
    await loadPlugins()
  }

  const uninstallPlugin = async (name: string) => {
    await ElMessageBox.confirm(`确认卸载插件 ${name}？`, '卸载确认', { type: 'warning' })
    await api.uninstall({ name })
    ElMessage.success('卸载成功')
    await loadPlugins()
  }

  const enablePlugin = async (name: string) => {
    await api.enable(name)
    ElMessage.success('启用成功')
    await loadPlugins()
  }

  const disablePlugin = async (name: string) => {
    await api.disable(name)
    ElMessage.success('禁用成功')
    await loadPlugins()
  }

  const openConfigDialog = async (name: string) => {
    const data = await api.getConfig(name)
    configPluginName.value = name
    configText.value = JSON.stringify(data ?? {}, null, 2)
    showConfigDialog.value = true
  }

  const savePluginConfig = async () => {
    let parsed: Record<string, any> = {}
    try {
      parsed = JSON.parse(configText.value || '{}')
    } catch {
      ElMessage.error('配置 JSON 格式不正确')
      return
    }

    await api.updateConfig(configPluginName.value, parsed)
    ElMessage.success('配置已保存')
    showConfigDialog.value = false
  }

  const openDoctorDialog = async (name?: string) => {
    doctorPluginName.value = name ?? ''
    showDoctorDialog.value = true
    await loadDoctorData()
  }

  const loadDoctorData = async () => {
    const name = doctorPluginName.value.trim()
    const data = await api.doctor(name ? { name } : undefined)
    doctorText.value = JSON.stringify(data ?? {}, null, 2)
  }

  const copyDoctorText = async () => {
    if (!doctorText.value) {
      ElMessage.warning('暂无诊断结果')
      return
    }
    await navigator.clipboard.writeText(doctorText.value)
    ElMessage.success('诊断结果已复制')
  }

  onMounted(() => {
    loadPlugins()
  })
</script>
