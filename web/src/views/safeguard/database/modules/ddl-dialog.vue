<template>
  <el-dialog
    v-model="visible"
    :title="`建表语句 - ${props.data?.name}`"
    width="860px"
    align-center
    @close="handleClose"
  >
    <div v-loading="loading">
      <pre class="ddl-code">{{ sql }}</pre>
    </div>
  </el-dialog>
</template>

<script setup lang="ts">
  import api from '@/api/safeguard/database'

  interface Props {
    modelValue: boolean
    data?: Record<string, any>
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    data: undefined
  })

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()

  const visible = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
  })

  const loading = ref(false)
  const sql = ref('')

  watch(
    () => props.modelValue,
    async (val) => {
      if (val && props.data?.name) {
        loading.value = true
        try {
          const response: any = await api.getDdl({ table: props.data.name })
          // 返回格式为 { code: 200, data: { table: '...', sql: '...' }, message: 'success' }
          sql.value = response.sql || ''
        } finally {
          loading.value = false
        }
      }
    }
  )

  const handleClose = () => {
    visible.value = false
    sql.value = ''
  }
</script>

<style scoped>
.ddl-code {
  margin: 0;
  padding: 16px;
  background: var(--el-fill-color-light);
  border-radius: var(--el-border-radius-base);
  font-size: 13px;
  line-height: 1.6;
  white-space: pre-wrap;
  word-break: break-all;
  max-height: 520px;
  overflow-y: auto;
  font-family: 'Courier New', Courier, monospace;
}
</style>
