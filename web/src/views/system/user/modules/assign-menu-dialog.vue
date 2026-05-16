<template>
  <el-dialog
    v-model="visible"
    title="分配菜单"
    width="500px"
    align-center
    :close-on-click-modal="false"
    @open="handleOpen"
    @close="handleClose"
  >
    <el-scrollbar max-height="420px">
      <el-tree
        ref="treeRef"
        :data="menuTree"
        :props="{ label: 'label', children: 'children' }"
        node-key="id"
        show-checkbox
        default-expand-all
        :check-strictly="false"
        v-loading="loading"
      />
    </el-scrollbar>
    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" :loading="saving" @click="handleSubmit">保存</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  import { ElMessage } from 'element-plus'
  import type { ElTree } from 'element-plus'
  import userApi from '@/api/system/user'
  import menuApi from '@/api/system/menu'

  interface Props {
    modelValue: boolean
    data?: Record<string, any>
  }

  interface Emits {
    (e: 'update:modelValue', value: boolean): void
    (e: 'success'): void
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    data: undefined
  })

  const emit = defineEmits<Emits>()

  const treeRef = ref<InstanceType<typeof ElTree>>()
  const menuTree = ref<any[]>([])
  const loading = ref(false)
  const saving = ref(false)

  const visible = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
  })

  /**
   * 从树中收集所有叶子节点ID（没有 children 或 children 为空的节点）
   */
  function collectLeafIds(nodes: any[], ids: Set<number>) {
    for (const node of nodes) {
      if (!node.children || node.children.length === 0) {
        ids.add(node.id)
      } else {
        collectLeafIds(node.children, ids)
      }
    }
  }

  async function handleOpen() {
    if (!props.data?.id) return
    loading.value = true
    try {
      const [treeRes, menuIdsRes] = await Promise.all([
        menuApi.accessMenu({ tree: true }),
        userApi.getUserMenus(props.data.id)
      ])
      menuTree.value = (treeRes as any) || []
      const menuIds: number[] = (menuIdsRes as any) || []

      // 收集树中所有叶子节点ID
      const leafIds = new Set<number>()
      collectLeafIds(menuTree.value, leafIds)

      // 只对叶子节点回显，避免父子联动导致多选
      const checkedLeafIds = menuIds.filter((id) => leafIds.has(id))

      await nextTick()
      treeRef.value?.setCheckedKeys(checkedLeafIds, false)
    } finally {
      loading.value = false
    }
  }

  async function handleSubmit() {
    if (!props.data?.id) return
    saving.value = true
    try {
      // 获取选中的节点（包含半选父节点）
      const checkedKeys = treeRef.value?.getCheckedKeys(false) as number[]
      const halfCheckedKeys = treeRef.value?.getHalfCheckedKeys() as number[]
      const allKeys = [...new Set([...checkedKeys, ...halfCheckedKeys])]
      await userApi.saveUserMenus(props.data.id, allKeys)
      ElMessage.success('分配菜单成功')
      emit('success')
      visible.value = false
    } catch (e: any) {
      ElMessage.error(e?.message || '保存失败')
    } finally {
      saving.value = false
    }
  }

  function handleClose() {
    menuTree.value = []
    treeRef.value?.setCheckedKeys([])
    visible.value = false
  }
</script>
