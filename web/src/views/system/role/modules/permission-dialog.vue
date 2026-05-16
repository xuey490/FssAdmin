<template>
  <ElDialog
    v-model="visible"
    title="菜单权限"
    width="600px"
    align-center
    class="el-dialog-border"
    @close="handleClose"
  >
    <ElScrollbar height="70vh">
      <ElTree
        ref="treeRef"
        :data="menuList"
        show-checkbox
        node-key="id"
        :default-expand-all="false"
        :check-strictly="false"
      >
        <template #default="{ data }">
          <div class="flex-c gap-2">
            <span>{{ data.label }}</span>
            <ElTag :type="getMenuTypeTag(data)" size="small">{{ getMenuTypeText(data) }}</ElTag>
          </div>
        </template>
      </ElTree>
    </ElScrollbar>
    <template #footer>
      <ElButton @click="toggleExpandAll" v-ripple>
        <template #icon>
          <ArtSvgIcon v-if="isExpandAll" icon="ri:collapse-diagonal-line" />
          <ArtSvgIcon v-else icon="ri:expand-diagonal-line" />
        </template>
        {{ isExpandAll ? '收起' : '展开' }}
      </ElButton>
      <ElButton type="primary" @click="savePermission">保存</ElButton>
    </template>
  </ElDialog>
</template>

<script setup lang="ts">
  import api from '@/api/system/role'
  import menuApi from '@/api/system/menu'

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

  const menuList = ref<Api.Common.ApiData[]>([])
  const treeRef = ref()

  const isExpandAll = ref(true)

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

  /**
   * 弹窗显示状态双向绑定
   */
  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

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
    // 菜单列表
    menuList.value = await menuApi.accessMenu({ tree: true })
    // 角色已分配菜单
    const data = await api.menuByRole({ id: props.data?.id })
    const menuIds: number[] = data.menus?.map((item: any) => item.id) || []

    // 收集树中所有叶子节点ID
    const leafIds = new Set<number>()
    collectLeafIds(menuList.value, leafIds)

    // 只对叶子节点回显，避免父子联动导致多选
    const checkedLeafIds = menuIds.filter((id) => leafIds.has(id))

    await nextTick()
    treeRef.value?.setCheckedKeys(checkedLeafIds, false)
  }

  /**
   * 获取菜单类型标签颜色
   * @param row 菜单行数据
   * @returns 标签颜色类型
   */
  const getMenuTypeTag = (row: any): 'primary' | 'success' | 'warning' | 'info' | 'danger' => {
    if (row.type == 1) return 'info'
    if (row.type == 2) return 'primary'
    if (row.type == 3) return 'danger'
    if (row.type == 4) return 'success'
    return 'info'
  }

  /**
   * 获取菜单类型文本
   * @param row 菜单行数据
   * @returns 菜单类型文本
   */
  const getMenuTypeText = (row: any): string => {
    if (row.type == 1) return '目录'
    if (row.type == 2) return '菜单'
    if (row.type == 3) return '按钮'
    if (row.type == 4) return '外链'
    return '未知'
  }

  /**
   * 关闭弹窗并清空选中状态
   */
  const handleClose = () => {
    visible.value = false
    treeRef.value?.setCheckedKeys([])
  }

  /**
   * 保存权限配置
   */
  const savePermission = async () => {
    // 获取全选节点 + 半选父节点，确保父子联动完整性
    const checkedKeys = treeRef.value.getCheckedKeys(false) as number[]
    const halfCheckedKeys = treeRef.value.getHalfCheckedKeys() as number[]
    const allKeys = [...new Set([...checkedKeys, ...halfCheckedKeys])]
    try {
      await api.menuPermission({
        id: props.data?.id,
        menu_ids: allKeys
      })
      ElMessage.success('保存成功')
      emit('success')
      handleClose()
    } catch (error) {
      console.log('表单验证失败:', error)
    }
  }

  /**
   * 切换全部展开/收起状态
   */
  const toggleExpandAll = () => {
    const tree = treeRef.value
    if (!tree) return

    const nodes = tree.store.nodesMap
    // 这里保留 any，因为 Element Plus 的内部节点类型较复杂
    Object.values(nodes).forEach((node: any) => {
      node.expanded = !isExpandAll.value
    })

    isExpandAll.value = !isExpandAll.value
  }
</script>
