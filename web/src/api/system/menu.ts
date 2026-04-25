import request from '@/utils/http'

/**
 * 菜单API
 */
export default {
  /**
   * 获取数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/menu/list',
      params
    })
  },

  /**
   * 获取菜单树
   * @returns 菜单树
   */
  tree() {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/menu/tree'
    })
  },

  /**
   * 获取用户菜单树
   * @returns 菜单树
   */
  userTree() {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/menu/user-tree'
    })
  },

  /**
   * 获取用户权限列表
   * @returns 权限列表
   */
  userPermissions() {
    return request.get<string[]>({
      url: '/api/system/menu/user-permissions'
    })
  },

  /**
   * 获取权限树（用于分配权限）
   * @returns 权限树
   */
  permissionTree() {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/menu/permission-tree'
    })
  },

  /**
   * 获取可访问的菜单树（含所有类型，带 label 字段，适配 ElTree）
   * @param params 参数
   * @returns 菜单树
   */
  accessMenu(params?: Record<string, any>) {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/menu/access-menu',
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
      url: '/api/system/menu/detail/' + id
    })
  },

  /**
   * 创建数据
   * @param params 数据参数
   * @returns 执行结果
   */
  save(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/system/menu/create',
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
      url: '/api/system/menu/update/' + params.id,
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
      url: '/api/system/menu/delete/' + id
    })
  },

  /**
   * 获取可分配的菜单树（status=1，未删除，适配 ElTree）
   * @returns 菜单树
   */
  assignableTree() {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/menu/assignable-tree'
    })
  },

  /**
   * 更新菜单状态
   * @param id 数据ID
   * @param status 状态
   * @returns 执行结果
   */
  updateStatus(id: number | string, status: number) {
    return request.put<any>({
      url: '/api/system/menu/status/' + id,
      data: { status }
    })
  }
}
