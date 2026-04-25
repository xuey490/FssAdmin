import request from '@/utils/http'

/**
 * 岗位API
 */
export default {
  /**
   * 获取数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/system/post/list',
      params
    })
  },

  /**
   * 获取所有启用的岗位
   * @returns 数据列表
   */
  enabled() {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/post/enabled'
    })
  },

  /**
   * 读取数据
   * @param id 数据ID
   * @returns 数据详情
   */
  read(id: number | string) {
    return request.get<Api.Common.ApiData>({
      url: '/api/system/post/detail/' + id
    })
  },

  /**
   * 创建数据
   * @param params 数据参数
   * @returns 执行结果
   */
  save(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/system/post/create',
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
      url: '/api/system/post/update/' + params.id,
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
      url: '/api/system/post/delete/' + id
    })
  },

  /**
   * 更新岗位状态
   * @param id 数据ID
   * @param enabled 状态
   * @returns 执行结果
   */
  updateStatus(id: number | string, enabled: number) {
    return request.put<any>({
      url: '/api/system/post/status/' + id,
      data: { enabled }
    })
  },

  /**
   * 获取可访问的岗位列表（供用户编辑弹窗使用）
   * @returns 岗位列表（包含 id, name 字段）
   */
  accessPost() {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/post/access-post'
    })
  }
}
