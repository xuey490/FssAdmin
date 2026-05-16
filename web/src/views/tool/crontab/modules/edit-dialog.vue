<template>
  <el-dialog
    v-model="visible"
    :title="dialogType === 'add' ? '新增定时任务' : '编辑定时任务'"
    width="800px"
    align-center
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="120px">
      <el-form-item label="任务名称" prop="name">
        <el-input v-model="formData.name" placeholder="请输入任务名称" />
      </el-form-item>
      <el-form-item label="任务类型" prop="type">
        <sa-select v-model="formData.type" dict="crontab_task_type" />
      </el-form-item>
      <el-form-item label="定时规则" prop="task_style">
        <el-space>
          <el-select v-model="formData.task_style" :style="{ width: '100px' }">
            <el-option :value="1" label="每天" />
            <el-option :value="2" label="每小时" />
            <el-option :value="3" label="N小时" />
            <el-option :value="4" label="N分钟" />
            <el-option :value="5" label="N秒" />
            <el-option :value="6" label="每周" />
            <el-option :value="7" label="每月" />
            <el-option :value="8" label="每年" />
          </el-select>
          <template v-if="formData.task_style == 8">
            <el-input-number
              v-model="formData.month"
              :precision="0"
              :min="1"
              :max="12"
              controls-position="right"
              :style="{ width: '100px' }"
            />
            <span>月</span>
          </template>
          <template v-if="formData.task_style > 6">
            <el-input-number
              v-model="formData.day"
              :precision="0"
              :min="1"
              :max="31"
              controls-position="right"
              :style="{ width: '100px' }"
            />
            <span>日</span>
          </template>
          <el-select
            v-if="formData.task_style == 6"
            v-model="formData.week"
            :style="{ width: '100px' }"
          >
            <el-option :value="1" label="周一" />
            <el-option :value="2" label="周二" />
            <el-option :value="3" label="周三" />
            <el-option :value="4" label="周四" />
            <el-option :value="5" label="周五" />
            <el-option :value="6" label="周六" />
            <el-option :value="0" label="周日" />
          </el-select>
          <template v-if="[1, 3, 6, 7, 8].includes(formData.task_style)">
            <el-input-number
              v-model="formData.hour"
              :precision="0"
              :min="0"
              :max="23"
              controls-position="right"
              :style="{ width: '100px' }"
            />
            <span>时</span>
          </template>
          <template v-if="formData.task_style != 5">
            <el-input-number
              v-model="formData.minute"
              :precision="0"
              :min="0"
              :max="59"
              controls-position="right"
              :style="{ width: '100px' }"
            />
            <span>分</span>
          </template>
          <template v-if="formData.task_style == 5">
            <el-input-number
              v-model="formData.second"
              :precision="0"
              :min="0"
              :max="59"
              controls-position="right"
              :style="{ width: '100px' }"
            />
            <span>秒</span>
          </template>
        </el-space>
      </el-form-item>
      <el-form-item label="调用目标" prop="target">
        <el-input
          v-model="formData.target"
          type="textarea"
          :rows="3"
          placeholder="请输入调用目标"
        />
      </el-form-item>
      <el-form-item label="任务参数" prop="params">
        <el-input
          v-model="formData.parameter"
          type="textarea"
          :rows="3"
          placeholder="请输入任务参数"
        />
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <sa-radio v-model="formData.status" dict="data_status" />
      </el-form-item>
      <el-form-item label="备注" prop="remark">
        <el-input v-model="formData.remark" type="textarea" :rows="2" placeholder="请输入备注" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" @click="handleSubmit">提交</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  import api from '@/api/tool/crontab'
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
    name: [{ required: true, message: '任务名称不能为空', trigger: 'blur' }],
    type: [{ required: true, message: '任务类型不能为空', trigger: 'blur' }],
    task_style: [{ required: true, message: '定时规则不能为空', trigger: 'blur' }],
    target: [{ required: true, message: '调用目标不能为空', trigger: 'blur' }]
  })

  /**
   * 初始数据
   */
  const initialFormData = {
    id: null,
    name: '',
    type: '',
    rule: '',
    task_style: 1,
    month: 1,
    day: 1,
    week: 1,
    hour: 1,
    minute: 1,
    second: 1,
    target: '',
    parameter: '',
    status: 1,
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

  /**
   * 初始化页面数据
   */
  const initPage = async () => {
    // 先重置为初始值
    Object.assign(formData, initialFormData)
    // 如果有数据，则填充数据
    if (props.data) {
      await nextTick()
      initForm()
    }
  }

  // 提取数字
  const extractNumber = (str: string) => {
    const match = str.match(/\d+/)
    return match ? Number.parseInt(match[0]) : 0
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
      const words = formData['rule'].split(' ')
      formData['second'] = extractNumber(words[0])
      formData['minute'] = extractNumber(words[1])
      formData['hour'] = extractNumber(words[2])
      formData['day'] = extractNumber(words[3])
      formData['month'] = extractNumber(words[4])
      formData['week'] = extractNumber(words[5])
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
        await api.create(formData)
        ElMessage.success('新增成功')
      } else if (formData.id !== null) {
        await api.update(formData.id, formData)
        ElMessage.success('修改成功')
      }
      emit('success')
      handleClose()
    } catch (error) {
      console.log('表单验证失败:', error)
    }
  }
</script>
