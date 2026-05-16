<template>
  <el-dialog
    v-model="visible"
    :title="title"
    width="min(840px, calc(100vw - 32px))"
    top="4vh"
    destroy-on-close
    :close-on-click-modal="false"
    body-class="article-form-dialog__body"
    header-class="article-form-dialog__header"
    footer-class="article-form-dialog__footer"
    @close="handleClose"
    class="article-form-dialog"
  >
    <div class="article-form-dialog__content">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
        <el-form-item label="文章标题" prop="title">
          <el-input v-model="form.title" placeholder="请输入文章标题" clearable />
        </el-form-item>

        <el-form-item label="文章分类" prop="category_id">
          <el-input-number v-model="form.category_id" placeholder="请输入分类ID" :min="1" style="width: 100%" />
        </el-form-item>

        <el-form-item label="作者" prop="author">
          <el-input v-model="form.author" placeholder="请输入作者" clearable />
        </el-form-item>

        <el-form-item label="封面图片" prop="image">
          <sa-image-picker v-model="form.image" :limit="1" :multiple="false" />
        </el-form-item>

        <el-form-item label="文章简介" prop="describe">
          <el-input
            v-model="form.describe"
            type="textarea"
            :rows="3"
            placeholder="请输入文章简介"
            clearable
          />
        </el-form-item>

        <el-form-item label="是否外链" prop="is_link">
          <el-radio-group v-model="form.is_link">
            <el-radio :label="2">否</el-radio>
            <el-radio :label="1">是</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item v-if="form.is_link === 1" label="外链地址" prop="link_url">
          <el-input v-model="form.link_url" placeholder="请输入外链地址" clearable />
        </el-form-item>

        <el-form-item v-if="form.is_link === 2" label="文章内容" prop="content">
          <sa-editor v-model="form.content" height="320px" />
        </el-form-item>

        <el-form-item label="是否热门" prop="is_hot">
          <el-radio-group v-model="form.is_hot">
            <el-radio :label="2">否</el-radio>
            <el-radio :label="1">是</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="排序" prop="sort">
          <el-input-number v-model="form.sort" :min="0" placeholder="请输入排序" style="width: 100%" />
        </el-form-item>

        <el-form-item label="状态" prop="status">
          <sa-radio v-model="form.status" dict="data_status" />
        </el-form-item>
      </el-form>
    </div>

    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, nextTick } from 'vue'
import { ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import api from '@/api/article'
import SaImagePicker from '@/components/sai/sa-image-picker/index.vue'
import SaEditor from '@/components/sai/sa-editor/index.vue'
import SaRadio from '@/components/sai/sa-radio/index.vue'
import './form.css'

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
const submitLoading = ref(false)

const visible = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const title = computed(() => props.dialogType === 'add' ? '新增文章' : '编辑文章')

const initialFormData = {
  id: null,
  title: '',
  category_id: null,
  author: '',
  image: '',
  describe: '',
  content: '',
  is_link: 2,
  link_url: '',
  is_hot: 2,
  sort: 100,
  status: 1
}

const form = reactive({ ...initialFormData })

// 动态验证规则
const rules = computed<FormRules>(() => {
  const baseRules: FormRules = {
    title: [{ required: true, message: '请输入文章标题', trigger: 'blur' }],
    category_id: [{ required: true, message: '请选择文章分类', trigger: 'change' }],
    describe: [{ required: true, message: '请输入文章简介', trigger: 'blur' }]
  }

  // 如果是外链，验证外链地址
  if (form.is_link === 1) {
    baseRules.link_url = [
      { required: true, message: '请输入外链地址', trigger: 'blur' },
      { type: 'url', message: '请输入正确的URL格式', trigger: 'blur' }
    ]
  }

  // 如果不是外链，验证文章内容
  if (form.is_link === 2) {
    baseRules.content = [
      { required: true, message: '请输入文章内容', trigger: 'blur' }
    ]
  }

  return baseRules
})

watch(
  () => props.modelValue,
  (newVal) => {
    if (newVal) {
      initPage()
    }
  }
)

const initPage = async () => {
  Object.assign(form, initialFormData)
  if (props.data) {
    await nextTick()
    initForm()
  }
}

const initForm = () => {
  if (props.data) {
    for (const key in form) {
      if (props.data[key] != null && props.data[key] != undefined) {
        ;(form as any)[key] = props.data[key]
      }
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
    submitLoading.value = true
    
    if (props.dialogType === 'add') {
      await api.create(form)
      ElMessage.success('创建成功')
    } else {
      await api.update(form)
      ElMessage.success('更新成功')
    }
    
    emit('success')
    handleClose()
  } catch (error) {
    console.log('表单验证失败:', error)
  } finally {
    submitLoading.value = false
  }
}
</script>