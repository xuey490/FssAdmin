<!-- 通知组件 -->
<template>
  <div
    class="art-notification-panel art-card-sm !shadow-xl"
    :style="{
      transform: show ? 'scaleY(1)' : 'scaleY(0.9)',
      opacity: show ? 1 : 0
    }"
    v-show="visible"
    @click.stop
  >
    <div class="flex-cb px-3.5 mt-3.5">
      <span class="text-base font-medium text-g-800">{{ $t('notice.title') }}</span>
      <span
        class="text-xs text-g-800 px-1.5 py-1 c-p select-none rounded hover:bg-g-200"
        @click="markAllReadByGroup"
      >
        {{ $t('notice.btnRead') }}
      </span>
    </div>

    <ul class="box-border flex items-end w-full h-12.5 px-3.5 border-b-d">
      <li
        v-for="(item, index) in barList"
        :key="item.key"
        class="h-12 leading-12 mr-5 overflow-hidden text-[13px] text-g-700 c-p select-none"
        :class="{ 'bar-active': barActiveIndex === index }"
        @click="changeBar(index)"
      >
        {{ item.name }} ({{ item.num }})
      </li>
    </ul>

    <div class="w-full h-[calc(100%-95px)]">
      <div class="h-[calc(100%-60px)] overflow-y-scroll scrollbar-thin">
        <ul v-if="currentList.length > 0">
          <li
            v-for="item in currentList"
            :key="item.id"
            class="box-border flex-cb px-3.5 py-3.5 c-p last:border-b-0 hover:bg-g-200/60"
          >
            <div class="w-[calc(100%-70px)]" @click="goDetail(item.instance_id)">
              <h4 class="text-sm font-normal leading-5.5 text-g-900 truncate">{{ item.title }}</h4>
              <p class="mt-1 text-xs text-g-500">
                事件：{{ resolveEventTypeText(item.event_type_name, item.event_type) }} / 类型：{{
                  resolveMessageTypeText(item.message_type_name, item.message_type)
                }}
              </p>
              <p class="mt-1.5 text-xs text-g-500 truncate">{{ item.content || '-' }}</p>
              <p class="mt-1 text-xs text-g-500">{{ item.create_time }}</p>
            </div>
            <div class="w-16 text-right">
              <el-button
                v-if="Number(item.is_read) === 0"
                link
                type="primary"
                class="!px-0"
                @click.stop="markRead(item.id)"
              >
                已读
              </el-button>
            </div>
          </li>
        </ul>

        <div v-else class="relative top-25 h-full text-g-500 text-center !bg-transparent">
          <ArtSvgIcon icon="system-uicons:inbox" class="text-5xl" />
          <p class="mt-3.5 text-xs !bg-transparent"
            >{{ $t('notice.text[0]') }}{{ currentTabName }}</p
          >
        </div>
      </div>

      <div class="relative box-border w-full px-3.5">
        <ElButton class="w-full mt-3" @click="handleViewAll" v-ripple>
          {{ $t('notice.viewAll') }}
        </ElButton>
      </div>
    </div>

    <div class="h-25"></div>
  </div>
</template>

<script setup lang="ts">
  import { computed, ref, watch, onMounted, onUnmounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { useIntervalFn } from '@vueuse/core'
  import api from '@/api/flow/message'

  defineOptions({ name: 'ArtNotification' })

  const props = defineProps<{
    value: boolean
  }>()

  const emit = defineEmits<{
    'update:value': [value: boolean]
    'unread-change': [count: number]
  }>()

  const router = useRouter()
  const show = ref(false)
  const visible = ref(false)
  const barActiveIndex = ref(0)
  const loading = ref(false)
  const grouped = ref({
    groups: [
      { message_type: 'cc', message_type_name: '抄送', unread_count: 0, list: [] as any[] },
      { message_type: 'notify', message_type_name: '通知', unread_count: 0, list: [] as any[] },
      { message_type: 'system', message_type_name: '系统', unread_count: 0, list: [] as any[] }
    ],
    total_unread: 0
  })

  const barList = computed(() =>
    grouped.value.groups.map((item) => ({
      key: item.message_type,
      name: item.message_type_name,
      num: Number(item.unread_count || 0)
    }))
  )
  const currentGroup = computed(
    () => grouped.value.groups[barActiveIndex.value] || grouped.value.groups[0]
  )
  const currentList = computed(() => currentGroup.value?.list || [])
  const currentTabName = computed(() => currentGroup.value?.message_type_name || '消息')
  const eventTypeNameMap: Record<string, string> = {
    start: '发起',
    resubmit: '重新提交',
    approve: '同意',
    reject: '驳回',
    return: '退回',
    transfer: '转办',
    add_sign: '加签',
    remove_sign: '减签',
    copy: '抄送',
    delegate: '委托',
    cancel: '撤销',
    terminate: '终止'
  }
  const messageTypeNameMap: Record<string, string> = {
    cc: '抄送',
    notify: '通知',
    system: '系统'
  }

  const resolveEventTypeText = (name: unknown, code: unknown) => {
    const text = String(name || '').trim()
    if (text) return text
    return eventTypeNameMap[String(code || '').trim()] || String(code || '-')
  }

  const resolveMessageTypeText = (name: unknown, code: unknown) => {
    const text = String(name || '').trim()
    if (text) return text
    return messageTypeNameMap[String(code || '').trim()] || String(code || '-')
  }

  const loadGrouped = async () => {
    if (loading.value) return
    loading.value = true
    try {
      const res = await api.grouped({ limit: 10 })
      const data = (res || {}) as any
      grouped.value = {
        groups: Array.isArray(data.groups)
          ? data.groups
          : [
              { message_type: 'cc', message_type_name: '抄送', unread_count: 0, list: [] },
              { message_type: 'notify', message_type_name: '通知', unread_count: 0, list: [] },
              { message_type: 'system', message_type_name: '系统', unread_count: 0, list: [] }
            ],
        total_unread: Number(data.total_unread || 0)
      }
      emit('unread-change', Number(grouped.value.total_unread || 0))
    } finally {
      loading.value = false
    }
  }

  const markRead = async (id: number) => {
    await api.read(id)
    await loadGrouped()
  }

  const markAllReadByGroup = async () => {
    const type = (currentGroup.value?.message_type || '').toString()
    await api.readAll(type)
    await loadGrouped()
  }

  const goDetail = (instanceId: number) => {
    if (Number(instanceId || 0) <= 0) return
    emit('update:value', false)
    router.push(`/flow/instance/detail/${instanceId}`)
  }

  const handleViewAll = () => {
    emit('update:value', false)
    router.push('/flow/message')
  }

  const changeBar = (index: number) => {
    barActiveIndex.value = index
  }

  const showNotice = (open: boolean) => {
    if (open) {
      visible.value = true
      setTimeout(() => {
        show.value = true
      }, 5)
    } else {
      show.value = false
      setTimeout(() => {
        visible.value = false
      }, 350)
    }
  }

  const { pause, resume } = useIntervalFn(
    async () => {
      await loadGrouped()
    },
    20000,
    { immediate: false }
  )

  const handleVisibility = async () => {
    if (document.hidden) {
      pause()
      return
    }
    await loadGrouped()
    resume()
  }

  watch(
    () => props.value,
    async (newValue) => {
      showNotice(newValue)
      if (newValue) {
        await loadGrouped()
      }
    }
  )

  onMounted(async () => {
    await loadGrouped()
    resume()
    document.addEventListener('visibilitychange', handleVisibility)
  })

  onUnmounted(() => {
    pause()
    document.removeEventListener('visibilitychange', handleVisibility)
  })
</script>

<style scoped>
  @reference '@styles/core/tailwind.css';

  .art-notification-panel {
    @apply absolute 
    top-14.5 
    right-5 
    w-90 
    h-125 
    overflow-hidden 
    transition-all 
    duration-300
    origin-top 
    will-change-[top,left] 
    max-[640px]:top-[65px]
    max-[640px]:right-0
    max-[640px]:w-full 
    max-[640px]:h-[80vh];
  }

  .bar-active {
    color: var(--theme-color) !important;
    border-bottom: 2px solid var(--theme-color);
  }

  .scrollbar-thin::-webkit-scrollbar {
    width: 5px !important;
  }

  .dark .scrollbar-thin::-webkit-scrollbar-track {
    background-color: var(--default-box-color);
  }

  .dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background-color: #222 !important;
  }
</style>
