import { onMounted, onUnmounted } from 'vue'
import { useUserStore } from '@/store/modules/user'
import { fetchRefreshToken } from '@/api/auth'

export function useTokenRefresh() {
  const userStore = useUserStore()
  let refreshTimer: ReturnType<typeof setInterval> | null = null
  let isRefreshing = false

  // === 新增：空闲检测 (Idle Detection) ===
  const IDLE_TIMEOUT = 30 * 60 * 1000 // 30 分钟无操作视为挂机
  let lastActiveTime = Date.now()

  // 节流更新活跃时间，避免频繁触发 (1秒内只更新一次)
  const updateActiveTime = () => {
    const now = Date.now()
    if (now - lastActiveTime > 1000) {
      lastActiveTime = now
    }
  }

  // 监听的浏览器活动事件
  const activityEvents = ['mousemove', 'keydown', 'mousedown', 'touchstart', 'wheel']
  // ===================================

  const parseJwt = (token: string) => {
    try {
      const base64Url = token.split('.')[1]
      const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/')
      const jsonPayload = decodeURIComponent(
        window
          .atob(base64)
          .split('')
          .map((c) => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
          .join('')
      )
      return JSON.parse(jsonPayload)
    } catch (e) {
      return null
    }
  }

  const checkTokenRefresh = async () => {
    const token = userStore.accessToken
    if (!token || !userStore.isLogin) return

    // 检查用户是否处于挂机状态
    if (Date.now() - lastActiveTime > IDLE_TIMEOUT) {
      // 只有在调试模式可以开启打印
      // console.log('用户处于挂机状态，停止自动续期 Token')
      return
    }

    const payload = parseJwt(token)
    if (!payload || !payload.exp) return

    const currentTime = Math.floor(Date.now() / 1000)
    // 如果距离过期不足 5 分钟 (300秒)
    //console.log('距离 Token 过期还有', payload.exp - currentTime, '秒')
    if (payload.exp - currentTime <= 300 && !isRefreshing) {
      isRefreshing = true
      try {
        const res = await fetchRefreshToken()
        // API 响应中包含 { access_token, refresh_token } (由于 axios 拦截器已经解构了 data.data)
        if (res && res.access_token) {
          userStore.setToken(res.access_token, res.refresh_token)
        }
      } catch (error) {
        console.error('Token refresh failed:', error)
      } finally {
        isRefreshing = false
      }
    }
  }

  onMounted(() => {
    // 1. 监听用户活跃事件
    activityEvents.forEach((event) => {
      window.addEventListener(event, updateActiveTime, { passive: true })
    })

    // 2. 立即执行一次
    checkTokenRefresh()
    
    // 3. 之后每分钟检查一次
    refreshTimer = setInterval(checkTokenRefresh, 60000)
  })

  onUnmounted(() => {
    // 移除事件监听，防止内存泄漏
    activityEvents.forEach((event) => {
      window.removeEventListener(event, updateActiveTime)
    })

    if (refreshTimer) {
      clearInterval(refreshTimer)
      refreshTimer = null
    }
  })
}
