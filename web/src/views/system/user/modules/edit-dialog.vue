<template>
  <el-dialog
    v-model="visible"
    :title="dialogType === 'add' ? '新增用户' : '编辑用户'"
    width="800px"
    align-center
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="100px">
      <el-form-item label="头像" prop="avatar">
        <sa-image-picker v-model="formData.avatar" round />
      </el-form-item>
      <el-row>
        <el-col :span="12">
          <el-form-item label="用户名" prop="username">
            <el-input v-model="formData.username" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="真实姓名" prop="realname">
            <el-input v-model="formData.realname" />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row v-if="dialogType === 'add'">
        <el-col :span="12">
          <el-form-item label="密码" prop="password">
            <el-input type="password" v-model="formData.password" show-password />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="确认密码" prop="password_confirm">
            <el-input type="password" v-model="formData.password_confirm" show-password />
          </el-form-item>
        </el-col>
      </el-row>

      <el-row>
        <el-col :span="12">
          <el-form-item label="邮箱" prop="email">
            <el-input v-model="formData.email" placeholder="请输入邮箱" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="手机号" prop="phone">
            <el-input v-model="formData.phone" placeholder="请输入手机号" />
          </el-form-item>
        </el-col>
      </el-row>

      <el-row>
        <el-col :span="12">
          <el-form-item label="部门" prop="dept_id">
            <el-tree-select
              v-model="formData.dept_id"
              :data="optionData.deptData"
              :render-after-expand="false"
              check-strictly
              clearable
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="角色" prop="role_ids">
            <el-select v-model="formData.role_ids" multiple clearable>
              <el-option
                v-for="role in optionData.roleList"
                :key="(role as any)?.id"
                :value="(role as any)?.id"
                :label="(role as any)?.name"
              />
            </el-select>
          </el-form-item>
        </el-col>
      </el-row>

      <el-row>
        <el-col :span="12">
          <el-form-item label="岗位" prop="post_ids">
            <el-select v-model="formData.post_ids" multiple clearable>
              <el-option
                v-for="post in optionData.postList"
                :key="(post as any)?.id"
                :value="(post as any)?.id"
                :label="(post as any)?.name"
              />
            </el-select>
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="性别" prop="gender">
            <sa-radio v-model="formData.gender" dict="gender" valueType="string" />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row>
        <el-col :span="24">
          <el-form-item label="状态" prop="status">
            <sa-radio v-model="formData.status" dict="data_status" />
          </el-form-item>
        </el-col>
      </el-row>

      <el-row>
        <el-col :span="24">
          <el-form-item label="备注" prop="remark">
            <el-input
              v-model="formData.remark"
              type="textarea"
              :rows="3"
              placeholder="请输入备注"
            />
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleClose">取消</el-button>
        <el-button type="primary" @click="handleSubmit">提交</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  import type { FormInstance, FormRules } from 'element-plus'
  import api from '@/api/system/user'
  import deptApi from '@/api/system/dept'
  import roleApi from '@/api/system/role'
  import postApi from '@/api/system/post'

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
    deptData: <any>[],
    roleList: <any>[],
    postList: <any>[]
  })

  /**
   * 弹窗显示状态双向绑定
   */
  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

  const validatePasswordConfirm = (rule: any, value: any, callback: any) => {
    if (value !== formData.password) {
      callback(new Error('两次输入的密码不一致'))
    } else {
      callback()
    }
  }

  // 表单验证规则
  const rules: FormRules = {
    username: [
      { required: true, message: '请输入用户名', trigger: 'blur' },
      { min: 2, max: 20, message: '长度在 2 到 20 个字符', trigger: 'blur' }
    ],
    password: [
      { required: true, message: '请输入密码', trigger: 'blur' },
      { min: 6, max: 20, message: '长度在 6 到 20 个字符', trigger: 'blur' }
    ],
    password_confirm: [
      { required: true, message: '请输入确认密码', trigger: 'blur' },
      { min: 6, max: 20, message: '长度在 6 到 20 个字符', trigger: 'blur' },
      { validator: validatePasswordConfirm, trigger: 'blur' }
    ],
    dept_id: [{ required: true, message: '请选择部门', trigger: 'change' }],
    role_ids: [{ required: true, message: '请选择角色', trigger: 'blur' }]
  }

  // 初始表单数据
  const initialFormData = {
    id: null,
    avatar: '',
    username: '',
    password: '',
    password_confirm: '',
    realname: '',
    dept_id: '',
    phone: '',
    email: '',
    role_ids: [],
    post_ids: [],
    status: 1,
    gender: '',
    remark: ''
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
  // 初始化页面数据
  const initPage = async () => {
    // 先重置为初始值
    Object.assign(formData, initialFormData)
    // 部门数据
    const deptData = await deptApi.accessDept()
    optionData.deptData = deptData
    // 角色数据
    const roleData = await roleApi.accessRole()
    optionData.roleList = roleData
    // 岗位数据
    const postData = await postApi.accessPost()
    optionData.postList = postData
    // 如果有数据，则填充数据
    if (props.data) {
      await nextTick()
      if (props.data.id) {
        let data = await api.read(props.data.id)
        if (data.postList) {
          const post = (data.postList as any[])?.map((item: any) => item.id)
          data.post_ids = post
        }
        const role = (data.roleList as any[])?.map((item: any) => item.id)
        data.role_ids = role
        data.password = ''
        initForm(data)
      }
    }
  }

  /**
   * 初始化表单数据
   */
  const initForm = (data: any) => {
    if (data) {
      for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
          ;(formData as any)[key] = data[key]
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
