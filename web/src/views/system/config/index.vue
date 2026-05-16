<!-- 左右页面 -->
<template>
  <div class="art-full-height">
    <div class="box-border flex gap-4 h-full max-md:block max-md:gap-0 max-md:h-auto">
      <div class="flex-shrink-0 h-full max-md:w-full max-md:h-auto max-md:mb-5">
        <ElCard class="left-card art-card-xs flex flex-col h-full mt-0" shadow="never">
          <template #header>
            <b>系统设置</b>
          </template>
          <ElSpace wrap>
            <SaButton type="primary" icon="ri:refresh-line" @click="reloadConfigData" />
            <SaButton v-permission="'core:config:edit'" type="primary" @click="showDialog('add')" />
            <SaButton
              v-permission="'core:config:edit'"
              type="secondary"
              @click="updateConfigDialog"
            />
            <SaButton v-permission="'core:config:edit'" type="error" @click="deleteConfigData" />
          </ElSpace>
          <ArtTable
            rowKey="id"
            :loading="loading"
            :data="groupData"
            :columns="groupColumns"
            :pagination="groupPagination"
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
                  v-model="configSearch.name"
                  placeholder="搜索配置名称"
                  size="small"
                  clearable
                  @input="handleConfigSearch"
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
                  v-model="configSearch.code"
                  placeholder="搜索配置标识"
                  size="small"
                  clearable
                  @input="handleConfigSearch"
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
                @update:modelValue="handleGroupChange(row.id, row)"
              />
            </template>
          </ArtTable>
        </ElCard>
      </div>

      <div class="flex flex-col flex-1 min-w-0">
        <ElCard class="art-card-xs flex flex-col h-full mt-0" shadow="never">
          <template #header>
            <div class="flex justify-between">
              <b>{{ selectedRow.name || '未选择配置' }}</b>
              <SaButton
                v-permission="'core:config:edit'"
                type="primary"
                icon="ri:settings-4-line"
                @click="handleConfigManage"
              />
            </div>
          </template>

          <div class="max-h-[calc(100vh-250px)] overflow-y-auto">
            <ElForm ref="formRef" :model="formData" label-width="140px">
              <template v-for="(item, index) in formArray" :key="index">
                <ElFormItem :label="item.name" :prop="item.key" v-show="item.display">
                  <template v-if="item.input_type === 'select'">
                    <el-select
                      v-model="item.value"
                      :options="item.config_select_data"
                      @change="handleSelect($event, item)"
                      :placeholder="'请选择' + item.name"
                    />
                  </template>
                  <template v-if="item.input_type === 'input'">
                    <el-input v-model="item.value" :placeholder="'请输入' + item.name" />
                  </template>
                  <template v-if="item.input_type === 'radio'">
                    <el-radio-group v-model="item.value" :options="item.config_select_data" />
                  </template>
                  <template v-if="item.input_type === 'textarea'">
                    <el-input
                      type="textarea"
                      v-model="item.value"
                      :placeholder="'请输入' + item.name"
                    />
                  </template>
                  <template v-if="item.input_type === 'uploadImage'">
                    <sa-image-picker v-model="item.value" />
                  </template>
                  <template v-if="item.input_type === 'uploadFile'">
                    <sa-file-upload v-model="item.value" />
                  </template>
                  <template v-if="item.input_type === 'wangEditor'">
                    <sa-editor v-model="item.value" />
                  </template>
                  <div class="text-gray-400 text-xs py-2">{{ item.remark }}</div>
                </ElFormItem>
              </template>
              <ElFormItem v-permission="'core:config:update'" v-if="formArray.length > 0">
                <ElButton type="primary" @click="submit(formArray)">保存修改</ElButton>
              </ElFormItem>
              <ElFormItem
                v-permission="'core:config:update'"
                label="测试邮件"
                v-if="selectedRow.code === 'email_config'"
              >
                <div class="flex items-center gap-2">
                  <ElInput
                    v-model="email"
                    style="width: 300px"
                    placeholder="请输入正确的邮箱接收地址"
                  />
                  <ElButton @click="sendMail()">
                    <template #icon>
                      <ArtSvgIcon icon="ri:mail-line" />
                    </template>
                    发送
                  </ElButton>
                </div>
              </ElFormItem>
              <el-empty v-if="selectedId === 0" description="请先选择左侧配置" />
            </ElForm>
          </div>
        </ElCard>
      </div>
    </div>

    <!-- 配置编辑弹窗 -->
    <GroupEditDialog
      v-model="dialogVisible"
      :dialog-type="dialogType"
      :data="dialogData"
      @success="reloadConfigData()"
    />

    <!-- 配置项管理 -->
    <ConfigList v-model="configVisible" :data="selectedRow" @success="getConfigData()" />
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import { Search } from '@element-plus/icons-vue'
  import { ElMessage } from 'element-plus'
  import api from '@/api/system/config'
  import GroupEditDialog from './modules/group-edit-dialog.vue'
  import ConfigList from './modules/config-list.vue'

  defineOptions({ name: 'TreeTable' })

  // 刷新配置数据
  const reloadConfigData = () => {
    selectedId.value = 0
    selectedRow.value = {}
    formArray.value = []
    getGroupData()
  }

  // 修改配置
  const updateConfigDialog = () => {
    if (selectedId.value === 0) {
      ElMessage.error('请选择要修改的数据')
      return
    }
    showDialog('edit', selectedRow.value)
  }

  // 删除配置
  const deleteConfigData = () => {
    if (selectedId.value === 0) {
      ElMessage.error('请选择要修改的数据')
      return
    }
    deleteRow({ ...selectedRow.value }, api.delete, reloadConfigData)
  }

  // 配置数据
  const formData = ref({})
  const formArray = ref<any[]>([])
  const email = ref('')

  const configVisible = ref(false)

  // 配置选中行
  const selectedId = ref(0)
  const selectedRow = ref<any>({})
  const configSearch = ref({
    name: '',
    code: ''
  })

  // 配置搜索
  const handleConfigSearch = () => {
    Object.assign(searchConfigParams, configSearch.value)
    getGroupData()
  }

  const searchForm = ref({
    label: '',
    value: '',
    status: '',
    group_id: null
  })

  /**
   * 配置分组改变时，获取配置数据
   */
  const handleGroupChange = (val: any, row?: any) => {
    selectedId.value = val
    selectedRow.value = row
    searchForm.value.group_id = val
    getConfigData()
  }

  const getConfigData = () => {
    api.configList({ group_id: selectedId.value, saiType: 'all' }).then((data) => {
      formArray.value = data.map((item: any) => {
        if (
          item.key.indexOf('local_') !== -1 ||
          item.key.indexOf('qiniu_') !== -1 ||
          item.key.indexOf('cos_') !== -1 ||
          item.key.indexOf('oss_') !== -1 ||
          item.key.indexOf('s3_') !== -1
        ) {
          item.display = false
        } else {
          item.display = true
        }
        return item
      })
      if (selectedId.value === 2) {
        formArray.value.map((item) => {
          if (item.key === 'upload_mode') {
            handleSelect(item.value, item)
          }
        })
      }
    })
  }

  // 配置名称
  const {
    data: groupData,
    columns: groupColumns,
    getData: getGroupData,
    searchParams: searchConfigParams,
    loading,
    pagination: groupPagination,
    handleSizeChange,
    handleCurrentChange
  } = useTable({
    core: {
      apiFn: api.groupList,
      apiParams: {
        ...configSearch.value
      },
      columnsFactory: () => [
        { prop: 'id', label: '选中', width: 80, align: 'center', useSlot: true },
        { prop: 'name', label: '配置名称', useHeaderSlot: true, width: 150 },
        { prop: 'code', label: '配置标识', useHeaderSlot: true, width: 150 }
      ]
    }
  })

  // 编辑配置
  const { dialogType, dialogVisible, dialogData, showDialog, deleteRow } = useSaiAdmin()

  const handleConfigManage = () => {
    if (selectedId.value === 0) {
      ElMessage.error('请选择要管理的配置')
      return
    }
    configVisible.value = true
  }

  // 发送测试邮件
  const sendMail = async () => {
    const reg = /^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/
    if (!reg.test(email.value)) {
      ElMessage.warning('请输入正确的邮箱地址')
      return
    }
    await api.emailTest({ email: email.value })
    ElMessage.success('发送成功')
  }

  // 自定义处理切换显示
  const handleSelect = async (val: any, ele: any) => {
    if (ele.key === 'upload_mode') {
      if (val == 1) {
        formArray.value.map((item) => {
          if (item.key.indexOf('local_') !== -1) {
            item.display = true
          }
          if (
            item.key.indexOf('qiniu_') !== -1 ||
            item.key.indexOf('cos_') !== -1 ||
            item.key.indexOf('oss_') !== -1 ||
            item.key.indexOf('s3_') !== -1
          ) {
            item.display = false
          }
        })
      }
      if (val == 2) {
        formArray.value.map((item) => {
          if (item.key.indexOf('oss_') !== -1) {
            item.display = true
          }
          if (
            item.key.indexOf('qiniu_') !== -1 ||
            item.key.indexOf('cos_') !== -1 ||
            item.key.indexOf('local_') !== -1 ||
            item.key.indexOf('s3_') !== -1
          ) {
            item.display = false
          }
        })
      }
      if (val == 3) {
        formArray.value.map((item) => {
          if (item.key.indexOf('qiniu_') !== -1) {
            item.display = true
          }
          if (
            item.key.indexOf('local_') !== -1 ||
            item.key.indexOf('cos_') !== -1 ||
            item.key.indexOf('oss_') !== -1 ||
            item.key.indexOf('s3_') !== -1
          ) {
            item.display = false
          }
        })
      }
      if (val == 4) {
        formArray.value.map((item) => {
          if (item.key.indexOf('cos_') !== -1) {
            item.display = true
          }
          if (
            item.key.indexOf('qiniu_') !== -1 ||
            item.key.indexOf('local_') !== -1 ||
            item.key.indexOf('oss_') !== -1 ||
            item.key.indexOf('s3_') !== -1
          ) {
            item.display = false
          }
        })
      }
      if (val == 5) {
        formArray.value.map((item) => {
          if (item.key.indexOf('s3_') !== -1) {
            item.display = true
          }
          if (
            item.key.indexOf('qiniu_') !== -1 ||
            item.key.indexOf('cos_') !== -1 ||
            item.key.indexOf('local_') !== -1 ||
            item.key.indexOf('oss_') !== -1
          ) {
            item.display = false
          }
        })
      }
    }
  }

  const submit = async (params: any) => {
    const data = {
      group_id: selectedId.value,
      config: params
    }
    await api.batchUpdate(data)
    ElMessage.success('保存成功')
  }
</script>

<style scoped>
  .left-card :deep(.el-card__body) {
    flex: 1;
    min-height: 0;
    padding: 10px 2px 10px 10px;
  }
</style>
