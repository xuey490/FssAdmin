<template>
  <el-dialog
    v-model="visible"
    :title="dialogType === 'add' ? '新增菜单' : '编辑菜单'"
    width="820px"
    align-center
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="120px">
      <el-form-item label="菜单类型" prop="type">
        <sa-radio v-model="formData.type" type="button" dict="menu_type"></sa-radio>
      </el-form-item>
      <el-form-item label="上级菜单" prop="parent_id">
        <el-tree-select
          v-model="formData.parent_id"
          :data="optionData.treeData"
          :render-after-expand="false"
          check-strictly
          clearable
        />
      </el-form-item>
      <el-row>
        <el-col :span="12">
          <el-form-item label="菜单名称" prop="name">
            <el-input v-model="formData.name" placeholder="请输入菜单名称" />
          </el-form-item>
        </el-col>
        <el-col :span="12" v-if="formData.type < 3">
          <el-form-item prop="path">
            <template #label>
              <sa-label
                label="路由地址"
                tooltip="一级菜单：以 / 开头的绝对路径（如 /dashboard） 二级及以下：相对路径（如 console、user）"
              />
            </template>
            <el-input v-model="formData.path" placeholder="如：/dashboard 或 console" />
          </el-form-item>
        </el-col>
        <el-col :span="12" v-if="formData.type != 3">
          <el-form-item label="组件名称" prop="code">
            <el-input v-model="formData.code" placeholder="如: User" />
          </el-form-item>
        </el-col>
        <el-col :span="12" v-if="formData.type === 2">
          <el-form-item prop="component">
            <template #label>
              <sa-label label="组件路径" tooltip="填写组件路径（views目录下） 目录菜单：留空" />
            </template>
            <el-autocomplete
              class="w-full"
              v-model="formData.component"
              :fetch-suggestions="querySearch"
              clearable
              placeholder="如：/system/user 或留空"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12" v-if="formData.type != 3">
          <el-form-item label="菜单图标" prop="icon">
            <sa-icon-picker v-model="formData.icon" />
          </el-form-item>
        </el-col>
        <el-col :span="12" v-if="formData.type === 3">
          <el-form-item label="权限标识" prop="slug">
            <el-input v-model="formData.slug" placeholder="请输入权限标识" />
          </el-form-item>
        </el-col>
        <el-col :span="24" v-if="formData.type === 4">
          <el-form-item label="外链地址" prop="link_url">
            <el-input v-model="formData.link_url" placeholder="如：https://www.baidu.com" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item prop="sort">
            <template #label>
              <sa-label label="排序" tooltip="数字越大越靠前" />
            </template>
            <el-input-number
              v-model="formData.sort"
              placeholder="请输入排序"
              controls-position="right"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item prop="status">
            <template #label>
              <sa-label label="状态" tooltip="禁用后，该菜单项将不可用" />
            </template>
            <sa-radio v-model="formData.status" dict="data_status" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item prop="is_iframe">
            <template #label>
              <sa-label label="是否内嵌" tooltip="外链模式下有效" />
            </template>
            <sa-switch v-model="formData.is_iframe" dict="yes_or_no" :showText="false" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item prop="is_keep_alive">
            <template #label>
              <sa-label label="是否缓存" tooltip="切换tabs不刷新" />
            </template>
            <sa-switch v-model="formData.is_keep_alive" dict="yes_or_no" :showText="false" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item prop="is_hidden">
            <template #label>
              <sa-label label="是否隐藏" tooltip="不在菜单栏显示，但是可以通过路由访问" />
            </template>
            <sa-switch v-model="formData.is_hidden" dict="yes_or_no" :showText="false" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item prop="is_fixed_tab">
            <template #label>
              <sa-label label="是否固定" tooltip="固定在tabs导航栏" />
            </template>
            <sa-switch v-model="formData.is_fixed_tab" dict="yes_or_no" :showText="false" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item prop="is_full_page">
            <template #label>
              <sa-label label="是否全屏" tooltip="不继承左侧菜单和顶部导航栏" />
            </template>
            <sa-switch v-model="formData.is_full_page" dict="yes_or_no" :showText="false" />
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" @click="handleSubmit">提交</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  import api from '@/api/system/menu'
  import { ElMessage } from 'element-plus'
  import type { FormInstance, FormRules } from 'element-plus'

  interface Props {
    modelValue: boolean
    dialogType: string
    data?: Record<string, any>
  }

  interface Emits {
    (e: 'update:modelValue', value: boolean): void
    (e: 'success'): void
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    dialogType: 'add',
    data: undefined
  })

  const emit = defineEmits<Emits>()

  const formRef = ref<FormInstance>()
  const optionData = reactive({
    treeData: <any[]>[]
  })

  /**
   * 处理自动查找视图文件
   */
  const modules = import.meta.glob('/src/views/**/*.vue')
  const getModulesKey = () => {
    return Object.keys(modules).map((item) => item.replace('/src/views/', '/').replace('.vue', ''))
  }
  const componentsOptions = ref(getModulesKey())
  const querySearch = (queryString: string, cb: any) => {
    const results = queryString
      ? componentsOptions.value.filter((item) =>
          item.toLowerCase().includes(queryString.toLowerCase())
        )
      : componentsOptions.value
    cb(results.map((item) => ({ value: item })))
  }

  /**
   * 弹窗显示状态双向绑定
   */
  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

  /**
   * 表单验证规则
   */
  const rules = reactive<FormRules>({
    parent_id: [{ required: true, message: '请选择上级菜单', trigger: 'change' }],
    name: [{ required: true, message: '请输入菜单名称', trigger: 'blur' }],
    path: [{ required: true, message: '请输入路由地址', trigger: 'blur' }],
    component: [{ required: true, message: '请输入组件地址', trigger: 'change' }],
    code: [{ required: true, message: '请输入组件名称', trigger: 'blur' }],
    slug: [{ required: true, message: '请输入权限标识', trigger: 'blur' }],
    link_url: [{ required: true, message: '请输入外链地址', trigger: 'blur' }]
  })

  /**
   * 初始数据
   */
  const initialFormData = {
    id: null,
    parent_id: null,
    type: 1,
    component: '',
    name: '',
    slug: '',
    path: '',
    icon: '',
    code: '',
    remark: '',
    link_url: '',
    is_iframe: 2,
    is_keep_alive: 2,
    is_hidden: 2,
    is_fixed_tab: 2,
    is_full_page: 2,
    sort: 100,
    status: 1
  }

  /**
   * 表单数据
   */
  const formData = reactive({ ...initialFormData })

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
    // 先重置为初始值
    Object.assign(formData, initialFormData)

    const data = await api.list({ tree: true })
    //console.log(data)
    optionData.treeData = [
      {
        id: 0,
        value: 0,
        label: '无上级菜单',
        children: data
      }
    ]

    // 如果有数据，则填充数据
    if (props.data) {
      await nextTick()
      initForm()
    }
  }

  /**
   * 初始化表单数据
   */
  const initForm = () => {
    if (props.data) {
      for (const key in formData) {
        if (props.data[key] != null && props.data[key] != undefined) {
          ;(formData as any)[key] = props.data[key]
        }
      }
    }
  }

  /**
   * 关闭弹窗并重置表单
   */
  const handleClose = () => {
    visible.value = false
    formRef.value?.resetFields()
  }

  /**
   * 提交表单
   */
  const handleSubmit = async () => {
    if (!formRef.value) return

    try {
      await formRef.value.validate()
      if (props.dialogType === 'add') {
        await api.save(formData)
        ElMessage.success('新增成功')
      } else {
        await api.update(formData)
        ElMessage.success('修改成功')
      }
      emit('success')
      handleClose()
    } catch (error) {
      console.log('表单验证失败:', error)
    }
  }
</script>

<style lang="scss" scoped>
  :deep(.el-input-number) {
    width: 100%;
  }
</style>
