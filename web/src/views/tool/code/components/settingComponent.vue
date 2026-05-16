<template>
  <el-dialog
    v-model="visible"
    :title="`设置组件 - ${row?.column_comment}`"
    width="600px"
    draggable
    destroy-on-close
    @close="handleClose"
  >
    <el-form :model="form" label-width="120px">
      <!-- 编辑器相关 -->
      <template v-if="row.view_type === 'editor'">
        <el-form-item label="编辑器高度" prop="height">
          <el-input-number v-model="form.height" :max="1000" :min="100" />
        </el-form-item>
      </template>

      <!-- 上传、资源选择器相关 -->
      <template
        v-if="['uploadImage', 'imagePicker', 'uploadFile', 'chunkUpload'].includes(row.view_type)"
      >
        <el-form-item label="是否多选" prop="multiple">
          <el-radio-group v-model="form.multiple">
            <el-radio :value="true">是</el-radio>
            <el-radio :value="false">否</el-radio>
          </el-radio-group>
          <div class="text-xs text-gray-400 ml-2">多个文件必须选是，字段自动处理为数组</div>
        </el-form-item>
        <el-form-item label="数量限制" prop="limit">
          <el-input-number v-model="form.limit" :max="10" :min="1" />
          <div class="text-xs text-gray-400 ml-2">限制上传数量</div>
        </el-form-item>
      </template>

      <!-- 用户选择器 -->
      <template v-if="row.view_type === 'userSelect'">
        <el-form-item label="是否多选" prop="multiple">
          <el-radio-group v-model="form.multiple">
            <el-radio :value="true">是</el-radio>
            <el-radio :value="false">否</el-radio>
          </el-radio-group>
          <div class="text-xs text-gray-400 ml-2">多个用户，字段自动处理为数组</div>
        </el-form-item>
      </template>

      <!-- 日期、时间选择器 -->
      <template v-if="['date'].includes(row.view_type)">
        <el-form-item label="选择器类型" prop="mode">
          <el-select v-model="form.mode" clearable>
            <el-option label="日期选择器" value="date" />
            <el-option label="日期时间择器" value="datetime" />
          </el-select>
        </el-form-item>
      </template>
    </el-form>

    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" @click="save">确定</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  const emit = defineEmits<{
    (e: 'confirm', name: string, value: any): void
  }>()

  const visible = ref(false)
  const row = ref<any>({})
  const form = ref<any>({})

  /**
   * 打开弹窗
   */
  const open = (record: any) => {
    row.value = record
    if (
      record.view_type === 'uploadImage' ||
      record.view_type === 'imagePicker' ||
      record.view_type === 'uploadFile' ||
      record.view_type === 'chunkUpload'
    ) {
      form.value = record.options ? { ...record.options } : { multiple: false }
    } else if (record.view_type === 'editor') {
      form.value = record.options ? { ...record.options } : { height: 400 }
    } else if (record.view_type === 'date' || record.view_type === 'datetime') {
      form.value = record.options ? { ...record.options } : { mode: record.view_type }
    } else if (record.view_type === 'userSelect') {
      form.value = record.options ? { ...record.options } : { multiple: false }
    } else {
      form.value = record.options ? { ...record.options } : {}
    }
    visible.value = true
  }

  /**
   * 保存
   */
  const save = () => {
    emit('confirm', row.value.column_name, form.value)
    handleClose()
  }

  /**
   * 关闭弹窗
   */
  const handleClose = () => {
    visible.value = false
  }

  defineExpose({ open })
</script>
