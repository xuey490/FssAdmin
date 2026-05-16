<template>
  <div class="art-card h-105 p-4 box-border mb-5 max-sm:mb-4">
    <div class="art-card-header">
      <div class="title">
        <h4>月度登录汇总</h4>
      </div>
    </div>
    <ArtBarChart
      class="box-border p-2"
      barWidth="50%"
      height="calc(100% - 40px)"
      :showAxisLine="false"
      :data="yData"
      :xAxisData="xData"
    />
  </div>
</template>

<script setup lang="ts">
  import { fetchLoginBarChart } from '@/api/auth'

  /**
   * 登录数据
   */
  const yData = ref<number[]>([])

  /**
   * 时间数据
   */
  const xData = ref<string[]>([])

  onMounted(async () => {
    fetchLoginBarChart().then((data) => {
      yData.value = data.login_count
      xData.value = data.login_month
    })
  })
</script>
