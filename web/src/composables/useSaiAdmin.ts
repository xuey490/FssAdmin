import { ref, nextTick } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'

/**
 * SaiAdmin Composable
 * SaiAdmin状态管理
 */
export function useSaiAdmin() {
  // 弹窗相关
  const dialogType = ref('add')
  const dialogVisible = ref(false)
  const dialogData = ref<Partial<Record<string, any>>>({})

  // 选中行
  const selectedRows = ref<Record<string, any>[]>([])

  // 显示弹窗
  const showDialog = (type: string, row?: Record<string, any>): void => {
    dialogType.value = type
    dialogData.value = row || {}
    nextTick(() => {
      dialogVisible.value = true
    })
  }

  // 隐藏弹窗
  const hideDialog = (): void => {
    dialogVisible.value = false
  }

  // 表格行选择变化
  const handleSelectionChange = (selection: Record<string, any>[]): void => {
    selectedRows.value = selection
  }

  // 删除数据
  const deleteRow = (
    row: Record<string, any>,
    apiFn: (params: any) => Promise<any>,
    callback?: () => void
  ): void => {
    ElMessageBox.confirm(`确定要删除该数据吗？`, '删除数据', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'error'
    }).then(() => {
      apiFn(row.id).then(() => {
        ElMessage.success('删除成功')
        if (callback) callback()
      })
    })
  }

  // 批量删除数据
  const deleteSelectedRows = (
    apiFn: (params: any) => Promise<any>,
    callback?: () => void
  ): void => {
    if (selectedRows.value.length === 0) {
      ElMessage.warning('请选择要删除的行')
      return
    }
    ElMessageBox.confirm(
      `确定要删除选中的 ${selectedRows.value.length} 条数据吗？`,
      '删除选中数据',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'error'
      }
    ).then(() => {
      apiFn({ ids: selectedRows.value.map((row) => row.id) }).then(() => {
        ElMessage.success('删除成功')
        if (callback) callback()
        selectedRows.value = []
      })
    })
  }

  return {
    dialogType,
    dialogVisible,
    dialogData,
    selectedRows,
    showDialog,
    hideDialog,
    handleSelectionChange,
    deleteRow,
    deleteSelectedRows
  }
}
