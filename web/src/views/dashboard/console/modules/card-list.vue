<template>
  <ElRow :gutter="20" class="flex">
    <ElCol :sm="12" :md="6" :lg="6">
      <div class="art-card relative flex flex-col justify-center h-35 px-5 mb-5 max-sm:mb-4">
        <span class="text-g-700 text-sm">用户统计</span>
        <ArtCountTo class="text-[26px] font-medium mt-2" :target="statData.user" :duration="1300" />
        <div class="flex-c mt-1">
          <span class="text-xs text-g-600">较上周</span>
          <span class="ml-1 text-xs font-semibold text-success">+10%</span>
        </div>
        <div
          class="absolute top-0 bottom-0 right-5 m-auto size-12.5 rounded-xl flex-cc bg-theme/10"
        >
          <ArtSvgIcon icon="ri:group-line" class="text-xl text-theme" />
        </div>
      </div>
    </ElCol>
    <ElCol :sm="12" :md="6" :lg="6">
      <div class="art-card relative flex flex-col justify-center h-35 px-5 mb-5 max-sm:mb-4">
        <span class="text-g-700 text-sm">附件统计</span>
        <ArtCountTo
          class="text-[26px] font-medium mt-2"
          :target="statData.attach"
          :duration="1300"
        />
        <div class="flex-c mt-1">
          <span class="text-xs text-g-600">较上周</span>
          <span class="ml-1 text-xs font-semibold text-success">+10%</span>
        </div>
        <div
          class="absolute top-0 bottom-0 right-5 m-auto size-12.5 rounded-xl flex-cc bg-theme/10"
        >
          <ArtSvgIcon icon="ri:attachment-line" class="text-xl text-theme" />
        </div>
      </div>
    </ElCol>
    <ElCol :sm="12" :md="6" :lg="6">
      <div class="art-card relative flex flex-col justify-center h-35 px-5 mb-5 max-sm:mb-4">
        <span class="text-g-700 text-sm">登录统计</span>
        <ArtCountTo
          class="text-[26px] font-medium mt-2"
          :target="statData.login"
          :duration="1300"
        />
        <div class="flex-c mt-1">
          <span class="text-xs text-g-600">较上周</span>
          <span class="ml-1 text-xs font-semibold text-success">+12%</span>
        </div>
        <div
          class="absolute top-0 bottom-0 right-5 m-auto size-12.5 rounded-xl flex-cc bg-theme/10"
        >
          <ArtSvgIcon icon="ri:fire-line" class="text-xl text-theme" />
        </div>
      </div>
    </ElCol>
    <ElCol :sm="12" :md="6" :lg="6">
      <div class="art-card relative flex flex-col justify-center h-35 px-5 mb-5 max-sm:mb-4">
        <span class="text-g-700 text-sm">操作统计</span>
        <ArtCountTo
          class="text-[26px] font-medium mt-2"
          :target="statData.operate"
          :duration="1300"
        />
        <div class="flex-c mt-1">
          <span class="text-xs text-g-600">较上周</span>
          <span class="ml-1 text-xs font-semibold text-danger">-5%</span>
        </div>
        <div
          class="absolute top-0 bottom-0 right-5 m-auto size-12.5 rounded-xl flex-cc bg-theme/10"
        >
          <ArtSvgIcon icon="ri:pie-chart-line" class="text-xl text-theme" />
        </div>
      </div>
    </ElCol>
  </ElRow>
</template>

<script setup lang="ts">
  import { fetchStatistics } from '@/api/auth'

  const statData = ref({
    user: 0,
    attach: 0,
    login: 0,
    operate: 0
  })

  onMounted(() => {
    fetchStatistics().then((data) => {
      statData.value = data
    })
  })
</script>
