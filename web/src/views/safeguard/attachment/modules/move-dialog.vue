<template>
  <el-dialog
    v-model="visible"
    title="移动到分类"
    width="500px"
    align-center
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="100px">
      <el-form-item>
        <div class="text-gray-600 mb-2">
          已选择 <span class="text-primary font-medium">{{ selectedCount }}</span> 个文件
        </div>
      </el-form-item>
      <el-form-item label="目标分类" prop="category_id">
        <el-tree-select
          v-model="formData.category_id"
          :data="optionData.treeData"
          :render-after-expand="false"
          check-strictly
          clearable
          placeholder="请选择目标分类"
        />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" @click="handleSubmit">确定移动</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  import api from '@/api/safeguard/attachment'
  import categoryApi from '@/api/safeguard/category'
  import { ElMessage } from 'element-plus'
  import type { FormInstance, FormRules } from 'element-plus'

  interface Props {
    modelValue: boolean
    selectedRows: any[]
  }

  interface Emits {
    (e: 'update:modelValue', value: boolean): void
    (e: 'success'): void
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    selectedRows: () => []
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
   * 选中数量
   */
  const selectedCount = computed(() => props.selectedRows.length)

  /**
   * 表单验证规则
   */
  const rules = reactive<FormRules>({
    category_id: [{ required: true, message: '请选择目标分类', trigger: 'change' }]
  })

  /**
   * 初始数据
   */
  const initialFormData = {
    category_id: null
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
    // 重置为初始值
    Object.assign(formData, initialFormData)

    const data = await categoryApi.list({ tree: true })
    optionData.treeData = data
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

      const ids = props.selectedRows.map((row) => row.id)
      await api.move({
        ids: ids,
        category_id: formData.category_id
      })

      ElMessage.success(`成功移动 ${ids.length} 个文件`)
      emit('success')
      handleClose()
    } catch (error) {
      console.log('表单验证失败:', error)
    }
  }
</script>
