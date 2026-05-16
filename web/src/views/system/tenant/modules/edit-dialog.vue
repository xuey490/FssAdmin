<template>
  <el-dialog
    v-model="visible"
    :title="dialogType === 'add' ? '新增租户' : '编辑租户'"
    width="720px"
    align-center
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="110px">
      <el-row :gutter="16">
        <el-col :span="12">
          <el-form-item label="租户名称" prop="tenant_name">
            <el-input v-model="formData.tenant_name" placeholder="请输入租户名称" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="租户编码" prop="tenant_code">
            <el-input v-model="formData.tenant_code" placeholder="请输入租户编码" />
          </el-form-item>
        </el-col>
      </el-row>

      <el-row :gutter="16">
        <el-col :span="12">
          <el-form-item label="联系人" prop="contact_name">
            <el-input v-model="formData.contact_name" placeholder="请输入联系人姓名" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="联系电话" prop="contact_phone">
            <el-input v-model="formData.contact_phone" placeholder="请输入联系电话" />
          </el-form-item>
        </el-col>
      </el-row>

      <el-row :gutter="16">
        <el-col :span="12">
          <el-form-item label="联系邮箱" prop="contact_email">
            <el-input v-model="formData.contact_email" placeholder="请输入联系邮箱" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="到期时间" prop="expire_time">
            <el-date-picker
              v-model="formData.expire_time"
              type="datetime"
              value-format="YYYY-MM-DD HH:mm:ss"
              placeholder="请选择到期时间"
              clearable
              style="width: 100%"
            />
          </el-form-item>
        </el-col>
      </el-row>

      <el-row :gutter="16">
        <el-col :span="8">
          <el-form-item label="最大用户数" prop="max_users">
            <el-input-number v-model="formData.max_users" :min="0" :step="1" style="width: 100%" />
          </el-form-item>
        </el-col>
        <el-col :span="8">
          <el-form-item label="最大部门数" prop="max_depts">
            <el-input-number v-model="formData.max_depts" :min="0" :step="1" style="width: 100%" />
          </el-form-item>
        </el-col>
        <el-col :span="8">
          <el-form-item label="最大角色数" prop="max_roles">
            <el-input-number v-model="formData.max_roles" :min="0" :step="1" style="width: 100%" />
          </el-form-item>
        </el-col>
      </el-row>

      <el-form-item label="地址" prop="address">
        <el-input v-model="formData.address" placeholder="请输入租户地址" />
      </el-form-item>

      <el-form-item label="Logo地址" prop="logo_url">
        <el-input v-model="formData.logo_url" placeholder="请输入Logo URL" />
      </el-form-item>

      <el-form-item label="状态" prop="status">
        <sa-radio v-model="formData.status" dict="data_status" />
      </el-form-item>

      <el-form-item label="备注" prop="remark">
        <el-input v-model="formData.remark" type="textarea" :rows="3" placeholder="请输入备注" />
      </el-form-item>
    </el-form>

    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" @click="handleSubmit">提交</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  import api from '@/api/system/tenant'
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

  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

  const validateEmail = (_rule: any, value: string, callback: (error?: Error) => void) => {
    if (!value) {
      callback()
      return
    }

    const emailRegex = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/
    if (!emailRegex.test(value)) {
      callback(new Error('邮箱格式不正确'))
      return
    }

    callback()
  }

  const validateHttpUrl = (_rule: any, value: string, callback: (error?: Error) => void) => {
    if (!value) {
      callback()
      return
    }

    try {
      const url = new URL(value)
      if (url.protocol !== 'http:' && url.protocol !== 'https:') {
        callback(new Error('Logo地址必须以 http:// 或 https:// 开头'))
        return
      }
      callback()
    } catch (_e) {
      callback(new Error('Logo地址格式不正确'))
    }
  }

  const rules = reactive<FormRules>({
    tenant_name: [{ required: true, message: '请输入租户名称', trigger: 'blur' }],
    tenant_code: [{ required: true, message: '请输入租户编码', trigger: 'blur' }],
    contact_email: [{ validator: validateEmail, trigger: 'blur' }],
    logo_url: [{ validator: validateHttpUrl, trigger: 'blur' }]
  })

  const initialFormData = {
    id: null as number | null,
    tenant_name: '',
    tenant_code: '',
    contact_name: '',
    contact_phone: '',
    contact_email: '',
    address: '',
    logo_url: '',
    status: 1,
    expire_time: null as string | null,
    max_users: 0,
    max_depts: 0,
    max_roles: 0,
    remark: ''
  }

  const formData = reactive({ ...initialFormData })

  watch(
    () => props.modelValue,
    (newVal) => {
      if (newVal) initPage()
    }
  )

  const initPage = async () => {
    Object.assign(formData, initialFormData)
    if (props.data) {
      await nextTick()
      initForm()
    }
  }

  const initForm = () => {
    if (!props.data) return
    for (const key in formData) {
      if (props.data[key] !== null && props.data[key] !== undefined) {
        ;(formData as any)[key] = props.data[key]
      }
    }
  }

  const handleClose = () => {
    visible.value = false
    formRef.value?.resetFields()
  }

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
      console.error('租户保存失败:', error)
    }
  }
</script>
