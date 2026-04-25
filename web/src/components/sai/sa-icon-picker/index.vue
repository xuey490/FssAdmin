<template>
  <el-popover placement="bottom-start" :width="460" trigger="click" v-model:visible="visible">
    <template #reference>
      <div
        class="w-full relative cursor-pointer"
        @mouseenter="hovering = true"
        @mouseleave="hovering = false"
      >
        <el-input v-bind="$attrs" v-model="modelValue" readonly placeholder="点击选择图标">
          <template #prepend>
            <div class="w-8 flex items-center justify-center">
              <Icon v-if="modelValue" :icon="modelValue" class="text-lg" />
              <el-icon v-else class="text-lg text-gray-400"><Search /></el-icon>
            </div>
          </template>
        </el-input>
        <div
          v-if="hovering && modelValue && !disabled"
          class="absolute right-2 top-0 h-full flex items-center cursor-pointer text-gray-400 hover:text-gray-600"
          @click.stop="handleClear"
        >
          <el-icon><CircleClose /></el-icon>
        </div>
      </div>
    </template>

    <div class="icon-picker">
      <div class="mb-3">
        <el-input
          v-model="searchText"
          placeholder="搜索图标（英文关键词）"
          clearable
          prefix-icon="Search"
          @input="handleSearch"
        />
      </div>

      <div class="h-[300px] overflow-y-auto custom-scrollbar">
        <div v-if="searchText" class="search-results">
          <div v-if="filteredIcons.length === 0" class="text-center text-gray-400 py-8">
            未找到相关图标
          </div>
          <div v-else class="grid grid-cols-6 gap-2">
            <div
              v-for="icon in filteredIcons"
              :key="icon.name"
              class="icon-item flex flex-col items-center justify-center p-2 rounded cursor-pointer hover:bg-gray-100 transition-colors"
              :class="{ 'bg-primary-50 text-primary': modelValue === icon.name }"
              @click="handleSelect(icon.name)"
              :title="icon.name"
            >
              <Icon :icon="icon.name" class="text-2xl mb-1" />
            </div>
          </div>
        </div>

        <div v-else>
          <el-collapse v-model="activeNames">
            <el-collapse-item
              v-for="category in categories"
              :key="category.name"
              :title="category.name"
              :name="category.name"
            >
              <div class="grid grid-cols-6 gap-2">
                <div
                  v-for="icon in category.icons"
                  :key="icon.name"
                  class="icon-item flex flex-col items-center justify-center p-2 rounded cursor-pointer hover:bg-gray-100 transition-colors"
                  :class="{ 'bg-primary-50 text-primary': modelValue === icon.name }"
                  @click="handleSelect(icon.name)"
                  :title="icon.name"
                >
                  <Icon :icon="icon.name" class="text-2xl mb-1" />
                </div>
              </div>
            </el-collapse-item>
          </el-collapse>
        </div>
      </div>
    </div>
  </el-popover>
</template>

<script lang="ts" setup>
  import { Search, CircleClose } from '@element-plus/icons-vue'
  import { Icon } from '@iconify/vue'

  import rawIcons from './lib/RemixIcon.json'

  defineOptions({ name: 'SaIconPicker', inheritAttrs: false })

  interface Props {
    disabled?: boolean
  }

  const props = withDefaults(defineProps<Props>(), {
    disabled: false
  })

  const modelValue = defineModel<string>()
  const visible = ref(false)
  const searchText = ref('')
  const hovering = ref(false)
  const activeNames = ref(['Arrows']) // 默认展开第一个分类

  // 处理图标数据
  interface IconItem {
    name: string
    tags: string
  }

  interface Category {
    name: string
    icons: IconItem[]
  }

  // 计算属性缓存处理后的图标数据
  const { allIcons, categories } = useMemo(() => {
    const all: IconItem[] = []
    const cats: Category[] = []

    for (const [categoryName, icons] of Object.entries(rawIcons)) {
      const iconList = icons as string[]

      const categoryIcons: IconItem[] = []
      for (const name of iconList) {
        const iconName = `ri:${name}`
        const item = { name: iconName, tags: name }
        categoryIcons.push(item)
        all.push(item)
      }

      cats.push({
        name: categoryName,
        icons: categoryIcons
      })
    }

    return { allIcons: all, categories: cats }
  })

  // 简单的 hook 模拟 useMemo，在 vue 中直接执行即可，因为 rawIcons 是静态的
  function useMemo<T>(fn: () => T): T {
    return fn()
  }

  // 搜索过滤
  const filteredIcons = computed(() => {
    if (!searchText.value) return []

    const query = searchText.value.toLowerCase()
    return allIcons
      .filter(
        (icon) => icon.name.toLowerCase().includes(query) || icon.tags.toLowerCase().includes(query)
      )
      .slice(0, 100) // 限制显示数量以提高性能
  })

  const handleSearch = () => {
    // 搜索逻辑主要由 computed 处理
  }

  const handleSelect = (icon: string) => {
    modelValue.value = icon
    visible.value = false
    searchText.value = ''
  }

  const handleClear = () => {
    modelValue.value = ''
  }
</script>

<style scoped>
  .custom-scrollbar::-webkit-scrollbar {
    width: 6px;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e5e7eb;
    border-radius: 3px;
  }
  .custom-scrollbar::-webkit-scrollbar-track {
    background-color: transparent;
  }
</style>
