import request from '@/utils/http'
import { AppRouteRecord } from '@/types/router'

/**
 * 获取验证码
 * @returns 响应
 */
export function fetchCaptcha() {
  return request.get<Api.Auth.CaptchaResponse>({
    url: '/api/core/captcha'
  })
}

/**
 * 登录
 * @param params 登录参数
 * @returns 登录响应
 */
export function fetchLogin(params: Api.Auth.LoginParams) {
  return request.post<Api.Auth.LoginResponse>({
    url: '/api/core/login',
    params
  })
}

/**
 * 刷新令牌
 * @returns 刷新响应
 */
export function fetchRefreshToken() {
  return request.post<any>({
    url: '/api/core/refresh'
  })
}

/**
 * 退出登录，吊销服务端 token 并清除 cookies
 */
export function fetchLogout() {
  return request.post<any>({
    url: '/api/core/logout'
  })
}

/**
 * 获取用户信息
 * @returns 用户信息
 */
export function fetchGetUserInfo() {
  return request.get<Api.Auth.UserInfo>({
    url: '/api/core/system/user'
  })
}

/**
 * 根据用户名获取租户列表（登录前）
 * @param username 用户名
 * @returns 租户列表
 */
export function fetchTenantsByUsername(username: string) {
  return request.get<Api.Auth.TenantItem[]>({
    url: '/api/core/tenants-by-username',
    params: { username }
  })
}

/**
 * 切换租户（登录后）
 * @param tenantId 目标租户ID
 * @returns 切换结果（包含新token）
 */
export function fetchSwitchTenant(tenantId: number) {
  return request.post<Api.Auth.SwitchTenantResponse>({
    url: '/api/core/switch-tenant',
    params: { tenant_id: tenantId }
  })
}

/**
 * 修改资料
 * @param params 修改资料参数
 * @returns 响应
 */
export function updateUserInfo(params: Record<string, any>) {
  return request.post<any>({
    url: '/api/core/user/updateInfo',
    params
  })
}

/**
 * 修改密码
 * @param params 修改密码参数
 * @returns 响应
 */
export function modifyPassword(params: Record<string, any>) {
  return request.post<any>({
    url: '/api/core/user/modifyPassword',
    params
  })
}

/**
 * 获取登录日志
 * @returns 登录日志数组
 */
export function fetchGetLogin(params: Record<string, any>) {
  return request.get<Api.Common.ApiPage>({
    url: '/api/core/system/getLoginLogList',
    params
  })
}

/**
 * 获取操作日志
 * @returns 操作日志数组
 */
export function fetchGetOperate(params: Record<string, any>) {
  return request.get<Api.Common.ApiPage>({
    url: '/api/core/system/getOperationLogList',
    params
  })
}

/**
 * 清理缓存
 * @returns
 */
export function fetchClearCache() {
  return request.get<any>({
    url: '/api/core/system/clearAllCache'
  })
}

/**
 * 获取字典数据
 * @returns 字典数组
 */
export function fetchGetDictList() {
  return request.get<Api.Auth.DictData>({
    url: '/api/core/system/dictAll'
  })
}

/**
 * 获取菜单列表
 * @returns 菜单数组
 */
export function fetchGetMenuList() {
  return request.get<AppRouteRecord[]>({
    url: '/api/core/system/menu'
  })
}

/**
 * 上传图片
 * @param params
 * @returns
 */
export function uploadImage(params: any) {
  return request.post<any>({
    url: '/api/core/system/uploadImage',
    headers: {
      'Content-Type': 'multipart/form-data'
    },
    params
  })
}

/**
 * 上传文件
 * @param params
 * @returns
 */
export function uploadFile(params: any) {
  return request.post<any>({
    url: '/api/core/system/uploadFile',
    headers: {
      'Content-Type': 'multipart/form-data'
    },
    params
  })
}

/**
 * 切片上传
 * @param params
 * @returns
 */
export function chunkUpload(params: any) {
  return request.post<any>({
    url: '/api/core/system/chunkUpload',
    headers: {
      'Content-Type': 'multipart/form-data'
    },
    params
  })
}

/**
 * 资源分类
 * @param params
 * @returns
 */
export function getResourceCategory(params: any) {
  return request.get<Api.Common.ApiData[]>({
    url: '/api/core/system/getResourceCategory',
    params
  })
}

/**
 * 图片资源列表
 * @param params
 * @returns
 */
export function getResourceList(params: any) {
  return request.get<Api.Common.ApiPage>({
    url: '/api/core/system/getResourceList',
    params
  })
}

/**
 * 用户列表
 * @param params
 * @returns
 */
export function getUserList(params: any) {
  return request.get<Api.Common.ApiPage>({
    url: '/api/core/system/getUserList',
    params
  })
}

/**
 * 控制台基础数据统计
 */
export function fetchStatistics() {
  return request.get<any>({
    url: '/api/core/system/statistics'
  })
}

/**
 * 控制台近30天每日登录折线图
 */
export function fetchLoginChart() {
  return request.get<any>({
    url: '/api/core/system/loginChart'
  })
}

/**
 * 控制台近12个月登录柱状图
 */
export function fetchLoginBarChart() {
  return request.get<any>({
    url: '/api/core/system/loginBarChart'
  })
}
