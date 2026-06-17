<script setup lang="ts">
  import alipayQr from '@/assets/images/qrcode/alipay.png'
  import wechatQr from '@/assets/images/qrcode/wechat.png'
  import { fetchPublicConfigValue } from '@/api/auth'

  defineOptions({ name: 'SponsorQrPopup' })

  const SPONSOR_POPUP_COOKIE = 'console_sponsor_popup_dismiss'
  const SPONSOR_DELAY_MS = 2000

  const sponsorPopupVisible = ref(false)
  const sponsorEnabled = ref(false)
  let sponsorPopupTimer: number | undefined

  const qrItems = [
    { key: 'alipay', src: alipayQr, alt: '支付宝收款码', label: '使用支付宝' },
    { key: 'wechat', src: wechatQr, alt: '微信收款码', label: '使用微信支付' }
  ] as const

  function getCookie(name: string): string | null {
    const match = document.cookie.match(new RegExp(`(?:^|;\\s*)${name}=([^;]*)`))
    return match ? decodeURIComponent(match[1]) : null
  }

  function getTodayKey() {
    const now = new Date()
    const year = now.getFullYear()
    const month = String(now.getMonth() + 1).padStart(2, '0')
    const day = String(now.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
  }

  function isSponsorPopupDismissedToday() {
    return getCookie(SPONSOR_POPUP_COOKIE) === getTodayKey()
  }

  function setSponsorPopupDismissCookie() {
    const expires = new Date()
    expires.setHours(23, 59, 59, 999)
    document.cookie = `${SPONSOR_POPUP_COOKIE}=${encodeURIComponent(getTodayKey())}; expires=${expires.toUTCString()}; path=/`
  }

  const loadSponsorConfig = async () => {
    try {
      const res = await fetchPublicConfigValue('sponsor_window')
      sponsorEnabled.value = res?.value === '1'
    } catch (error) {
      console.error('[SponsorQrPopup] 加载赞助弹窗配置失败:', error)
      sponsorEnabled.value = false
    }
  }

  function openSponsorPopup() {
    if (!sponsorEnabled.value || isSponsorPopupDismissedToday()) return
    sponsorPopupVisible.value = true
  }

  function closeSponsorPopup() {
    sponsorPopupVisible.value = false
  }

  function dismissSponsorPopupForToday() {
    setSponsorPopupDismissCookie()
    closeSponsorPopup()
  }

  function clearSponsorPopupTimer() {
    if (sponsorPopupTimer) {
      window.clearTimeout(sponsorPopupTimer)
      sponsorPopupTimer = undefined
    }
  }

  function scheduleSponsorPopup() {
    clearSponsorPopupTimer()
    if (!sponsorEnabled.value || isSponsorPopupDismissedToday() || sponsorPopupVisible.value) return
    sponsorPopupTimer = window.setTimeout(openSponsorPopup, SPONSOR_DELAY_MS)
  }

  onMounted(async () => {
    await loadSponsorConfig()
    scheduleSponsorPopup()
  })

  onActivated(() => {
    scheduleSponsorPopup()
  })

  onBeforeUnmount(clearSponsorPopupTimer)
</script>

<template>
  <ElDialog
    v-model="sponsorPopupVisible"
    title="赞助作者"
    width="560px"
    align-center
    append-to-body
    destroy-on-close
    :show-close="true"
    :close-on-click-modal="false"
    :close-on-press-escape="false"
    class="console-sponsor-popup"
    modal-class="console-sponsor-popup__overlay"
    @close="closeSponsorPopup"
  >
    <p class="console-sponsor-popup__desc">
      如果您觉得这个项目对您有帮助，可以请作者喝一杯咖啡，感谢您对开源项目的支持。赞助金额超过￥200元，请添加作者微信号：xdbyvibm6
      ，将提供提供全套源码！
    </p>

    <div class="console-sponsor-popup__content">
      <div v-for="item in qrItems" :key="item.key" class="console-sponsor-popup__qr-item">
        <p class="console-sponsor-popup__qr-label">{{ item.label }}</p>
        <ElImage :src="item.src" :alt="item.alt" fit="contain" class="console-sponsor-popup__qr-image" />
      </div>
    </div>

    <template #footer>
      <div class="console-sponsor-popup__footer">
        <ElButton type="primary" link @click="dismissSponsorPopupForToday">今天内不再显示</ElButton>
      </div>
    </template>
  </ElDialog>
</template>

<style scoped lang="scss">
  .console-sponsor-popup {
    :deep(.el-dialog) {
      background: rgb(255 255 255 / 96%);
      border-radius: 12px;
      box-shadow: 0 12px 32px rgb(0 0 0 / 18%);
    }

    :deep(.el-dialog__header) {
      padding: 20px 20px 0;
      margin: 0;
      background: transparent;
    }

    :deep(.el-dialog__title) {
      font-size: 20px;
      font-weight: 700;
      color: #1f2d3d;
    }

    :deep(.el-dialog__headerbtn) {
      top: 16px;
      right: 16px;
      z-index: 2;
      width: 32px;
      height: 32px;
      background: rgb(255 255 255 / 92%);
      border-radius: 50%;
      box-shadow: 0 2px 8px rgb(0 0 0 / 12%);
    }

    :deep(.el-dialog__body) {
      padding: 12px 20px 0;
      background: transparent;
    }

    :deep(.el-dialog__footer) {
      padding: 12px 20px 16px;
      background: transparent;
    }
  }

  .console-sponsor-popup__desc {
    margin: 0 0 16px;
    font-size: 14px;
    line-height: 1.75;
    color: #5e6d82;
  }

  .console-sponsor-popup__content {
    display: flex;
    gap: 16px;
    align-items: center;
    justify-content: center;
  }

  .console-sponsor-popup__qr-item {
    flex: 1;
    max-width: 220px;
    text-align: center;
  }

  .console-sponsor-popup__qr-label {
    margin: 0 0 10px;
    font-size: 15px;
    font-weight: 600;
    color: #1f2d3d;
  }

  .console-sponsor-popup__qr-image {
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 8px;
    background: #fff;
  }

  .console-sponsor-popup__footer {
    display: flex;
    justify-content: flex-end;
  }

  :global(html.dark) .console-sponsor-popup {
    :deep(.el-dialog) {
      background: rgb(30 30 30 / 96%);
    }

    :deep(.el-dialog__title) {
      color: var(--el-text-color-primary);
    }

    .console-sponsor-popup__desc {
      color: var(--el-text-color-secondary);
    }

    .console-sponsor-popup__qr-label {
      color: var(--el-text-color-primary);
    }
  }
</style>

<style lang="scss">
  .console-sponsor-popup__overlay {
    background-color: rgb(0 0 0 / 35%);
  }
</style>
