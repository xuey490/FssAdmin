<template>
  <div class="art-card h-105 p-5 mb-5 max-sm:mb-4">
    <div class="art-card-header">
      <div class="title">
        <h4>近期登录统计</h4>
      </div>
    </div>
    <ArtLineChart
      height="calc(100% - 40px)"
      :data="yData"
      :xAxisData="xData"
      :showAreaColor="true"
      :showAxisLine="false"
    />
  </div>
</template>

<script setup lang="ts">
  import { fetchLoginChart } from '@/api/auth'

  /**
   * 登录数据
   */
  const yData = ref<number[]>([])

  /**
   * 时间数据
   */
  const xData = ref<string[]>([])

  onMounted(async () => {
    fetchLoginChart().then((data) => {
      yData.value = data.login_count
      xData.value = data.login_date
    })
  })
</script>
