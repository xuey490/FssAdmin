import request from '@/utils/http'

/**
 * 部门API
 */
export default {
  /**
   * 获取所有启用的部门（扁平列表，供下拉选择）
   */
  allEnabled() {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/dept/all-enabled'
    })
  },

  /**
   * 获取数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/dept/list',
      params
    })
  },

  /**
   * 获取部门树
   * @returns 部门树
   */
  tree() {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/dept/tree'
    })
  },

  /**
   * 读取数据
   * @param id 数据ID
   * @returns 数据详情
   */
  read(id: number | string) {
    return request.get<Api.Common.ApiData>({
      url: '/api/system/dept/detail/' + id
    })
  },

  /**
   * 创建数据
   * @param params 数据参数
   * @returns 执行结果
   */
  save(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/system/dept/create',
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
      url: '/api/system/dept/update/' + params.id,
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
      url: '/api/system/dept/delete/' + id
    })
  },

  /**
   * 更新部门状态
   * @param id 数据ID
   * @param status 状态
   * @returns 执行结果
   */
  updateStatus(id: number | string, status: number) {
    return request.put<any>({
      url: '/api/system/dept/status/' + id,
      data: { status }
    })
  },

  /**
   * 获取所有子部门ID
   * @param id 部门ID
   * @returns 子部门ID列表
   */
  children(id: number | string) {
    return request.get<number[]>({
      url: '/api/system/dept/children/' + id
    })
  },

  /**
   * 获取可访问的部门树（供用户管理左侧树使用）
   * @returns 部门树（带 label 字段）
   */
  accessDept() {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/dept/access-dept'
    })
  }
}
