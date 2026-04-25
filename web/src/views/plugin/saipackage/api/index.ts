/**
 * 插件管理 API
 *
 * 提供插件安装、卸载、上传等功能接口
 *
 * @module api/tool/saipackage
 */
import request from '@/utils/http'

export interface AppInfo {
  app: string
  title: string
  about: string
  author: string
  version: string
  support?: string
  website?: string
  state: number
  npm_dependent_wait_install?: number
  composer_dependent_wait_install?: number
}

export interface VersionInfo {
  saiadmin_version?: {
    describe: string
    notes: string
    state: string
  }
  saipackage_version?: {
    describe: string
    notes: string
    state: string
  }
}

export interface AppListResponse {
  data: AppInfo[]
  version: VersionInfo
}

export interface StoreApp {
  id: number
  title: string
  about: string
  logo: string
  version: string
  price: string
  avatar?: string
  username: string
  sales_num: number
  content?: string
  screenshots?: string[]
}

export interface StoreUser {
  nickname?: string
  username: string
  avatar?: string
}

export interface PurchasedApp {
  id: number
  app_id: number
  appname?: string
  title: string
  logo: string
  version: string
  developer: string
  about: string
}

export interface AppVersion {
  id: number
  version: string
  create_time: string
  remark: string
}

export default {
  /**
   * 获取已安装的插件列表
   */
  getAppList() {
    return request.get<AppListResponse>({ url: '/app/saipackage/install/index' })
  },

  /**
   * 上传插件包
   */
  uploadApp(data: FormData) {
    return request.post<AppInfo>({ url: '/app/saipackage/install/upload', data })
  },

  /**
   * 安装插件
   */
  installApp(data: { appName: string }) {
    return request.post<any>({ url: '/app/saipackage/install/install', data })
  },

  /**
   * 卸载插件
   */
  uninstallApp(data: { appName: string }) {
    return request.post<any>({ url: '/app/saipackage/install/uninstall', data })
  },

  /**
   * 重载后端
   */
  reloadBackend() {
    return request.post<any>({ url: '/app/saipackage/install/reload' })
  },

  /**
   * 获取在线商店应用列表
   */
  getOnlineAppList(params: {
    page?: number
    limit?: number
    price?: string
    type?: string | number
    keywords?: string
  }) {
    return request.get<{ data: StoreApp[]; total: number }>({
      url: '/tool/install/online/appList',
      params
    })
  },

  /**
   * 获取验证码
   */
  getStoreCaptcha() {
    return request.get<{ image: string; uuid: string }>({
      url: '/tool/install/online/storeCaptcha'
    })
  },

  /**
   * 商店登录
   */
  storeLogin(data: { username: string; password: string; code: string; uuid: string }) {
    return request.post<{ access_token: string }>({
      url: '/tool/install/online/storeLogin',
      data
    })
  },

  /**
   * 获取商店用户信息
   */
  getStoreUserInfo(token: string) {
    return request.get<StoreUser>({
      url: '/tool/install/online/storeUserInfo',
      params: { token }
    })
  },

  /**
   * 获取已购应用列表
   */
  getPurchasedApps(token: string) {
    return request.get<PurchasedApp[]>({
      url: '/tool/install/online/storePurchasedApps',
      params: { token }
    })
  },

  /**
   * 获取应用版本列表
   */
  getAppVersions(token: string, app_id: number) {
    return request.get<AppVersion[]>({
      url: '/tool/install/online/storeAppVersions',
      params: { token, app_id }
    })
  },

  /**
   * 下载应用
   */
  downloadApp(data: { token: string; id: number }) {
    return request.post<any>({
      url: '/tool/install/online/storeDownloadApp',
      data
    })
  }
}
