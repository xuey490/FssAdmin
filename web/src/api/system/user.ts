import request from '@/utils/http'

/**
 * 用户API
 */
export default {
  /**
   * 获取数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/system/user/list',
      params
    })
  },

  /**
   * 读取数据
   * @param id 数据ID
   * @returns 数据详情
   */
  read(id: number | string) {
    return request.get<Api.Common.ApiData>({
      url: '/api/system/user/detail/' + id
    })
  },

  /**
   * 创建数据
   * @param params 数据参数
   * @returns 执行结果
   */
  save(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/system/user/create',
      data: params
    })
  },

  /**
   * 更新数据
   * @param params 数据参数
   * @returns 执行结果
   */
  update(params: Record<string, any>) {
    return request.put<any>({
      url: '/api/system/user/update/' + params.id,
      data: params
    })
  },

  /**
   * 删除数据
   * @param id 数据ID
   * @returns 执行结果
   */
  delete(id: number | string) {
    return request.del<any>({
      url: '/api/system/user/delete/' + id
    })
  },

  /**
   * 更新用户状态
   * @param params 数据参数
   * @returns 执行结果
   */
  updateStatus(id: number | string, status: number) {
    return request.put<any>({
      url: '/api/system/user/status/' + id,
      data: { status }
    })
  },

  /**
   * 重置密码
   * @param params 数据参数
   * @returns 执行结果
   */
  resetPassword(id: number | string, password: string) {
    return request.put<any>({
      url: '/api/system/user/reset-password/' + id,
      data: { password }
    })
  },

  /**
   * 修改密码
   * @param params 数据参数
   * @returns 执行结果
   */
  changePassword(params: Record<string, any>) {
    return request.put<any>({
      url: '/api/system/user/change-password/' + params.id,
      data: params
    })
  },

  /**
   * 清理用户缓存
   * @param params 数据参数
   * @returns 执行结果
   */
  clearCache(params: Record<string, any>) {
    return request.put<any>({
      url: '/api/system/user/clear-cache/' + params.id,
      data: params
    })
  },

  /**
   * 设置首页/工作台
   * @param params 数据参数
   * @returns 执行结果
   */
  setHomePage(params: Record<string, any>) {
    return request.put<any>({
      url: '/api/system/user/set-home-page/' + params.id,
      data: params
    })
  },

  /**
   * 获取用户已分配的菜单ID列表
   * @param id 用户ID
   * @returns 菜单ID数组
   */
  getUserMenus(id: number | string) {
    return request.get<number[]>({
      url: '/api/system/user/menus/' + id
    })
  },

  /**
   * 保存用户菜单分配
   * @param id 用户ID
   * @param menuIds 菜单ID数组
   * @returns 执行结果
   */
  saveUserMenus(id: number | string, menuIds: number[]) {
    return request.put<any>({
      url: '/api/system/user/menus/' + id,
      data: { menu_ids: menuIds }
    })
  }
}
