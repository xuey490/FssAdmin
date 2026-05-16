<!-- 工作台页面 -->
<template>
  <div>
    <template v-if="userInfo.dashboard === 'statistics' || !userInfo.dashboard">
      <CardList></CardList>

      <ElRow :gutter="20">
        <ElCol :sm="24" :md="12" :lg="10">
          <ActiveUser />
        </ElCol>
        <ElCol :sm="24" :md="12" :lg="14">
          <SalesOverview />
        </ElCol>
      </ElRow>
    </template>

    <template v-if="userInfo.dashboard === 'work'">
      <ElRow :gutter="20">
        <ElCol :sm="24" :md="24" :lg="12">
          <NewUser />
        </ElCol>
        <ElCol :sm="24" :md="12" :lg="6">
          <Dynamic />
        </ElCol>
        <ElCol :sm="24" :md="12" :lg="6">
          <TodoList />
        </ElCol>
      </ElRow>
    </template>

    <AboutProject />
  </div>
</template>

<script setup lang="ts">
  import CardList from './modules/card-list.vue'
  import ActiveUser from './modules/active-user.vue'
  import SalesOverview from './modules/sales-overview.vue'
  import AboutProject from './modules/about-project.vue'
  import NewUser from './modules/new-user.vue'
  import Dynamic from './modules/dynamic-stats.vue'
  import TodoList from './modules/todo-list.vue'
  import { useCommon } from '@/hooks/core/useCommon'
  import { useUserStore } from '@/store/modules/user'

  defineOptions({ name: 'Console' })

  const userStore = useUserStore()
  const userInfo = userStore.getUserInfo

  const { scrollToTop } = useCommon()
  scrollToTop()
</script>
