<template>
  <el-dialog
    v-model="visible"
    :title="dialogType === 'add' ? '新增部门' : '编辑部门'"
    width="600px"
    align-center
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="120px">
      <el-form-item label="上级部门" prop="parent_id">
        <el-tree-select
          v-model="formData.parent_id"
          :data="optionData.treeData"
          :render-after-expand="false"
          check-strictly
          clearable
        />
      </el-form-item>
      <el-form-item label="部门名称" prop="name">
        <el-input v-model="formData.name" placeholder="请输入部门名称" />
      </el-form-item>
      <el-form-item label="部门编码" prop="code">
        <el-input v-model="formData.code" placeholder="请输入部门编码" />
      </el-form-item>
      <el-form-item label="部门领导">
        <sa-user v-model="formData.leader_id" value-type="object" clearable />
      </el-form-item>
      <el-form-item label="描述" prop="remark">
        <el-input
          v-model="formData.remark"
          type="textarea"
          :rows="3"
          placeholder="请输入部门描述"
        />
      </el-form-item>
      <el-form-item label="排序" prop="sort">
        <el-input-number v-model="formData.sort" placeholder="请输入排序" />
      </el-form-item>
      <el-form-item label="启用" prop="status">
        <sa-radio v-model="formData.status" dict="data_status" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" @click="handleSubmit">提交</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  import api from '@/api/system/dept'
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
    parent_id: [{ required: true, message: '请选择上级部门', trigger: 'change' }],
    name: [{ required: true, message: '请输入部门名称', trigger: 'blur' }],
    code: [{ required: true, message: '请输入部门编码', trigger: 'blur' }]
  })

  /**
   * 初始数据
   */
  const initialFormData = {
    id: null,
    parent_id: null,
    level: '',
    name: '',
    code: '',
    leader_id: null,
    remark: '',
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

    const data = await api.tree()
    optionData.treeData = [
      {
        id: 0,
        value: 0,
        label: '无上级部门',
        children: data
      }
    ]

    // 如果有数据，则填充数据
    if (props.data) {
      await nextTick()
      initForm()
    }
  }

  type LeaderOption = {
    id: number
    username: string
    realname: string
    email: string
    phone: string
    avatar?: string
    status: string
  }

  /**
   * 构建部门领导选择器初始值，确保编辑时显示姓名而非 ID
   */
  const buildLeaderValue = (data: Record<string, any>): LeaderOption | null => {
    const leaderId = Number(data.leader_id || 0)
    if (!leaderId) return null

    const leader = data.leader || {}
    return {
      id: leaderId,
      username: String(leader.username || ''),
      realname: String(data.leader_name || leader.realname || leader.username || ''),
      email: String(leader.email || ''),
      phone: String(leader.phone || ''),
      avatar: leader.avatar,
      status: String(leader.status ?? '1')
    }
  }

  /**
   * 提交时提取部门领导 ID
   */
  const resolveLeaderId = (leader: LeaderOption | number | null): number | null => {
    if (!leader) return null
    if (typeof leader === 'number') return leader
    const leaderId = Number(leader.id || 0)
    return leaderId > 0 ? leaderId : null
  }

  /**
   * 初始化表单数据
   */
  const initForm = () => {
    if (props.data) {
      for (const key in formData) {
        if (key === 'leader_id') continue
        if (props.data[key] != null && props.data[key] != undefined) {
          ;(formData as any)[key] = props.data[key]
        }
      }
      formData.leader_id = buildLeaderValue(props.data) as any
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
      const payload = {
        ...formData,
        leader_id: resolveLeaderId(formData.leader_id as LeaderOption | number | null)
      }
      if (props.dialogType === 'add') {
        await api.save(payload)
        ElMessage.success('新增成功')
      } else {
        await api.update(payload)
        ElMessage.success('修改成功')
      }
      emit('success')
      handleClose()
    } catch (error) {
      console.log('表单验证失败:', error)
    }
  }
</script>
