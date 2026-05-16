import request from '@/utils/http'

/**
 * 角色API
 */
export default {
  /**
   * 获取数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/system/role/list',
      params
    })
  },

  /**
   * 获取所有启用的角色
   * @returns 数据列表
   */
  all() {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/role/all'
    })
  },

  /**
   * 获取角色树
   * @returns 数据列表
   */
  tree() {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/role/tree'
    })
  },

  /**
   * 读取数据
   * @param id 数据ID
   * @returns 数据详情
   */
  read(id: number | string) {
    return request.get<Api.Common.ApiData>({
      url: '/api/system/role/detail/' + id
    })
  },

  /**
   * 创建数据
   * @param params 数据参数
   * @returns 执行结果
   */
  save(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/system/role/create',
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
      url: '/api/system/role/update/' + params.id,
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
      url: '/api/system/role/delete/' + id
    })
  },

  /**
   * 更新角色状态
   * @param id 数据ID
   * @param status 状态
   * @returns 执行结果
   */
  updateStatus(id: number | string, status: number) {
    return request.put<any>({
      url: '/api/system/role/status/' + id,
      data: { status }
    })
  },

  /**
   * 分配菜单给角色
   * @param params
   * @returns
   */
  assignMenus(id: number | string, menuIds: number[]) {
    return request.put<any>({
      url: '/api/system/role/assign-menus/' + id,
      data: { menu_ids: menuIds }
    })
  },

  /**
   * 获取可访问的角色列表（供用户编辑弹窗使用）
   * @returns 角色列表（包含 id, name 字段）
   */
  accessRole() {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/role/access-role'
    })
  },

  /**
   * 获取角色已分配的菜单
   * @param params 包含角色ID
   * @returns 菜单列表
   */
  menuByRole(params: Record<string, any>) {
    return request.get<any>({
      url: '/api/system/role/menu-by-role/' + params.id
    })
  },

  /**
   * 保存角色菜单权限
   * @param params 包含角色ID和菜单ID数组
   * @returns 执行结果
   */
  menuPermission(params: Record<string, any>) {
    return request.put<any>({
      url: '/api/system/role/menu-permission/' + params.id,
      data: { menu_ids: params.menu_ids }
    })
  }
}
