<template>
  <div class="hrm-dashboard">
    <div class="art-card p-5 mb-5 max-sm:mb-4">
      <div class="flex-cb max-sm:block">
        <div class="flex-c">
          <img class="size-10 rounded-full object-cover mr-3" :src="avatar" alt="avatar" />
          <div>
            <h2 class="text-xl font-semibold"
              >{{ greeting }}，{{ userInfo.username }}，祝你开心每一天！</h2
            >
            <p class="text-g-600 mt-1 text-sm">今日温度：25℃ - 28℃</p>
          </div>
        </div>
        <div class="flex-c gap-10 max-sm:mt-4 max-sm:justify-between">
          <div>
            <p class="text-g-500 text-xs">用户量</p>
            <p class="text-2xl font-semibold mt-1">288</p>
          </div>
          <div>
            <p class="text-g-500 text-xs">活跃数</p>
            <p class="text-2xl font-semibold mt-1">66</p>
          </div>
          <div>
            <p class="text-g-500 text-xs">待办</p>
            <p class="text-2xl font-semibold mt-1">10 / 100</p>
          </div>
        </div>
      </div>
    </div>

    <ElRow :gutter="20">
      <ElCol :lg="9" :md="24" :sm="24">
        <div class="art-card p-5 h-75 mb-5 max-sm:mb-4">
          <div class="art-card-header pb-2 border-b border-g-200">
            <h4>加入QQ交流群</h4>
          </div>
          <div class="flex-cc h-[calc(100%-46px)]">
            <div class="p-2.5 border border-g-300 rounded-md bg-white">
              <img class="size-36 object-cover" :src="qrcode" alt="qrcode" />
            </div>
          </div>
        </div>
      </ElCol>
      <ElCol :lg="15" :md="24" :sm="24">
        <div class="art-card p-5 mb-5 max-sm:mb-4">
          <div class="art-card-header pb-2 border-b border-g-200">
            <h4>销售统计</h4>
          </div>
          <ArtBarChart
            class="mt-3"
            height="320px"
            :data="saleData"
            :xAxisData="monthData"
            :colors="saleColors"
            :showAxisLine="false"
            :showLegend="true"
          />
        </div>
      </ElCol>
    </ElRow>

    <div class="art-card p-5">
      <ElTabs v-model="activeTab">
        <ElTabPane label="项目列表" name="all" />
        <ElTabPane label="我的项目" name="mine" />
      </ElTabs>

      <ArtTable
        :data="currentProjects"
        :border="false"
        :stripe="false"
        :header-cell-style="{ background: 'transparent' }"
      >
        <template #default>
          <ElTableColumn label="项目名称" prop="name" width="180" />
          <ElTableColumn label="项目地址" prop="url" width="320">
            <template #default="{ row }">
              <ElLink :href="row.url" type="primary" target="_blank">{{ row.url }}</ElLink>
            </template>
          </ElTableColumn>
          <ElTableColumn label="项目描述" prop="desc" show-overflow-tooltip />
        </template>
      </ArtTable>
    </div>
  </div>
</template>

<script setup lang="ts">
  import type { BarDataItem } from '@/types/component/chart'
  import defaultAvatar from '@/assets/images/avatar/avatar.webp'
  import qqgroup from '@/assets/images/common/qqgroup.png'
  import { useUserStore } from '@/store/modules/user'

  defineOptions({ name: 'Hrm' })

  interface ProjectItem {
    name: string
    url: string
    desc: string
    mine?: boolean
  }

  const userStore = useUserStore()
  const userInfo = computed(() => userStore.getUserInfo)
  const avatar = computed(() => userInfo.value.avatar || defaultAvatar)
  const qrcode = qqgroup
  const activeTab = ref('all')

  const monthData = ref([
    '1月',
    '2月',
    '3月',
    '4月',
    '5月',
    '6月',
    '7月',
    '8月',
    '9月',
    '10月',
    '11月',
    '12月'
  ])
  const saleColors = ['#4d8dff', '#fb7348', '#f2c455', '#69c970']
  const saleData = ref<BarDataItem[]>([
    {
      name: '招聘',
      data: [80, 160, 100, 70, 50, 60, 90, 220, 140, 120, 210, 240]
    },
    { name: '培训', data: [30, 55, 40, 35, 30, 28, 45, 120, 85, 70, 110, 130] },
    {
      name: '绩效',
      data: [110, 150, 120, 90, 60, 75, 95, 260, 180, 150, 220, 280]
    },
    { name: '薪酬', data: [70, 120, 95, 80, 50, 60, 85, 200, 130, 110, 170, 210] }
  ])

  const projectList = ref<ProjectItem[]>([
    {
      name: 'FSSPHP',
      url: 'https://gitee.com/fsscms/NovaFrame',
      desc: '企业级php框架，使用workerman，symfony框架，作为基座，可扩展性高。',
      mine: true
    },
    {
      name: 'FSSADMIN',
      url: 'https://gitee.com/fsscms/FssAdmin',
      desc: '企业级Saas后台管理系统，使用 Vue3、TypeScript、ElementPlus 技术栈，支持多租户。',
      mine: true
    },
    {
      name: 'SpeedThinkphp',
      url: 'https://gitee.com/fsscms/speed-thinkphp',
      desc: '使用 Workerman 作为传统 ThinkPHP 8 框架（原本基于 FPM）的进程启动器，提高性能和稳定性，支持高并发场景。'
    },
    {
      name: 'FSSDB',
      url: 'https://gitee.com/fsscms/database',
      desc: '利用工厂接口类整合Eloquent 和 ThinkORM 的Model和DB的操作，实现两者的无缝切换'
    }
  ])

  const currentProjects = computed(() => {
    if (activeTab.value === 'mine') {
      return projectList.value.filter((item) => item.mine)
    }
    return projectList.value
  })

  const greeting = computed(() => {
    const hour = new Date().getHours()
    if (hour < 6) return '凌晨好'
    if (hour < 9) return '早安'
    if (hour < 12) return '上午好'
    if (hour < 14) return '中午好'
    if (hour < 18) return '下午好'
    return '晚上好'
  })
</script>
