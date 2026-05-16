<template>
  <el-drawer
    v-model="visible"
    :title="`编辑生成信息 - ${record?.table_comment}`"
    size="100%"
    destroy-on-close
    @close="handleClose"
  >
    <div v-loading="loading" element-loading-text="加载数据中...">
      <el-form ref="formRef" :model="form">
        <el-tabs v-model="activeTab">
          <!-- 配置信息 Tab -->
          <el-tab-pane label="配置信息" name="base_config">
            <el-divider content-position="left">基础信息</el-divider>
            <el-row :gutter="24">
              <el-col :span="8">
                <el-form-item label="表名称" prop="table_name" label-width="100px">
                  <el-input v-model="form.table_name" disabled />
                  <div class="text-xs text-gray-400 mt-1">
                    数据库表的名称，自动读取数据库表名称
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item
                  label="表描述"
                  prop="table_comment"
                  label-width="100px"
                  :rules="[{ required: true, message: '表描述必填' }]"
                >
                  <el-input v-model="form.table_comment" />
                  <div class="text-xs text-gray-400 mt-1"> 表的描述，自动读取数据库表注释 </div>
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item
                  label="实体类"
                  prop="class_name"
                  label-width="100px"
                  :rules="[{ required: true, message: '实体类必填' }]"
                >
                  <el-input v-model="form.class_name" />
                  <div class="text-xs text-gray-400 mt-1"> 生成的实体类名称，可以修改去掉前缀 </div>
                </el-form-item>
              </el-col>
            </el-row>

            <el-row :gutter="24">
              <el-col :span="8">
                <el-form-item
                  label="业务名称"
                  prop="business_name"
                  label-width="100px"
                  :rules="[{ required: true, message: '业务名称必填' }]"
                >
                  <el-input v-model="form.business_name" />
                  <div class="text-xs text-gray-400 mt-1"> 英文、业务名称、同一个分组包下唯一 </div>
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item label="数据源" prop="source" label-width="100px">
                  <el-select v-model="form.source" placeholder="请选择数据源" style="width: 100%">
                    <el-option
                      v-for="item in dataSourceList"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    />
                  </el-select>
                  <div class="text-xs text-gray-400 mt-1"> 数据库配置文件中配置的数据源 </div>
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item label="备注信息" prop="remark" label-width="100px">
                  <el-input v-model="form.remark" />
                </el-form-item>
              </el-col>
            </el-row>

            <el-divider content-position="left">生成信息</el-divider>
            <el-row :gutter="24">
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item
                  label="应用类型"
                  prop="template"
                  label-width="100px"
                  :rules="[{ required: true, message: '应用类型必选' }]"
                >
                  <el-select
                    v-model="form.template"
                    placeholder="请选择生成模板"
                    style="width: 100%"
                    clearable
                  >
                    <el-option label="应用[app]" value="app" />
                    <el-option label="插件[plugin]" value="plugin" />
                  </el-select>
                  <div class="text-xs text-gray-400 mt-1"
                    >默认app模板,生成文件放app目录下，plugin应用需要先手动初始化</div
                  >
                </el-form-item>
              </el-col>
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item
                  label="应用名称"
                  prop="namespace"
                  label-width="100px"
                  :rules="[{ required: true, message: '应用名称必填' }]"
                >
                  <el-input v-model="form.namespace" />
                  <div class="text-xs text-gray-400 mt-1">
                    plugin插件名称, 或者app下应用名称
                  </div>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item
                  label="分组包名"
                  prop="package_name"
                  label-width="100px"
                  :rules="[{ required: true, message: '分组包名必填' }]"
                >
                  <el-input v-model="form.package_name" placeholder="请输入分组包名" clearable />
                  <div class="text-xs text-gray-400 mt-1">
                    生成的文件放在分组包名目录下，功能模块分组
                  </div>
                </el-form-item>
              </el-col>
            </el-row>

            <el-row :gutter="24">
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item
                  label="生成类型"
                  prop="tpl_category"
                  label-width="100px"
                  :rules="[{ required: true, message: '生成类型必填' }]"
                >
                  <el-select
                    v-model="form.tpl_category"
                    placeholder="请选择所属模块"
                    style="width: 100%"
                    clearable
                  >
                    <el-option label="单表CRUD" value="single" />
                    <el-option label="树表CRUD" value="tree" />
                  </el-select>
                  <div class="text-xs text-gray-400 mt-1">
                    单表须有主键，树表须指定id、parent_id、name等字段
                  </div>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item
                  label="生成路径"
                  prop="generate_path"
                  label-width="100px"
                  :rules="[{ required: true, message: '生成路径必填' }]"
                >
                  <el-input v-model="form.generate_path" />
                  <div class="text-xs text-gray-400 mt-1">
                    前端根目录文件夹名称，必须与后端根目录同级
                  </div>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item label="模型类型" prop="stub" label-width="100px">
                  <div class="flex-col">
                    <el-radio-group v-model="form.stub">
                      <el-radio value="eloquent">EloquentORM</el-radio>
                    </el-radio-group>
                    <div class="text-xs text-gray-400 mt-1">生成不同驱动模型的代码</div>
                  </div>
                </el-form-item>
              </el-col>
            </el-row>

            <el-row :gutter="24">
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item label="所属菜单" prop="belong_menu_id" label-width="100px">
                  <el-cascader
                    v-model="form.belong_menu_id"
                    :options="menus"
                    :props="{
                      expandTrigger: 'hover',
                      checkStrictly: true,
                      value: 'id',
                      label: 'label'
                    }"
                    style="width: 100%"
                    placeholder="生成功能所属菜单"
                    clearable
                  />
                  <div class="text-xs text-gray-400 mt-1">
                    默认为工具菜单栏目下的子菜单。不选择则为顶级菜单栏目
                  </div>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item
                  label="菜单名称"
                  prop="menu_name"
                  label-width="100px"
                  :rules="[{ required: true, message: '菜单名称必选' }]"
                >
                  <el-input v-model="form.menu_name" placeholder="请输入菜单名称" clearable />
                  <div class="text-xs text-gray-400 mt-1">
                    显示在菜单栏目上的菜单名称、以及代码中的业务功能名称
                  </div>
                </el-form-item>
              </el-col>
            </el-row>

            <el-row :gutter="24">
              <el-col :span="8">
                <el-form-item label="表单效果" prop="component_type" label-width="100px">
                  <div class="flex-col">
                    <el-radio-group v-model="form.component_type">
                      <el-radio-button :value="1">抽屉式</el-radio-button>
                      <el-radio-button :value="2">弹出框</el-radio-button>
                    </el-radio-group>
                    <div class="text-xs text-gray-400 mt-1">表单显示方式</div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item label="表单宽度" prop="form_width" label-width="100px">
                  <div class="flex-col">
                    <el-input-number v-model="form.form_width" :min="200" :max="10000" />
                    <div class="text-xs text-gray-400 mt-1">表单组件的宽度，单位为px</div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item label="表单全屏" prop="is_full" label-width="100px">
                  <div class="flex-col">
                    <el-radio-group v-model="form.is_full">
                      <el-radio :value="1">否</el-radio>
                      <el-radio :value="2">是</el-radio>
                    </el-radio-group>
                    <div class="text-xs text-gray-400 mt-1">编辑表单是否全屏</div>
                  </div>
                </el-form-item>
              </el-col>
            </el-row>

            <!-- 树表配置 -->
            <template v-if="form.tpl_category === 'tree'">
              <el-divider content-position="left">树表配置</el-divider>
              <el-row :gutter="24">
                <el-col :xs="24" :md="8" :xl="8">
                  <el-form-item label="树主ID" prop="tree_id" label-width="100px">
                    <el-select
                      v-model="formOptions.tree_id"
                      placeholder="请选择树表的主ID"
                      style="width: 100%"
                      clearable
                      filterable
                    >
                      <el-option
                        v-for="(item, index) in form.columns"
                        :key="index"
                        :label="`${item.column_name} - ${item.column_comment}`"
                        :value="item.column_name"
                      />
                    </el-select>
                    <div class="text-xs text-gray-400 mt-1">指定树表的主要ID，一般为主键</div>
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :md="8" :xl="8">
                  <el-form-item label="树父ID" prop="tree_parent_id" label-width="100px">
                    <el-select
                      v-model="formOptions.tree_parent_id"
                      placeholder="请选择树表的父ID"
                      style="width: 100%"
                      clearable
                      filterable
                    >
                      <el-option
                        v-for="(item, index) in form.columns"
                        :key="index"
                        :label="`${item.column_name} - ${item.column_comment}`"
                        :value="item.column_name"
                      />
                    </el-select>
                    <div class="text-xs text-gray-400 mt-1">指定树表的父ID，比如：parent_id</div>
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :md="8" :xl="8">
                  <el-form-item label="树名称" prop="tree_name" label-width="100px">
                    <el-select
                      v-model="formOptions.tree_name"
                      placeholder="请选择树表的名称字段"
                      style="width: 100%"
                      clearable
                      filterable
                    >
                      <el-option
                        v-for="(item, index) in form.columns"
                        :key="index"
                        :label="`${item.column_name} - ${item.column_comment}`"
                        :value="item.column_name"
                      />
                    </el-select>
                    <div class="text-xs text-gray-400 mt-1">指定树显示的名称字段，比如：name</div>
                  </el-form-item>
                </el-col>
              </el-row>
            </template>
          </el-tab-pane>

          <!-- 字段配置 Tab -->
          <el-tab-pane label="字段配置" name="field_config">
            <el-table :data="form.columns" max-height="750">
              <el-table-column prop="sort" label="排序" width="150">
                <template #default="{ row }">
                  <el-input-number
                    v-model="row.sort"
                    style="width: 100px"
                    controls-position="right"
                  />
                </template>
              </el-table-column>
              <el-table-column
                prop="column_name"
                label="字段名称"
                width="160"
                show-overflow-tooltip
              />
              <el-table-column prop="column_comment" label="字段描述" width="160">
                <template #default="{ row }">
                  <el-input v-model="row.column_comment" clearable />
                </template>
              </el-table-column>
              <el-table-column prop="column_type" label="物理类型" width="100" />
              <el-table-column prop="is_required" label="必填" width="80" align="center">
                <template #header>
                  <div class="flex-c justify-center items-center gap-1">
                    <span>必填</span>
                    <el-checkbox @change="(val) => handlerAll(val, 'required')" />
                  </div>
                </template>
                <template #default="{ row }">
                  <el-checkbox v-model="row.is_required" />
                </template>
              </el-table-column>
              <el-table-column prop="is_insert" label="表单" width="80" align="center">
                <template #header>
                  <div class="flex-c justify-center items-center gap-1">
                    <span>表单</span>
                    <el-checkbox @change="(val) => handlerAll(val, 'insert')" />
                  </div>
                </template>
                <template #default="{ row }">
                  <el-checkbox v-model="row.is_insert" />
                </template>
              </el-table-column>
              <el-table-column prop="is_list" label="列表" width="80" align="center">
                <template #header>
                  <div class="flex-c justify-center items-center gap-1">
                    <span>列表</span>
                    <el-checkbox @change="(val) => handlerAll(val, 'list')" />
                  </div>
                </template>
                <template #default="{ row }">
                  <el-checkbox v-model="row.is_list" />
                </template>
              </el-table-column>
              <el-table-column prop="is_query" label="查询" width="80" align="center">
                <template #header>
                  <div class="flex-c justify-center items-center gap-1">
                    <span>查询</span>
                    <el-checkbox @change="(val) => handlerAll(val, 'query')" />
                  </div>
                </template>
                <template #default="{ row }">
                  <el-checkbox v-model="row.is_query" />
                </template>
              </el-table-column>
              <el-table-column prop="is_sort" label="排序" width="80" align="center">
                <template #header>
                  <div class="flex-c justify-center items-center gap-1">
                    <span>排序</span>
                    <el-checkbox @change="(val) => handlerAll(val, 'sort')" />
                  </div>
                </template>
                <template #default="{ row }">
                  <el-checkbox v-model="row.is_sort" />
                </template>
              </el-table-column>
              <el-table-column prop="query_type" label="查询方式" width="150">
                <template #default="{ row }">
                  <el-select v-model="row.query_type" clearable>
                    <el-option
                      v-for="item in queryType"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    />
                  </el-select>
                </template>
              </el-table-column>
              <el-table-column prop="view_type" label="页面控件">
                <template #default="{ row }">
                  <div class="flex items-center gap-2">
                    <el-select
                      v-model="row.view_type"
                      style="width: 140px"
                      @change="changeViewType(row)"
                      clearable
                    >
                      <el-option
                        v-for="item in viewComponent"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value"
                      />
                    </el-select>
                    <el-link
                      v-if="notNeedSettingComponents.includes(row.view_type)"
                      @click="settingComponentRef.open(row)"
                    >
                      设置
                    </el-link>
                  </div>
                </template>
              </el-table-column>
              <el-table-column prop="dict_type" label="数据字典">
                <template #default="{ row }">
                  <el-select
                    v-model="row.dict_type"
                    clearable
                    placeholder="选择数据字典"
                    :disabled="!['saSelect', 'radio', 'checkbox'].includes(row.view_type)"
                  >
                    <el-option
                      v-for="(item, key) in dictStore.dictList"
                      :key="key"
                      :label="key"
                      :value="key"
                    />
                  </el-select>
                </template>
              </el-table-column>
            </el-table>
          </el-tab-pane>

          <!-- 关联配置 Tab -->
          <el-tab-pane label="关联配置" name="relation_config">
            <el-alert type="info" :closable="false">
              模型关联支持：一对一、一对多、一对一（反向）、多对多。
            </el-alert>

            <el-button type="primary" class="mt-4 mb-4" @click="addRelation">
              <template #icon>
                <ArtSvgIcon icon="ri:add-line" />
              </template>
              新增关联
            </el-button>

            <div v-for="(item, index) in formOptions.relations" :key="index">
              <el-divider content-position="left">
                {{ item.name ? item.name : '定义新关联' }}
                <el-link type="danger" class="ml-5" @click="delRelation(index)">
                  <ArtSvgIcon icon="ri:delete-bin-line" class="mr-1" />
                  删除定义
                </el-link>
              </el-divider>
              <el-row :gutter="24">
                <el-col :span="8">
                  <el-form-item label="关联类型" label-width="100px">
                    <el-select
                      v-model="item.type"
                      placeholder="请选择关联类型"
                      clearable
                      filterable
                    >
                      <el-option
                        v-for="types in relationsType"
                        :key="types.value"
                        :label="types.name"
                        :value="types.value"
                      />
                    </el-select>
                    <div class="text-xs text-gray-400 mt-1">指定关联类型</div>
                  </el-form-item>
                </el-col>
                <el-col :span="8">
                  <el-form-item label="关联名称" label-width="100px">
                    <el-input v-model="item.name" placeholder="设置关联名称" clearable />
                    <div class="text-xs text-gray-400 mt-1">属性名称，代码中with调用的名称</div>
                  </el-form-item>
                </el-col>

                <el-col :span="8">
                  <el-form-item label="关联模型" label-width="100px">
                    <el-input v-model="item.model" placeholder="设置关联模型" clearable />
                    <div class="text-xs text-gray-400 mt-1">选择要关联的实体模型</div>
                  </el-form-item>
                </el-col>
                <el-col :span="8">
                  <el-form-item
                    :label="
                      item.type === 'belongsTo'
                        ? '外键'
                        : item.type === 'belongsToMany'
                          ? '外键'
                          : '当前模型主键'
                    "
                    label-width="100px"
                  >
                    <el-input v-model="item.localKey" placeholder="设置键名" clearable />
                    <div class="text-xs text-gray-400 mt-1">
                      {{
                        item.type === 'belongsTo'
                          ? '关联模型_id'
                          : item.type === 'belongsToMany'
                            ? '关联模型_id'
                            : '当前模型主键'
                      }}
                    </div>
                  </el-form-item>
                </el-col>

                <el-col v-show="item.type === 'belongsToMany'" :span="8">
                  <el-form-item label="中间模型" label-width="100px">
                    <el-input v-model="item.table" placeholder="请输入中间模型" clearable />
                    <div class="text-xs text-gray-400 mt-1">多对多关联的中间模型</div>
                  </el-form-item>
                </el-col>
                <el-col :span="8">
                  <el-form-item
                    :label="item.type === 'belongsTo' ? '关联主键' : '外键'"
                    label-width="100px"
                  >
                    <el-input v-model="item.foreignKey" placeholder="设置键名" clearable />
                    <div class="text-xs text-gray-400 mt-1">
                      {{ item.type === 'belongsTo' ? '关联模型主键' : '当前模型_id' }}
                    </div>
                  </el-form-item>
                </el-col>
              </el-row>
            </div>
          </el-tab-pane>
        </el-tabs>
      </el-form>
    </div>

    <!-- 设置组件弹窗 -->
    <SettingComponent ref="settingComponentRef" @confirm="confirmSetting" />

    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" :loading="submitLoading" @click="save">保存</el-button>
    </template>
  </el-drawer>
</template>

<script setup lang="ts">
  import { ElMessage } from 'element-plus'
  import type { CheckboxValueType, FormInstance } from 'element-plus'
  import { useDictStore } from '@/store/modules/dict'

  // 接口导入
  import generate from '@/api/tool/generate'
  import database from '@/api/safeguard/database'
  import menuApi from '@/api/system/menu'

  import SettingComponent from './settingComponent.vue'

  // 导入变量
  import { relationsType, queryType, viewComponent } from '../js/vars'

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

  const dictStore = useDictStore()

  const record = ref<any>({})
  const loading = ref(true)
  const submitLoading = ref(false)
  const activeTab = ref('base_config')
  const formRef = ref<FormInstance>()
  const settingComponentRef = ref()

  const notNeedSettingComponents = ref([
    'uploadFile',
    'uploadImage',
    'imagePicker',
    'chunkUpload',
    'editor',
    'date',
    'userSelect'
  ])

  const form = ref<any>({
    generate_menus: ['index', 'save', 'update', 'read', 'destroy'],
    columns: []
  })

  // form扩展组
  const formOptions = ref<any>({
    relations: []
  })
  // 菜单列表
  const menus = ref<any[]>([])
  // 数据源
  const dataSourceList = ref<{ label: string; value: string }[]>([])

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

  watch(
    () => form.value.class_name,
    (newVal) => {
      // 联动：当修改实体类时，如果分组包名是空的或需要跟实体类保持一致，可以自动更新
      // 为了符合“分组包名，跟实体类 一样”的需求，只要类名变化就自动赋予包名
      form.value.package_name = newVal
    }
  )

  /**
   * 初始化页面数据
   */
  const initPage = async () => {
    loading.value = true
    // 获取数据源
    const data = await database.getDataSource()
    dataSourceList.value = data.map((item: any) => ({
      label: item,
      value: item
    }))
    const response = await generate.read({ id: props.data?.id })
    record.value = response
    initForm()
    loading.value = false
  }

  /**
   * 设置组件确认
   */
  const confirmSetting = (name: string, value: any) => {
    form.value.columns.find((item: any, idx: number) => {
      if (item.column_name === name) {
        form.value.columns[idx].options = value
      }
    })
    ElMessage.success('组件设置成功')
  }

  /**
   * 切换页面控件类型
   */
  const changeViewType = (record: any) => {
    if (
      record.view_type === 'uploadImage' ||
      record.view_type === 'imagePicker' ||
      record.view_type === 'uploadFile' ||
      record.view_type === 'chunkUpload'
    ) {
      record.options = { multiple: false, limit: 1 }
    } else if (record.view_type === 'editor') {
      record.options = { height: 400 }
    } else if (record.view_type === 'date') {
      record.options = { mode: 'date' }
    } else if (record.view_type === 'userSelect') {
      record.options = { multiple: false }
    } else {
      record.options = {}
    }
  }

  /**
   * 保存
   */
  const save = async () => {
    if (form.value.namespace === 'saiadmin') {
      ElMessage.error('应用名称不能为saiadmin')
      return
    }

    const validResult = await formRef.value?.validate().catch((err) => err)
    if (validResult !== true) {
      return
    }

    submitLoading.value = true
    try {
      form.value.options = formOptions.value
      await generate.update({ ...form.value })
      ElMessage.success('更新成功')
      emit('success')
      handleClose()
    } finally {
      submitLoading.value = false
    }
  }

  /**
   * 全选 / 全不选
   */
  const handlerAll = (value: CheckboxValueType, type: string) => {
    form.value.columns.forEach((item: any) => {
      item['is_' + type] = value
    })
  }

  /**
   * 新增关联定义
   */
  const addRelation = () => {
    formOptions.value.relations.push({
      name: '',
      type: 'hasOne',
      model: '',
      foreignKey: '',
      localKey: '',
      table: ''
    })
  }

  /**
   * 删除关联定义
   */
  const delRelation = (idx: number | string) => {
    formOptions.value.relations.splice(idx, 1)
  }

  /**
   * 初始化数据
   */
  const initForm = () => {
    // 设置form数据
    for (const name in record.value) {
      if (name === 'generate_menus') {
        form.value[name] = record.value[name] ? record.value[name].split(',') : []
      } else {
        form.value[name] = record.value[name]
      }
    }

    // 设置默认值
    if (!form.value.template) {
      form.value.template = 'app'
    }
    
    // 强制使用 EloquentORM
    form.value.stub = 'eloquent'

    // 分组包名默认跟实体类一样
    if (!form.value.package_name && form.value.class_name) {
      form.value.package_name = form.value.class_name
    }

    if (record.value.options && record.value.options.relations) {
      formOptions.value.relations = record.value.options.relations
    } else {
      formOptions.value.relations = []
    }

    if (record.value.tpl_category === 'tree') {
      formOptions.value.tree_id = record.value.options.tree_id
      formOptions.value.tree_name = record.value.options.tree_name
      formOptions.value.tree_parent_id = record.value.options.tree_parent_id
    }

    // 请求表字段
    generate.getTableColumns({ table_id: record.value.id }).then((data: any) => {
      form.value.columns = []
      data.forEach((item: any) => {
        item.is_required = item.is_required === 2 ? true : false
        item.is_insert = item.is_insert === 2 ? true : false
        item.is_edit = item.is_edit === 2 ? true : false
        item.is_list = item.is_list === 2 ? true : false
        item.is_query = item.is_query === 2 ? true : false
        item.is_sort = item.is_sort === 2 ? true : false
        form.value.columns.push(item)
      })
    })

    // 请求菜单列表
    menuApi.list({ tree: true, menu: true }).then((data: any) => {
      menus.value = data
      menus.value.unshift({ id: 0, value: 0, label: '顶级菜单' })
    })
  }

  /**
   * 关闭弹窗
   */
  const handleClose = () => {
    visible.value = false
    formRef.value?.resetFields()
  }
</script>
